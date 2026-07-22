<?php

namespace fostercommerce\advanceddiscounts\variables;

use craft\commerce\elements\Order;
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

			if ($discount->code !== null && strcasecmp($discount->code, $order->couponCode ?? '') !== 0) {
				continue;
			}

			if (! $discount->getGlobalCartCondition()->matchElement($order)) {
				continue;
			}

			array_push($messages, ...$discount->getType()->getMessages($order, $discount));
		}

		return $messages;
	}

	public function getMessage(Order $order): ?string
	{
		return $this->getMessages($order)[0] ?? null;
	}
}
