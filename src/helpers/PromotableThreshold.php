<?php

namespace fostercommerce\advanceddiscounts\helpers;

use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\commerce\elements\Order;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\fields\conditions\MoneyFieldConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\HasPurchasableConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderConditionRule;

/**
 * Evaluates discount conditions against the promotable portion of an order.
 *
 * Commerce order rules read whole-order totals, which include non-promotable line items.
 * A condition must only count as met when the promotable items alone satisfy it, so a
 * fully non-promotable cart behaves like an empty one (promotable subtotal 0, qty 0).
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
						return max(0.0, (float) $orderRule->value - self::promotableValue($order, $orderField, $lineItemField));
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
		foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
			if ($orderRule instanceof MoneyFieldConditionRule && isset(self::VALUE_RULE_FIELDS[$orderRule::class])) {
				[$orderField, $lineItemField] = self::VALUE_RULE_FIELDS[$orderRule::class];
				if (! self::compare(self::promotableValue($order, $orderField, $lineItemField), $orderRule->operator, $orderRule->value, $orderRule->maxValue)) {
					return false;
				}

				continue;
			}

			if ($orderRule instanceof TotalQtyConditionRule) {
				if (! self::compare((float) self::promotableQty($order), $orderRule->operator, $orderRule->value, $orderRule->maxValue)) {
					return false;
				}

				continue;
			}

			if ($orderRule instanceof ElementConditionRuleInterface && ! $orderRule->matchElement($order)) {
				return false;
			}
		}

		return true;
	}

	private static function lineItemConditionMatches(LineItemConditionRule $triggerRule, Order $order): bool
	{
		foreach ($triggerRule->getLineItemCondition()->getConditionRules() as $rule) {
			if ($rule instanceof HasPurchasableConditionRule) {
				if (! self::compare((float) self::promotableMatchingQty($rule, $order), $rule->operator, (string) $rule->quantity, null)) {
					return false;
				}

				continue;
			}

			if ($rule instanceof ElementConditionRuleInterface && ! $rule->matchElement($order)) {
				return false;
			}
		}

		return true;
	}

	private static function promotableValue(Order $order, string $orderField, string $lineItemField): float
	{
		$value = (float) $order->{$orderField};
		foreach ($order->getLineItems() as $lineItem) {
			if (! $lineItem->getIsPromotable()) {
				$value -= (float) $lineItem->{$lineItemField};
			}
		}

		return $value;
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
	 * Mirrors craft\base\conditions\BaseNumberConditionRule::matchValue — between is inclusive, empty bounds are skipped.
	 */
	private static function compare(float $value, string $operator, ?string $min, ?string $max): bool
	{
		if ($operator === 'between') {
			if (($min !== null && $min !== '') && $value < (float) $min) {
				return false;
			}

			return ! (($max !== null && $max !== '') && $value > (float) $max);
		}

		if ($min === null || $min === '') {
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
			default => false,
		};
	}
}
