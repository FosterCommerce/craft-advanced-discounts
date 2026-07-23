<?php

namespace fostercommerce\advanceddiscounts\helpers;

use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\commerce\elements\Order;
use craft\elements\conditions\ElementConditionInterface;
use fostercommerce\advanceddiscounts\elements\conditions\HasPurchasableConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderConditionRule;

final class PromotableThreshold
{
	/**
	 * Whether the promotable portion of the cart satisfies a condition's value/quantity thresholds.
	 *
	 * Commerce order rules read whole-order totals, which include non-promotable line items.
	 * A tier must only count as reached when the promotable items alone cross its threshold.
	 */
	public static function reached(ElementConditionInterface $condition, Order $order): bool
	{
		$amountRemaining = self::amountRemaining($condition, $order);
		if ($amountRemaining !== null && $amountRemaining > 0) {
			return false;
		}

		$quantityRemaining = self::quantityRemaining($condition, $order);
		return $quantityRemaining === null || $quantityRemaining <= 0;
	}

	public static function amountRemaining(ElementConditionInterface $condition, Order $order): ?float
	{
		$ruleFieldMap = [
			ItemSubtotalConditionRule::class => ['itemSubtotal', 'subtotal'],
			ItemTotalConditionRule::class => ['itemTotal', 'total'],
			TotalPriceConditionRule::class => ['totalPrice', 'total'],
			TotalConditionRule::class => ['total', 'total'],
		];

		foreach ($condition->getConditionRules() as $triggerRule) {
			if (! $triggerRule instanceof OrderConditionRule) {
				continue;
			}

			foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
				foreach ($ruleFieldMap as $ruleClass => [$orderField, $lineItemField]) {
					if (
						$orderRule instanceof $ruleClass
						&& property_exists($orderRule, 'value')
						&& property_exists($orderRule, 'operator')
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						$promotableValue = (float) $order->{$orderField};
						foreach ($order->getLineItems() as $lineItem) {
							if (! $lineItem->getIsPromotable()) {
								$promotableValue -= (float) $lineItem->{$lineItemField};
							}
						}

						return max(0.0, (float) $orderRule->value - $promotableValue);
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
						$promotableQty = 0;
						foreach ($order->getLineItems() as $lineItem) {
							if ($lineItem->getIsPromotable()) {
								$promotableQty += $lineItem->qty;
							}
						}

						return max(0, (int) $orderRule->value - $promotableQty);
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
						$purchasableId = (int) $rule->getElementId();
						$totalQty = 0;

						foreach ($order->getLineItems() as $lineItem) {
							$purchasable = $lineItem->getPurchasable();
							if ($purchasable !== null && $lineItem->getIsPromotable() && Purchasables::matches($purchasable, $rule->purchasableType, [$purchasableId])) {
								$totalQty += $lineItem->qty;
							}
						}

						return max(0, $rule->quantity - $totalQty);
					}
				}
			}
		}

		return null;
	}
}
