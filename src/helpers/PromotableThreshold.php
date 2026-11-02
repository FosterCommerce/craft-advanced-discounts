<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

use craft\base\conditions\ConditionRuleInterface;
use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use craft\commerce\models\LineItem;
use craft\commerce\models\Store;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\fields\conditions\MoneyFieldConditionRule;
use craft\helpers\Json;
use craft\helpers\MoneyHelper;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemMatchConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderConditionRule;
use Money\Money;

/**
 * Evaluates discount conditions against the promotable portion of an order.
 *
 * Leave non-promotable line items out of every total, so a threshold counts only what can be discounted.
 *
 * Top-level cart conditions AND together. The rules nested inside a Line Items or Order
 * condition OR together, matching the “OR” the condition builder puts on its add button.
 */
final class PromotableThreshold
{
	/**
	 * @var array<class-string, array{0: string, 1: string, 2: bool}> order attribute => line item field to subtract, whether the store's minimum total price applies
	 */
	private const VALUE_RULE_FIELDS = [
		ItemSubtotalConditionRule::class => ['itemSubtotal', 'subtotal', false],
		ItemTotalConditionRule::class => ['itemTotal', 'total', false],
		TotalPriceConditionRule::class => ['totalPrice', 'total', true],
		TotalConditionRule::class => ['total', 'total', false],
	];

	public static function matches(ElementConditionInterface $condition, Order $order, ?ElementConditionInterface $productCondition = null): bool
	{
		foreach ($condition->getConditionRules() as $conditionRule) {
			if ($conditionRule instanceof OrderConditionRule) {
				if (! self::orderConditionMatches($conditionRule, $order, $productCondition)) {
					return false;
				}
			} elseif ($conditionRule instanceof LineItemConditionRule) {
				if (! self::lineItemConditionMatches($conditionRule, $order, $productCondition)) {
					return false;
				}
			} elseif ($conditionRule instanceof ElementConditionRuleInterface && ! $conditionRule->matchElement($order)) {
				return false;
			}
		}

		return true;
	}

	public static function amountRemaining(ElementConditionInterface $condition, Order $order, ?ElementConditionInterface $productCondition = null): ?float
	{
		foreach ($condition->getConditionRules() as $conditionRule) {
			if (! $conditionRule instanceof OrderConditionRule) {
				continue;
			}

			foreach ($conditionRule->getOrderCondition()->getConditionRules() as $orderRule) {
				foreach (self::VALUE_RULE_FIELDS as $ruleClass => [$orderField, $lineItemField, $appliesMinimumPrice]) {
					if (
						$orderRule instanceof $ruleClass
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						$currency = (string) $order->currency;
						$remaining = Amounts::toMoney((float) $orderRule->value, $currency)
							->subtract(Amounts::toMoney(self::promotableValue($order, $orderField, $lineItemField, $appliesMinimumPrice, $productCondition), $currency));

						return $remaining->isNegative() ? 0.0 : (float) MoneyHelper::toDecimal($remaining);
					}
				}
			}
		}

		return null;
	}

	public static function quantityRemaining(ElementConditionInterface $condition, Order $order, ?ElementConditionInterface $productCondition = null): ?int
	{
		foreach ($condition->getConditionRules() as $conditionRule) {
			if ($conditionRule instanceof OrderConditionRule) {
				foreach ($conditionRule->getOrderCondition()->getConditionRules() as $orderRule) {
					if (
						$orderRule instanceof TotalQtyConditionRule
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						return max(0, (int) $orderRule->value - self::promotableQty($order, $productCondition));
					}
				}
			}

			if ($conditionRule instanceof LineItemConditionRule) {
				foreach ($conditionRule->getLineItemCondition()->getConditionRules() as $rule) {
					if (
						$rule instanceof LineItemMatchConditionRule
						&& $rule->quantity !== null
						&& in_array($rule->operator, ['>=', '>'], true)
					) {
						return max(0, $rule->quantity - $rule->matchedQuantity(self::promotableLineItems($order, $productCondition)));
					}
				}
			}
		}

		return null;
	}

	public static function orderConditionMatches(OrderConditionRule $triggerRule, Order $order, ?ElementConditionInterface $productCondition = null): bool
	{
		$orderRules = $triggerRule->getOrderCondition()->getConditionRules();
		if ($orderRules === []) {
			return true;
		}

		foreach ($orderRules as $orderRule) {
			if (self::orderRuleMatches($orderRule, $order, $productCondition)) {
				return true;
			}
		}

		return false;
	}

	public static function lineItemConditionMatches(LineItemConditionRule $triggerRule, Order $order, ?ElementConditionInterface $productCondition = null): bool
	{
		$lineItemRules = $triggerRule->getLineItemCondition()->getConditionRules();
		if ($lineItemRules === []) {
			return true;
		}

		foreach ($lineItemRules as $lineItemRule) {
			if (self::lineItemRuleMatches($lineItemRule, $order, $productCondition)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Only a variant can match a product condition that has rows.
	 */
	public static function isPromotable(LineItem $lineItem, ?ElementConditionInterface $productCondition): bool
	{
		if (! $lineItem->getIsPromotable()) {
			return false;
		}

		if (! $productCondition instanceof ElementConditionInterface || $productCondition->getConditionRules() === []) {
			return true;
		}

		// Skip custom line items before reading the purchasable, which throws for them
		if ($lineItem->purchasableId === null) {
			return false;
		}

		$purchasable = $lineItem->getPurchasable();

		return $purchasable instanceof Variant && $productCondition->matchElement($purchasable);
	}

	private static function orderRuleMatches(ConditionRuleInterface $orderRule, Order $order, ?ElementConditionInterface $productCondition): bool
	{
		if ($orderRule instanceof MoneyFieldConditionRule && isset(self::VALUE_RULE_FIELDS[$orderRule::class])) {
			[$orderField, $lineItemField, $appliesMinimumPrice] = self::VALUE_RULE_FIELDS[$orderRule::class];

			return self::compare(self::promotableValue($order, $orderField, $lineItemField, $appliesMinimumPrice, $productCondition), $orderRule->operator, $orderRule->value, $orderRule->maxValue);
		}

		if ($orderRule instanceof TotalQtyConditionRule) {
			return self::compare((float) self::promotableQty($order, $productCondition), $orderRule->operator, $orderRule->value, $orderRule->maxValue);
		}

		return $orderRule instanceof ElementConditionRuleInterface && $orderRule->matchElement($order);
	}

	private static function lineItemRuleMatches(ConditionRuleInterface $lineItemRule, Order $order, ?ElementConditionInterface $productCondition): bool
	{
		if ($lineItemRule instanceof LineItemMatchConditionRule) {
			return $lineItemRule->quantityMatches($lineItemRule->matchedQuantity(self::promotableLineItems($order, $productCondition)));
		}

		return $lineItemRule instanceof ElementConditionRuleInterface && $lineItemRule->matchElement($order);
	}

	/**
	 * @return LineItem[]
	 */
	private static function promotableLineItems(Order $order, ?ElementConditionInterface $productCondition): array
	{
		return array_values(array_filter(
			$order->getLineItems(),
			static fn (LineItem $lineItem): bool => self::isPromotable($lineItem, $productCondition)
		));
	}

	private static function promotableValue(Order $order, string $orderField, string $lineItemField, bool $appliesMinimumPrice, ?ElementConditionInterface $productCondition): float
	{
		$currency = (string) $order->currency;
		$value = Amounts::toMoney((float) $order->{$orderField}, $currency);

		foreach ($order->getLineItems() as $lineItem) {
			if (! self::isPromotable($lineItem, $productCondition)) {
				$value = $value->subtract(Amounts::toMoney((float) $lineItem->{$lineItemField}, $currency));
			}
		}

		if ($appliesMinimumPrice) {
			$value = self::applyMinimumTotalPrice($order, $value);
		}

		return (float) MoneyHelper::toDecimal($value);
	}

	/**
	 * Apply the store's minimum total price strategy to a promotable total.
	 */
	private static function applyMinimumTotalPrice(Order $order, Money $value): Money
	{
		$currency = (string) $order->currency;
		$shipping = Amounts::toMoney($order->getTotalShippingCost(), $currency);

		return match ($order->getStore()->getMinimumTotalPriceStrategy()) {
			Store::MINIMUM_TOTAL_PRICE_STRATEGY_ZERO => $value->isNegative()
				? Amounts::toMoney(0, $currency)
				: $value,
			Store::MINIMUM_TOTAL_PRICE_STRATEGY_SHIPPING => $value->lessThan($shipping)
				? $shipping
				: $value,
			default => $value,
		};
	}

	private static function promotableQty(Order $order, ?ElementConditionInterface $productCondition): int
	{
		$qty = 0;
		foreach ($order->getLineItems() as $lineItem) {
			if (self::isPromotable($lineItem, $productCondition)) {
				$qty += $lineItem->qty;
			}
		}

		return $qty;
	}

	/**
	 * Compare a promotable total against a number rule's operator and values.
	 *
	 * Handle every operator the CP offers, or a rule the admin builds never matches.
	 */
	private static function compare(float $value, string $operator, ?string $min, ?string $max): bool
	{
		$min ??= '';
		$max ??= '';

		if ($operator === 'empty') {
			return ! $value;
		}

		if ($operator === 'notempty') {
			return (bool) $value;
		}

		if ($operator === 'between') {
			if (($min === '' || $min === '0') && ($max === '' || $max === '0')) {
				return true;
			}

			if ($min !== '' && $min !== '0' && $value < (float) $min) {
				return false;
			}

			return ! ($max !== '' && $max !== '0' && $value > (float) $max);
		}

		if ($min === '') {
			return true;
		}

		$threshold = (float) $min;

		return match ($operator) {
			'=' => $value === $threshold,
			'!=' => $value !== $threshold,
			'<' => $value < $threshold,
			'<=' => $value <= $threshold,
			'>' => $value > $threshold,
			'>=' => $value >= $threshold,
			'in' => self::inList($value, $min),
			'ni' => ! self::inList($value, $min),
			default => false,
		};
	}

	private static function inList(float $value, string $list): bool
	{
		$decoded = Json::decodeIfJson($list);

		return is_array($decoded) && in_array($value, array_map('floatval', $decoded), true);
	}
}
