<?php

namespace fostercommerce\advanceddiscounts\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodActionRule;
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

			foreach ($discount->getActionCondition()->getConditionRules() as $rule) {
				if ($rule instanceof OrderActionRule) {
					$adjustment = $this->buildOrderAdjustment($rule, $order, $discount->name);
					if ($adjustment !== null) {
						$adjustments[] = $adjustment;
					}
				} elseif ($rule instanceof ShippingMethodActionRule) {
					$adjustment = $this->buildShippingAdjustment($rule, $order, $discount->name);
					if ($adjustment !== null) {
						$adjustments[] = $adjustment;
					}
				} elseif ($rule instanceof LineItemActionRule) {
					array_push($adjustments, ...$this->buildLineItemAdjustments($rule, $order, $discount->name));
				}
			}
		}

		return $adjustments;
	}

	private function buildOrderAdjustment(OrderActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
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

	private function buildShippingAdjustment(ShippingMethodActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
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
	private function buildLineItemAdjustments(LineItemActionRule $rule, Order $order, string $discountName): array
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
					(float) $rule->discountValue * ($rule->applyPer === LineItemActionRule::APPLY_PER_PURCHASABLE ? $lineItem->qty : 1),
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
