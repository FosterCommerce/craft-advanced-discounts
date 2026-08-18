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
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\fields\conditions\MoneyFieldConditionRule;
use craft\helpers\Json;
use craft\helpers\MoneyHelper;
use fostercommerce\advanceddiscounts\elements\conditions\HasPurchasableConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\RelatedToConditionRule;

/**
 * Evaluates discount conditions against the promotable portion of an order.
 *
 * Commerce order rules read whole-order totals, so a fully non-promotable cart would
 * otherwise satisfy a threshold no discountable item contributed to.
 *
 * Top-level cart conditions AND together. The rules nested inside a Line Items or Order
 * condition OR together, matching the “OR” the condition builder puts on its add button.
 */
final class PromotableThreshold
{
	/**
	 * @var array<class-string, array{0: string, 1: string}> order attribute => line item field to subtract
	 */
	private const VALUE_RULE_FIELDS = [
		ItemSubtotalConditionRule::class => ['itemSubtotal', 'subtotal'],
		ItemTotalConditionRule::class => ['itemTotal', 'total'],
		TotalPriceConditionRule::class => ['totalPrice', 'total'],
		TotalConditionRule::class => ['total', 'total'],
	];

	public static function matches(ElementConditionInterface $condition, Order $order): bool
	{
		foreach ($condition->getConditionRules() as $rule) {
			if ($rule instanceof OrderConditionRule) {
				if (! self::orderConditionMatches($rule, $order)) {
					return false;
				}
			} elseif ($rule instanceof LineItemConditionRule) {
				if (! self::lineItemConditionMatches($rule, $order)) {
					return false;
				}
			} elseif ($rule instanceof ElementConditionRuleInterface && ! $rule->matchElement($order)) {
				return false;
			}
		}

		return true;
	}

	public static function amountRemaining(ElementConditionInterface $condition, Order $order): ?float
	{
		foreach ($condition->getConditionRules() as $triggerRule) {
			if (! $triggerRule instanceof OrderConditionRule) {
				continue;
			}

			foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
				foreach (self::VALUE_RULE_FIELDS as $ruleClass => [$orderField, $lineItemField]) {
					if (
						$orderRule instanceof $ruleClass
						&& property_exists($orderRule, 'value')
						&& property_exists($orderRule, 'operator')
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						$currency = (string) $order->currency;
						$remaining = Amounts::toMoney((float) $orderRule->value, $currency)
							->subtract(Amounts::toMoney(self::promotableValue($order, $orderField, $lineItemField), $currency));

						return max(0.0, (float) MoneyHelper::toDecimal($remaining));
					}
				}
			}
		}

		return null;
	}

	public static function quantityRemaining(ElementConditionInterface $condition, Order $order): ?int
	{
		foreach ($condition->getConditionRules() as $triggerRule) {
			if ($triggerRule instanceof OrderConditionRule) {
				foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
					if (
						$orderRule instanceof TotalQtyConditionRule
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						return max(0, (int) $orderRule->value - self::promotableQty($order));
					}
				}
			}

			if ($triggerRule instanceof LineItemConditionRule) {
				foreach ($triggerRule->getLineItemCondition()->getConditionRules() as $rule) {
					if (
						$rule instanceof HasPurchasableConditionRule
						&& $rule->quantity !== null
						&& in_array($rule->operator, ['>=', '>'], true)
					) {
						return max(0, $rule->quantity - self::promotableMatchingQty($rule, $order));
					}
				}
			}
		}

		return null;
	}

	private static function orderConditionMatches(OrderConditionRule $triggerRule, Order $order): bool
	{
		$orderRules = $triggerRule->getOrderCondition()->getConditionRules();
		if ($orderRules === []) {
			return true;
		}

		foreach ($orderRules as $orderRule) {
			if (self::orderRuleMatches($orderRule, $order)) {
				return true;
			}
		}

		return false;
	}

	private static function orderRuleMatches(ConditionRuleInterface $orderRule, Order $order): bool
	{
		if ($orderRule instanceof MoneyFieldConditionRule && isset(self::VALUE_RULE_FIELDS[$orderRule::class])) {
			[$orderField, $lineItemField] = self::VALUE_RULE_FIELDS[$orderRule::class];

			return self::compare(self::promotableValue($order, $orderField, $lineItemField), $orderRule->operator, $orderRule->value, $orderRule->maxValue);
		}

		if ($orderRule instanceof TotalQtyConditionRule) {
			return self::compare((float) self::promotableQty($order), $orderRule->operator, $orderRule->value, $orderRule->maxValue);
		}

		return $orderRule instanceof ElementConditionRuleInterface && $orderRule->matchElement($order);
	}

	private static function lineItemConditionMatches(LineItemConditionRule $triggerRule, Order $order): bool
	{
		$lineItemRules = $triggerRule->getLineItemCondition()->getConditionRules();
		if ($lineItemRules === []) {
			return true;
		}

		foreach ($lineItemRules as $lineItemRule) {
			if (self::lineItemRuleMatches($lineItemRule, $order)) {
				return true;
			}
		}

		return false;
	}

	private static function lineItemRuleMatches(ConditionRuleInterface $lineItemRule, Order $order): bool
	{
		if ($lineItemRule instanceof HasPurchasableConditionRule) {
			$matchingQty = self::promotableMatchingQty($lineItemRule, $order);
			if ($lineItemRule->quantity === null) {
				return $matchingQty > 0;
			}

			return self::compare((float) $matchingQty, $lineItemRule->operator, (string) $lineItemRule->quantity, null);
		}

		if ($lineItemRule instanceof RelatedToConditionRule) {
			return self::hasPromotableRelated($lineItemRule, $order);
		}

		return $lineItemRule instanceof ElementConditionRuleInterface && $lineItemRule->matchElement($order);
	}

	private static function hasPromotableRelated(RelatedToConditionRule $rule, Order $order): bool
	{
		$elementId = (int) $rule->getElementId();
		if ($elementId === 0) {
			return false;
		}

		$variantIds = Purchasables::relatedVariantIds($elementId);

		foreach ($order->getLineItems() as $lineItem) {
			if ($lineItem->getIsPromotable() && in_array((int) $lineItem->purchasableId, $variantIds, true)) {
				return true;
			}
		}

		return false;
	}

	private static function promotableValue(Order $order, string $orderField, string $lineItemField): float
	{
		$currency = (string) $order->currency;
		$value = Amounts::toMoney((float) $order->{$orderField}, $currency);

		foreach ($order->getLineItems() as $lineItem) {
			if (! $lineItem->getIsPromotable()) {
				$value = $value->subtract(Amounts::toMoney((float) $lineItem->{$lineItemField}, $currency));
			}
		}

		return (float) MoneyHelper::toDecimal($value);
	}

	private static function promotableQty(Order $order): int
	{
		$qty = 0;
		foreach ($order->getLineItems() as $lineItem) {
			if ($lineItem->getIsPromotable()) {
				$qty += $lineItem->qty;
			}
		}

		return $qty;
	}

	private static function promotableMatchingQty(HasPurchasableConditionRule $rule, Order $order): int
	{
		$purchasableId = (int) $rule->getElementId();
		$qty = 0;
		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable !== null && $lineItem->getIsPromotable() && Purchasables::matches($purchasable, $rule->purchasableType, [$purchasableId])) {
				$qty += $lineItem->qty;
			}
		}

		return $qty;
	}

	/**
	 * Mirrors the operator handling in `craft\base\conditions\BaseNumberConditionRule::matchValue`,
	 * against the promotable total rather than the rule's own attribute.
	 *
	 * Every operator the CP offers has to be handled here. An unhandled one is a rule the
	 * store admin can build and that silently never matches.
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
			if (empty($min) && empty($max)) {
				return true;
			}

			if (! empty($min) && $value < (float) $min) {
				return false;
			}

			return ! (! empty($max) && $value > (float) $max);
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
