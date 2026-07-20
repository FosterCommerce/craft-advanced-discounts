<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as CommercePlugin;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\advanceddiscounts\enums\DiscountType;

class BogoCartActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	/**
	 * @var class-string<ElementInterface>
	 */
	public string $buyPurchasableType = Variant::class;

	/**
	 * @var int[]
	 */
	public array $buyPurchasableIds = [];

	public ?int $buyQuantity = null;

	/**
	 * @var class-string<ElementInterface>
	 */
	public string $freePurchasableType = Variant::class;

	/**
	 * @var int[]
	 */
	public array $freePurchasableIds = [];

	public ?int $freeQuantity = null;

	public bool $repeat = true;

	public string $discountType = DiscountType::Percentage;

	public ?float $discountValue = 100;

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'Buy X, Get Y');
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
			'buyPurchasableType' => $this->buyPurchasableType,
			'buyPurchasableIds' => $this->buyPurchasableIds,
			'buyQuantity' => $this->buyQuantity,
			'freePurchasableType' => $this->freePurchasableType,
			'freePurchasableIds' => $this->freePurchasableIds,
			'freeQuantity' => $this->freeQuantity,
			'repeat' => $this->repeat,
			'discountType' => $this->discountType,
			'discountValue' => $this->discountValue,
		]);
	}

	protected function inputHtml(): string
	{
		$discountTypeLabel = match ($this->discountType) {
			DiscountType::Percentage => Craft::t('advanced-discounts', 'Percentage'),
			default => Craft::t('advanced-discounts', 'Flat Amount'),
		};

		$buyRow = Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Buy'), 'buyPurchasableType') .
			Cp::selectHtml([
				'id' => 'buyPurchasableType',
				'name' => 'buyPurchasableType',
				'options' => $this->_purchasableTypeOptions(),
				'value' => $this->buyPurchasableType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Cp::elementSelectHtml([
				'elementType' => $this->buyPurchasableType,
				'id' => 'buyPurchasableIds',
				'name' => 'buyPurchasableIds',
				'elements' => $this->_selectedElements($this->buyPurchasableType, $this->buyPurchasableIds),
				'limit' => null,
			]) .
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Buy quantity'), 'buyQuantity') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'buyQuantity',
				'name' => 'buyQuantity',
				'value' => $this->buyQuantity,
				'autocomplete' => false,
				'placeholder' => Craft::t('advanced-discounts', 'Quantity'),
			]),
			[
				'class' => ['flex', 'flex-start', 'gap-s'],
			]
		);

		$freeRow = Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Get'), 'freePurchasableType') .
			Cp::selectHtml([
				'id' => 'freePurchasableType',
				'name' => 'freePurchasableType',
				'options' => $this->_purchasableTypeOptions(),
				'value' => $this->freePurchasableType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Cp::elementSelectHtml([
				'elementType' => $this->freePurchasableType,
				'id' => 'freePurchasableIds',
				'name' => 'freePurchasableIds',
				'elements' => $this->_selectedElements($this->freePurchasableType, $this->freePurchasableIds),
				'limit' => null,
			]) .
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Get quantity'), 'freeQuantity') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'freeQuantity',
				'name' => 'freeQuantity',
				'value' => $this->freeQuantity,
				'autocomplete' => false,
				'placeholder' => Craft::t('advanced-discounts', 'Quantity'),
			]),
			[
				'class' => ['flex', 'flex-start', 'gap-s'],
			]
		);

		$discountRow = Html::tag(
			'div',
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
			Cp::lightswitchHtml([
				'id' => 'repeat',
				'name' => 'repeat',
				'label' => Craft::t('advanced-discounts', 'Apply repeatedly'),
				'on' => $this->repeat,
			]),
			[
				'class' => ['flex', 'flex-start', 'gap-s'],
			]
		);

		return Html::tag(
			'div',
			$buyRow . $freeRow . $discountRow,
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
			[['buyPurchasableType', 'buyPurchasableIds', 'buyQuantity', 'freePurchasableType', 'freePurchasableIds', 'freeQuantity', 'repeat', 'discountType', 'discountValue'], 'safe'],
		]);
	}

	/**
	 * @param class-string<ElementInterface> $purchasableType
	 * @param int[] $purchasableIds
	 * @return array<int, ElementInterface|array<string, mixed>>
	 */
	private function _selectedElements(string $purchasableType, array $purchasableIds): array
	{
		if ($purchasableIds === []) {
			return [];
		}

		return $purchasableType::find()
			->id($purchasableIds)
			->status(null)
			->all();
	}

	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	private function _purchasableTypeOptions(): array
	{
		$options = [];

		foreach ((CommercePlugin::getInstance()?->getPurchasables()->getAllPurchasableElementTypes() ?? []) as $type) {
			/** @var string|ElementInterface $type */
			$options[] = [
				'value' => $type,
				'label' => $type::displayName(),
			];
		}

		return $options;
	}
}
