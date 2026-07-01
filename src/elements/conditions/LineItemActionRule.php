<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use fostercommerce\advanceddiscounts\enums\DiscountType;

class LineItemActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public const FILTER_ALL = 'all';

	public const FILTER_MATCHING = 'matching';

	public string $discountType = DiscountType::FlatAmount;

	public ?float $discountValue = null;

	public string $lineItemsFilter = self::FILTER_ALL;

	public ?ElementConditionInterface $_lineItemCondition = null;

	public function __construct($config = [])
	{
		$config['lineItemCondition'] = isset($config['lineItemCondition']) ? $config['lineItemCondition'] : ($config['attributes']['lineItemCondition'] ?? []);
		parent::__construct($config);
	}

	public function getLineItemCondition(): ElementConditionInterface
	{
		$condition = $this->_lineItemCondition ?? new LineItemCondition();
		$condition->mainTag = 'div';
		$condition->name = 'lineItemCondition';
		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed> $condition
	 */
	public function setLineItemCondition(ElementConditionInterface|string|array $condition): void
	{
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = LineItemCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_lineItemCondition = $condition;
	}

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
			'lineItemsFilter' => $this->lineItemsFilter,
			'lineItemCondition' => $this->_lineItemCondition?->getConfig() ?? [],
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

		$conditionBuilderHtml = $this->lineItemsFilter === self::FILTER_MATCHING
			? $this->getLineItemCondition()->getBuilderHtml()
			: '';

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
				]),
				[
					'class' => ['flex', 'flex-start', 'flex-grow'],
				]
			) .
			$conditionBuilderHtml,
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
			[['discountType', 'discountValue', 'lineItemsFilter'], 'safe'],
		]);
	}
}
