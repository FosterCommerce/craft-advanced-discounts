<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use fostercommerce\advanceddiscounts\enums\TaxBasis;
use fostercommerce\advanceddiscounts\helpers\RemainingDiscount;
use fostercommerce\advanceddiscounts\Plugin;

/**
 * Applies advanced discounts as order adjustments.
 *
 * Purchasables outside a cart do not get a sale price from these discounts.
 */
class DiscountAdjuster implements AdjusterInterface
{
	protected TaxBasis $servesTaxBasis = TaxBasis::AfterDiscount;

	public function adjust(Order $order): array
	{
		if (Plugin::getInstance()->discounts->getTaxBasis($order) !== $this->servesTaxBasis) {
			return [];
		}

		$adjustments = [];
		$remainingDiscount = new RemainingDiscount($order);

		foreach (Plugin::getInstance()->discounts->getMatchingDiscounts($order) as $matchingDiscount) {
			$discountAdjustments = $remainingDiscount->cap($matchingDiscount->getType()->getAdjustments($order, $matchingDiscount));
			array_push($adjustments, ...$discountAdjustments);

			if ($discountAdjustments !== [] && $matchingDiscount->stopProcessing) {
				break;
			}
		}

		return $adjustments;
	}
}
