<?php

namespace fostercommerce\coupons\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use fostercommerce\coupons\elements\conditions\OrderActionRule;
use fostercommerce\coupons\enums\DiscountType;
use fostercommerce\coupons\enums\ItemsChoice;
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
			if ($coupon->code !== $order->couponCode || $coupon->enabled) {
				continue;
			}

			if (! $coupon->getTriggerCondition()->matchElement($order)) {
				continue;
			}

			foreach ($coupon->getActionCondition()->getConditionRules() as $rule) {
				if ($rule instanceof OrderActionRule) {
					array_push($adjustments, ...$this->buildLineItemAdjustments($rule, $order, $coupon->title));
				}
			}
		}

		return $adjustments;
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildLineItemAdjustments(OrderActionRule $rule, Order $order, string $couponTitle): array
	{
		$adjustments = [];
		$itemCondition = $rule->getOrderActionCondition();
		$applied = 0;

		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable === null) {
				continue;
			}

			if (! $itemCondition->matchElement($purchasable)) {
				continue;
			}

			if ($rule->itemsChoice === ItemsChoice::NumberOfItems) {
				if ($applied >= (int) $rule->numberOfItems) {
					break;
				}
				$applied++;
			}

			$amount = $rule->discountType === DiscountType::Percentage
				? -($lineItem->subtotal * ($rule->discountValue / 100))
				: -min((float) $rule->discountValue, $lineItem->subtotal);

			$adjustment = new OrderAdjustment();
			$adjustment->type = 'discount';
			$adjustment->name = $couponTitle;
			$adjustment->amount = $amount;
			$adjustment->orderId = $order->id;
			$adjustment->lineItemId = $lineItem->id;
			$adjustment->setLineItem($lineItem);

			$adjustments[] = $adjustment;
		}

		return $adjustments;
	}
}
