<?php

namespace fostercommerce\advanceddiscounts\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use fostercommerce\advanceddiscounts\enums\TaxBasis;
use fostercommerce\advanceddiscounts\helpers\PromotableThreshold;
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
	protected string $servesTaxBasis = TaxBasis::AfterDiscount;

	public function adjust(Order $order): array
	{
		if (Plugin::getInstance()->discounts->getTaxBasis($order) !== $this->servesTaxBasis) {
			return [];
		}

		$adjustments = [];

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

			$discountAdjustments = $discount->getType()->getAdjustments($order, $discount);
			array_push($adjustments, ...$discountAdjustments);

			if ($discountAdjustments !== [] && $discount->stopProcessing) {
				break;
			}
		}

		return $adjustments;
	}
}
