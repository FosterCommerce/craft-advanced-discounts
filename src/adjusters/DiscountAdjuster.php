<?php

namespace fostercommerce\advanceddiscounts\adjusters;

use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
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

			if (! $discount->getGlobalCartCondition()->matchElement($order)) {
				continue;
			}

			array_push($adjustments, ...$discount->getType()->getAdjustments($order, $discount));
		}

		return $adjustments;
	}
}
