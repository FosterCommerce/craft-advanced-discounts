<?php

namespace fostercommerce\advanceddiscounts\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderActionRule;
use fostercommerce\advanceddiscounts\enums\DiscountType;
use fostercommerce\advanceddiscounts\Plugin;

class DiscountAdjuster implements AdjusterInterface
{
	public function adjust(Order $order): array
	{
		$adjustments = [];

		foreach (Plugin::getInstance()->coupons->getAllCoupons() as $coupon) {
			if (! $coupon->enabled) {
				continue;
			}

			if ($coupon->code !== null && strcasecmp($coupon->code, $order->couponCode ?? '') !== 0) {
				continue;
			}

			if (! $coupon->getTriggerCondition()->matchElement($order)) {
				continue;
			}

			foreach ($coupon->getActionCondition()->getConditionRules() as $rule) {
				if ($rule instanceof OrderActionRule) {
					$adjustment = $this->buildOrderAdjustment($rule, $order, $coupon->name);
					if ($adjustment !== null) {
						$adjustments[] = $adjustment;
					}
				} elseif ($rule instanceof LineItemActionRule) {
					array_push($adjustments, ...$this->buildLineItemAdjustments($rule, $order, $coupon->name));
				}
			}
		}

		return $adjustments;
	}

	private function buildOrderAdjustment(OrderActionRule $rule, Order $order, string $couponName): ?OrderAdjustment
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
		$adjustment->name = $couponName;
		$adjustment->amount = $amount;
		$adjustment->orderId = $order->id;

		return $adjustment;
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildLineItemAdjustments(LineItemActionRule $rule, Order $order, string $couponName): array
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
				: -min((float) $rule->discountValue, $lineItem->subtotal);

			$adjustment = new OrderAdjustment();
			$adjustment->type = 'discount';
			$adjustment->name = $couponName;
			$adjustment->amount = $amount;
			$adjustment->orderId = $order->id;
			$adjustment->lineItemId = $lineItem->id;
			$adjustment->setLineItem($lineItem);

			$adjustments[] = $adjustment;
		}

		return $adjustments;
	}
}
