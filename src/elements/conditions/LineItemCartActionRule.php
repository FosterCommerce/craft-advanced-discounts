<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Variant;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\advanceddiscounts\enums\DiscountType;
use fostercommerce\advanceddiscounts\helpers\Purchasables;

class LineItemCartActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public const FILTER_ALL = 'all';

	public const FILTER_MATCHING = 'matching';

	public const APPLY_PER_LINE_ITEM = 'lineItem';

	public const APPLY_PER_PURCHASABLE = 'purchasable';

	public string $discountType = DiscountType::FlatAmount;

	public ?float $discountValue = null;

	public string $lineItemsFilter = self::FILTER_ALL;

	public string $applyPer = self::APPLY_PER_LINE_ITEM;

	public string $purchasableType = Variant::class;

	/**
	 * @var int[]
	 */
	public array $purchasableIds = [];

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'Line Items');
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
		if ($this->lineItemsFilter !== self::FILTER_MATCHING) {
			return true;
		}

		if (empty($this->purchasableIds)) {
			return false;
		}

		return Purchasables::matches($element, $this->purchasableType, $this->purchasableIds);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'discountType' => $this->discountType,
			'discountValue' => $this->discountValue,
			'lineItemsFilter' => $this->lineItemsFilter,
			'applyPer' => $this->applyPer,
			'purchasableType' => $this->purchasableType,
			'purchasableIds' => $this->purchasableIds,
		]);
	}

	protected function inputHtml(): string
	{
		$discountTypeLabel = match ($this->discountType) {
			DiscountType::Percentage => Craft::t('advanced-discounts', 'Percentage'),
			default => Craft::t('advanced-discounts', 'Flat Amount'),
		};

		$filterOptions = [
			self::FILTER_ALL => Craft::t('advanced-discounts', 'All line items'),
			self::FILTER_MATCHING => Craft::t('advanced-discounts', 'Matching line items'),
		];

		$purchasableSelectHtml = '';

		if ($this->lineItemsFilter === self::FILTER_MATCHING) {
			$selectedElements = [];
			if (! empty($this->purchasableIds)) {
				/** @var class-string<ElementInterface> $type */
				$type = $this->purchasableType;
				$selectedElements = $type::find()
					->id($this->purchasableIds)
					->status(null)
					->all();
			}

			$purchasableSelectHtml = Html::tag(
				'div',
				Cp::selectHtml([
					'id' => 'purchasableType',
					'name' => 'purchasableType',
					'options' => Purchasables::typeOptions(),
					'value' => $this->purchasableType,
					'inputAttributes' => [
						'hx' => [
							'post' => UrlHelper::actionUrl('conditions/render'),
						],
					],
				]) .
				Cp::elementSelectHtml([
					'elementType' => $this->purchasableType,
					'id' => 'purchasableIds',
					'name' => 'purchasableIds',
					'elements' => $selectedElements,
					'limit' => null,
				]),
				[
					'class' => ['flex', 'flex-start', 'gap-s'],
				]
			);
		}

		return Html::tag(
			'div',
			Html::tag(
				'div',
				Html::hiddenLabel(Craft::t('advanced-discounts', 'Apply to'), 'lineItemsFilter') .
				Cp::selectHtml([
					'id' => 'lineItemsFilter',
					'name' => 'lineItemsFilter',
					'options' => $filterOptions,
					'value' => $this->lineItemsFilter,
					'inputAttributes' => [
						'hx' => [
							'post' => UrlHelper::actionUrl('conditions/render'),
						],
					],
				]) .
				Html::hiddenLabel(Craft::t('advanced-discounts', 'Discount Type'), 'discountType') .
				Cp::selectHtml([
					'id' => 'discountType',
					'name' => 'discountType',
					'options' => [
						DiscountType::FlatAmount => Craft::t('advanced-discounts', 'Discount a flat amount'),
						DiscountType::Percentage => Craft::t('advanced-discounts', 'Discount a percentage'),
					],
					'value' => $this->discountType,
					'inputAttributes' => [
						'hx' => [
							'post' => UrlHelper::actionUrl('conditions/render'),
						],
					],
				]) .
				Html::hiddenLabel(Craft::t('advanced-discounts', 'Discount value'), 'discountValue') .
				Cp::textHtml([
					'type' => 'number',
					'id' => 'discountValue',
					'name' => 'discountValue',
					'value' => $this->discountValue,
					'autocomplete' => false,
					'placeholder' => $discountTypeLabel,
					'class' => 'flex-grow flex-shrink',
				]) .
				Html::hiddenLabel(Craft::t('advanced-discounts', 'Apply per'), 'applyPer') .
				Cp::selectHtml([
					'id' => 'applyPer',
					'name' => 'applyPer',
					'options' => [
						self::APPLY_PER_LINE_ITEM => Craft::t('advanced-discounts', 'Per line item'),
						self::APPLY_PER_PURCHASABLE => Craft::t('advanced-discounts', 'Per purchasable'),
					],
					'value' => $this->applyPer,
				]),
				[
					'class' => ['flex', 'flex-start', 'flex-grow'],
				]
			) .
			$purchasableSelectHtml,
			[
				'class' => ['flex', 'flex-start', 'flex-grow'],
				'style' => [
					'flex-direction' => 'column',
				],
			]
		);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['discountType', 'discountValue', 'lineItemsFilter', 'applyPer', 'purchasableType', 'purchasableIds'], 'safe'],
		]);
	}
}
