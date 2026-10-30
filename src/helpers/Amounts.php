<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

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
}
