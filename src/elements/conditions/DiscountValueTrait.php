<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\advanceddiscounts\enums\DiscountType;

/**
 * Discount type and value inputs shared by the cart action rules.
 */
trait DiscountValueTrait
{
	private function discountValueInputHtml(): string
	{
		$discountTypeLabel = (DiscountType::tryFrom($this->discountType) ?? DiscountType::FlatAmount)->label();

		return Html::hiddenLabel(Craft::t('advanced-discounts', 'rules.discountTypeLabel'), 'discountType') .
			Cp::selectHtml([
				'id' => 'discountType',
				'name' => 'discountType',
				'options' => DiscountType::actionOptions(),
				'value' => $this->discountType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Html::hiddenLabel(Craft::t('advanced-discounts', 'rules.discountValue'), 'discountValue') .
			Cp::textHtml([
				'type' => 'number',
				'min' => 0,
				'id' => 'discountValue',
				'name' => 'discountValue',
				'value' => $this->discountValue,
				'autocomplete' => false,
				'placeholder' => $discountTypeLabel,
				'class' => 'flex-grow flex-shrink',
			]);
	}

	/**
	 * @return array<int, mixed>
	 */
	private function discountValueRules(): array
	{
		return [
			[['discountType', 'discountValue'], 'safe'],
			[['discountValue'],
				'number',
				'min' => 0,
				'tooSmall' => Craft::t('advanced-discounts', 'rules.error.discountValueMin')],
			[['discountValue'],
				'number',
				'max' => 100,
				'tooBig' => Craft::t('advanced-discounts', 'rules.error.percentageMax'),
				'when' => static fn (self $rule): bool => $rule->discountType === DiscountType::Percentage->value],
		];
	}
}
