<?php

namespace fostercommerce\coupons\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use fostercommerce\coupons\elements\conditions\LineItemActionRule;
use fostercommerce\coupons\elements\conditions\OrderActionRule;
use fostercommerce\coupons\enums\DiscountType;
use fostercommerce\coupons\Plugin;

class CouponAdjuster implements AdjusterInterface
{
	public function adjust(Order $order): array
	{
		if (! $order->couponCode) {
			return [];
		}

		$adjustments = [];

		foreach (Plugin::getInstance()->coupons->getAllCoupons() as $coupon) {
			if ($coupon->code !== $order->couponCode || ! $coupon->enabled) {
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
		$matchingOnly = $rule->lineItemsFilter === LineItemActionRule::FILTER_MATCHING;
		$lineItemCondition = $matchingOnly ? $rule->getLineItemCondition() : null;

		foreach ($order->getLineItems() as $lineItem) {
			if ($lineItemCondition !== null) {
				$purchasable = $lineItem->getPurchasable();
				if ($purchasable === null || ! $lineItemCondition->matchElement($purchasable)) {
					continue;
				}
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
