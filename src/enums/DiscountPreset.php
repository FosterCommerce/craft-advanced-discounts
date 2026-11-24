<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\enums;

use Craft;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodCartActionRule;
use fostercommerce\advanceddiscounts\models\Discount;

enum DiscountPreset: string
{
	case FreeItems = 'freeItems';

	case FreeShipping = 'freeShipping';

	case FreeOrder = 'freeOrder';

	/**
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function options(): array
	{
		return array_map(static fn (self $preset): array => [
			'label' => $preset->label(),
			'value' => $preset->value,
		], self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::FreeItems => Craft::t('advanced-discounts', 'preset.freeItems'),
			self::FreeShipping => Craft::t('advanced-discounts', 'preset.freeShipping'),
			self::FreeOrder => Craft::t('advanced-discounts', 'preset.freeOrder'),
		};
	}

	public function createDiscount(): Discount
	{
		// Discount each line, since an Item Subtotal discount doesn't lower item tax under Commerce's tax engine
		$ruleClasses = match ($this) {
			self::FreeItems => [LineItemCartActionRule::class],
			self::FreeShipping => [ShippingMethodCartActionRule::class],
			self::FreeOrder => [LineItemCartActionRule::class, ShippingMethodCartActionRule::class],
		};

		$discount = new Discount([
			'name' => $this->label(),
		]);
		$discount->setPanels([[
			'name' => $this->label(),
			'cartActionCondition' => [
				'conditionRules' => array_map(static fn (string $ruleClass): array => [
					'class' => $ruleClass,
					'discountType' => DiscountType::Percentage->value,
					'discountValue' => 100,
				], $ruleClasses),
			],
		]]);

		return $discount;
	}
}
