<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\enums;

use Craft;

enum DiscountType: string
{
	case FlatAmount = 'flatAmount';

	case Percentage = 'percentage';

	/**
	 * @return array<string, string> Select options, keyed by stored value
	 */
	public static function actionOptions(): array
	{
		return [
			self::FlatAmount->value => Craft::t('advanced-discounts', 'rules.discountType.flatAmountAction'),
			self::Percentage->value => Craft::t('advanced-discounts', 'rules.discountType.percentageAction'),
		];
	}

	/**
	 * @return string[]
	 */
	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public function label(): string
	{
		return match ($this) {
			self::FlatAmount => Craft::t('advanced-discounts', 'rules.discountType.flatAmount'),
			self::Percentage => Craft::t('advanced-discounts', 'rules.discountType.percentage'),
		};
	}
}
