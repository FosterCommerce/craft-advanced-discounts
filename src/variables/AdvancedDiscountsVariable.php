<?php

namespace fostercommerce\advanceddiscounts\variables;

use craft\commerce\elements\Order;
use fostercommerce\advanceddiscounts\helpers\PromotableThreshold;
use fostercommerce\advanceddiscounts\Plugin;

class AdvancedDiscountsVariable
{
	/**
	 * @return string[]
	 */
	public function getMessages(Order $order): array
	{
		$messages = [];

		foreach (Plugin::getInstance()->discounts->getAllDiscounts() as $discount) {
			if (! $discount->enabled) {
				continue;
			}

			if (! $discount->matchesCouponCode($order->couponCode)) {
				continue;
			}

			if (! PromotableThreshold::matches($discount->getGlobalCartCondition(), $order)) {
				continue;
			}

			array_push($messages, ...$discount->getType()->getMessages($order, $discount));

			if ($discount->stopProcessing && $discount->getType()->getAdjustments($order, $discount) !== []) {
				break;
			}
		}

		return $messages;
	}

	public function getMessage(Order $order): ?string
	{
		return $this->getMessages($order)[0] ?? null;
	}
}
