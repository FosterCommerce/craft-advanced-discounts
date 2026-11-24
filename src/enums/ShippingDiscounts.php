<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\enums;

use Craft;

enum ShippingDiscounts: string
{
	case Shipping = 'shipping';

	case Discount = 'discount';

	/**
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function options(): array
	{
		return array_map(static fn (self $shippingDiscounts): array => [
			'label' => $shippingDiscounts->label(),
			'value' => $shippingDiscounts->value,
		], self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Shipping => Craft::t('advanced-discounts', 'settings.shippingDiscounts.shipping'),
			self::Discount => Craft::t('advanced-discounts', 'settings.shippingDiscounts.discount'),
		};
	}
}
