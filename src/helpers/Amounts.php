<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

use craft\helpers\MoneyHelper;
use Money\Money;
use RuntimeException;

final class Amounts
{
	public static function toMoney(float $value, string $currency): Money
	{
		$money = MoneyHelper::toMoney([
			'value' => (string) $value,
			'currency' => $currency,
		]);

		if ($money === false) {
			throw new RuntimeException("Could not build a money value for “{$value} {$currency}”.");
		}

		return $money;
	}
}
