<?php

namespace fostercommerce\advanceddiscounts\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodCartActionRule;
use fostercommerce\advanceddiscounts\enums\DiscountType;
use fostercommerce\advanceddiscounts\Plugin;

/**
 * Applies advanced-discount sales as order adjustments at cart recalc time.
 *
 * Scope: cart-based only. Discounts are evaluated against order state (totals,
 * quantities, shipping method) and exist as adjustments on the order, never as
 * a catalog price. A purchasable outside a cart has no sale price here; catalog
 * sales require Commerce catalog pricing, a separate mechanism.
 */
class DiscountAdjuster implements AdjusterInterface
{
	public function adjust(Order $order): array
	{
		$adjustments = [];

		foreach (Plugin::getInstance()->discounts->getAllDiscounts() as $discount) {
			if (! $discount->enabled) {
				continue;
			}

			if ($discount->code !== null && strcasecmp($discount->code, $order->couponCode ?? '') !== 0) {
				continue;
			}

			if (! $discount->getTriggerCondition()->matchElement($order)) {
				continue;
			}

			$actionRules = $discount->getCartActionCondition()->getConditionRules();
			$includeRuleLabel = count($actionRules) > 1;

			foreach ($actionRules as $rule) {
				$discountName = $includeRuleLabel ? $discount->name . ': ' . $rule->getLabel() : $discount->name;

				if ($rule instanceof OrderCartActionRule) {
					$adjustment = $this->buildOrderAdjustment($rule, $order, $discountName);
					if ($adjustment !== null) {
						$adjustments[] = $adjustment;
					}
				} elseif ($rule instanceof ShippingMethodCartActionRule) {
					$adjustment = $this->buildShippingAdjustment($rule, $order, $discountName);
					if ($adjustment !== null) {
						$adjustments[] = $adjustment;
					}
				} elseif ($rule instanceof LineItemCartActionRule) {
					array_push($adjustments, ...$this->buildLineItemAdjustments($rule, $order, $discountName));
				}
			}
		}

		return $adjustments;
	}

	private function buildOrderAdjustment(OrderCartActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
	{
		if (! $rule->discountValue) {
			return null;
		}

		$subtotal = $order->itemSubtotal;

		$amount = $rule->discountType === DiscountType::Percentage
			? -($subtotal * ($rule->discountValue / 100))
			: -min((float) $rule->discountValue, $subtotal);

		$adjustment = new OrderAdjustment();
		$adjustment->type = 'discount';
		$adjustment->name = $discountName;
		$adjustment->amount = $amount;
		$adjustment->orderId = $order->id;

		return $adjustment;
	}

	private function buildShippingAdjustment(ShippingMethodCartActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
	{
		if (! $rule->discountValue) {
			return null;
		}

		if (! $rule->matchElement($order)) {
			return null;
		}

		$shippingCost = $order->getTotalShippingCost();
		if ($shippingCost <= 0) {
			return null;
		}

		$amount = $rule->discountType === DiscountType::Percentage
			? -($shippingCost * ($rule->discountValue / 100))
			: -min((float) $rule->discountValue, $shippingCost);

		$adjustment = new OrderAdjustment();
		$adjustment->type = 'discount';
		$adjustment->name = $discountName;
		$adjustment->amount = $amount;
		$adjustment->orderId = $order->id;

		return $adjustment;
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildLineItemAdjustments(LineItemCartActionRule $rule, Order $order, string $discountName): array
	{
		if (! $rule->discountValue) {
			return [];
		}

		$adjustments = [];

		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable === null || ! $rule->matchElement($purchasable)) {
				continue;
			}

			$amount = $rule->discountType === DiscountType::Percentage
				? -($lineItem->subtotal * ($rule->discountValue / 100))
				: -min(
					(float) $rule->discountValue * ($rule->applyPer === LineItemCartActionRule::APPLY_PER_PURCHASABLE ? $lineItem->qty : 1),
					$lineItem->subtotal
				);

			$adjustment = new OrderAdjustment();
			$adjustment->type = 'discount';
			$adjustment->name = $discountName;
			$adjustment->amount = $amount;
			$adjustment->orderId = $order->id;
			$adjustment->lineItemId = $lineItem->id;
			$adjustment->setLineItem($lineItem);

			$adjustments[] = $adjustment;
		}

		return $adjustments;
	}
}
