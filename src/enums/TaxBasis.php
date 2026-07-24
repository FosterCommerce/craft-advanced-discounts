<?php

namespace fostercommerce\advanceddiscounts\enums;

use Craft;

abstract class TaxBasis
{
	public const AfterDiscount = 'afterDiscount';

	public const BeforeDiscount = 'beforeDiscount';

	/**
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function options(): array
	{
		return [
			[
				'label' => Craft::t('advanced-discounts', 'After discounts'),
				'value' => self::AfterDiscount,
			],
			[
				'label' => Craft::t('advanced-discounts', 'Before discounts'),
				'value' => self::BeforeDiscount,
			],
		];
	}
}
