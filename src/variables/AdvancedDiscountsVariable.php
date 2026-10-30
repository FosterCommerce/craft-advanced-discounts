<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\variables;

use craft\commerce\elements\Order;
use fostercommerce\advanceddiscounts\helpers\RemainingDiscount;
use fostercommerce\advanceddiscounts\Plugin;

class AdvancedDiscountsVariable
{
	/**
	 * @return string[]
	 */
	public function getMessages(Order $order): array
	{
		$messages = [];
		$remainingDiscount = new RemainingDiscount($order);

		foreach (Plugin::getInstance()->discounts->getMatchingDiscounts($order) as $matchingDiscount) {
			$discountType = $matchingDiscount->getType();
			array_push($messages, ...$discountType->getMessages($order, $matchingDiscount));

			if ($matchingDiscount->stopProcessing && $remainingDiscount->cap($discountType->getAdjustments($order, $matchingDiscount)) !== []) {
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
