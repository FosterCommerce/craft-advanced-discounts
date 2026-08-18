<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\enums;

use Craft;

enum TaxBasis: string
{
	case AfterDiscount = 'afterDiscount';

	case BeforeDiscount = 'beforeDiscount';

	/**
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function options(): array
	{
		return array_map(static fn (self $taxBasis): array => [
			'label' => $taxBasis->label(),
			'value' => $taxBasis->value,
		], self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::AfterDiscount => Craft::t('advanced-discounts', 'settings.taxBasis.afterDiscount'),
			self::BeforeDiscount => Craft::t('advanced-discounts', 'settings.taxBasis.beforeDiscount'),
		};
	}
}
