<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\coupons\enums\DiscountType;

class OrderActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public string $discountType = DiscountType::FlatAmount;

	public ?float $discountValue = null;

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Order');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
	}

	public function matchElement(ElementInterface $element): bool
	{
		return true;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'discountType' => $this->discountType,
			'discountValue' => $this->discountValue,
		]);
	}

	protected function inputHtml(): string
	{
		$discountTypeLabel = match ($this->discountType) {
			DiscountType::Percentage => Craft::t('coupons', 'Percentage'),
			default => Craft::t('coupons', 'Flat Amount'),
		};

		return Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('coupons', 'Discount Type'), 'discountType') .
			Cp::selectHtml([
				'id' => 'discountType',
				'name' => 'discountType',
				'options' => [
					DiscountType::FlatAmount => Craft::t('coupons', 'Discount a flat amount'),
					DiscountType::Percentage => Craft::t('coupons', 'Discount a percentage'),
				],
				'value' => $this->discountType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Html::hiddenLabel(Craft::t('coupons', 'Discount value'), 'discountValue') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'discountValue',
				'name' => 'discountValue',
				'value' => $this->discountValue,
				'autocomplete' => false,
				'placeholder' => $discountTypeLabel,
				'class' => 'flex-grow flex-shrink',
			]),
			[
				'class' => ['flex', 'flex-start', 'flex-grow'],
			]
		);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['discountType', 'discountValue'], 'safe'],
		]);
	}
}
