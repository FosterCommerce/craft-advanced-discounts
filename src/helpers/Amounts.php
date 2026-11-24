<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

use craft\commerce\adjusters\Shipping as ShippingAdjuster;
use craft\commerce\elements\Order;
use craft\helpers\MoneyHelper;
use Money\Money;

final class Amounts
{
	public static function toMoney(float $value, string $currency): Money
	{
		/** @var Money $money */
		$money = MoneyHelper::toMoney([
			'value' => (string) $value,
			'currency' => $currency,
		]);

		return $money;
	}

	/**
	 * The order's shipping cost before this plugin's discounts, which can be `shipping` adjustments.
	 */
	public static function shippingCost(Order $order): Money
	{
		$currency = (string) $order->currency;
		$shippingCost = self::toMoney(0, $currency);
		foreach ($order->getAdjustmentsByType(ShippingAdjuster::ADJUSTMENT_TYPE) as $adjustment) {
			if (! $adjustment->included && ! isset($adjustment->sourceSnapshot['advancedDiscountId'])) {
				$shippingCost = $shippingCost->add(self::toMoney($adjustment->amount, $currency));
			}
		}

		return $shippingCost;
	}
}
