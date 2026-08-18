<?php

declare(strict_types=1);

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

	public const FILTER_SELECTED = 'matching';

	public const FILTER_CART_CONDITION = 'cartCondition';

	public const APPLY_PER_LINE_ITEM = 'lineItem';

	public const APPLY_PER_PURCHASABLE = 'purchasable';

	public string $discountType = 'flatAmount';

	public ?float $discountValue = null;

	public string $lineItemsFilter = self::FILTER_ALL;

	public string $applyPer = self::APPLY_PER_LINE_ITEM;

	/**
	 * @var class-string<ElementInterface>
	 */
	public string $purchasableType = Variant::class;

	/**
	 * @var int[]
	 */
	public array $purchasableIds = [];

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'rules.lineItems');
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
		if ($this->lineItemsFilter === self::FILTER_ALL) {
			return true;
		}

		if ($this->lineItemsFilter !== self::FILTER_SELECTED || $this->purchasableIds === []) {
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
		$discountTypeLabel = (DiscountType::tryFrom($this->discountType) ?? DiscountType::FlatAmount)->label();

		$filterOptions = [
			self::FILTER_ALL => Craft::t('advanced-discounts', 'rules.lineItemsFilter.all'),
			self::FILTER_SELECTED => Craft::t('advanced-discounts', 'rules.lineItemsFilter.selected'),
			self::FILTER_CART_CONDITION => Craft::t('advanced-discounts', 'rules.lineItemsFilter.cartCondition'),
		];

		$purchasableSelectHtml = '';

		if ($this->lineItemsFilter === self::FILTER_SELECTED) {
			$selectedElements = [];
			if ($this->purchasableIds !== []) {
				$purchasableType = $this->purchasableType;
				$selectedElements = $purchasableType::find()
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
				Html::hiddenLabel(Craft::t('advanced-discounts', 'rules.applyTo'), 'lineItemsFilter') .
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
				Html::hiddenLabel(Craft::t('advanced-discounts', 'rules.discountTypeLabel'), 'discountType') .
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
					'id' => 'discountValue',
					'name' => 'discountValue',
					'value' => $this->discountValue,
					'autocomplete' => false,
					'placeholder' => $discountTypeLabel,
					'class' => 'flex-grow flex-shrink',
				]) .
				Html::hiddenLabel(Craft::t('advanced-discounts', 'rules.applyPer'), 'applyPer') .
				Cp::selectHtml([
					'id' => 'applyPer',
					'name' => 'applyPer',
					'options' => [
						self::APPLY_PER_LINE_ITEM => Craft::t('advanced-discounts', 'rules.applyPer.lineItem'),
						self::APPLY_PER_PURCHASABLE => Craft::t('advanced-discounts', 'rules.applyPer.purchasable'),
					],
					'value' => $this->applyPer,
				]),
				[
					'class' => ['flex', 'flex-start', 'flex-grow'],
				]
			) .
			$purchasableSelectHtml,
			[
				'class' => ['flex', 'flex-start', 'flex-grow', 'advanced-discount-line-item-action'],
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
