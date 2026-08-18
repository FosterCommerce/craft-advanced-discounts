<?php

declare(strict_types=1);

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

			$discountType = $discount->getType();
			array_push($messages, ...$discountType->getMessages($order, $discount));

			if ($discount->stopProcessing && $discountType->getAdjustments($order, $discount) !== []) {
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
