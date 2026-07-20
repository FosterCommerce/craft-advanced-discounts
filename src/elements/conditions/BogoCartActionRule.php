<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\advanceddiscounts\enums\DiscountType;
use fostercommerce\advanceddiscounts\helpers\Purchasables;

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
	public string $discountedPurchasableType = Variant::class;

	/**
	 * @var int[]
	 */
	public array $discountedPurchasableIds = [];

	public ?int $discountedQuantity = null;

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

	public function bundleSize(): int
	{
		$buyVariantIds = Purchasables::expandToVariantIds($this->buyPurchasableType, $this->buyPurchasableIds);
		$discountedVariantIds = Purchasables::expandToVariantIds($this->discountedPurchasableType, $this->discountedPurchasableIds);
		$overlaps = array_intersect($buyVariantIds, $discountedVariantIds) !== [];

		return ($this->buyQuantity ?? 0) + ($overlaps ? ($this->discountedQuantity ?? 0) : 0);
	}

	public function earnedQuantity(Order $order): int
	{
		if (! $this->buyQuantity || ! $this->discountedQuantity) {
			return 0;
		}

		$bundleSize = $this->bundleSize();
		$buyQty = $this->buyCartQuantity($order);

		return $this->repeat
			? intdiv($buyQty, $bundleSize) * $this->discountedQuantity
			: ($buyQty >= $bundleSize ? $this->discountedQuantity : 0);
	}

	public function buyQuantityRemaining(Order $order): int
	{
		if (! $this->buyQuantity || ! $this->discountedQuantity) {
			return 0;
		}

		$bundleSize = $this->bundleSize();
		$remainder = $this->buyCartQuantity($order) % $bundleSize;

		return $remainder === 0 ? $bundleSize : $bundleSize - $remainder;
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
			'discountedPurchasableType' => $this->discountedPurchasableType,
			'discountedPurchasableIds' => $this->discountedPurchasableIds,
			'discountedQuantity' => $this->discountedQuantity,
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
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Customer buys'), 'buyPurchasableType') .
			Cp::selectHtml([
				'id' => 'buyPurchasableType',
				'name' => 'buyPurchasableType',
				'options' => Purchasables::typeOptions(),
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
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Customer buys quantity'), 'buyQuantity') .
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

		$discountedRow = Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Customer gets'), 'discountedPurchasableType') .
			Cp::selectHtml([
				'id' => 'discountedPurchasableType',
				'name' => 'discountedPurchasableType',
				'options' => Purchasables::typeOptions(),
				'value' => $this->discountedPurchasableType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Cp::elementSelectHtml([
				'elementType' => $this->discountedPurchasableType,
				'id' => 'discountedPurchasableIds',
				'name' => 'discountedPurchasableIds',
				'elements' => $this->_selectedElements($this->discountedPurchasableType, $this->discountedPurchasableIds),
				'limit' => null,
			]) .
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Customer gets quantity'), 'discountedQuantity') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'discountedQuantity',
				'name' => 'discountedQuantity',
				'value' => $this->discountedQuantity,
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
			]),
			[
				'class' => ['flex', 'flex-start', 'gap-s'],
			]
		);

		$repeatRow = Html::tag(
			'div',
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

		$buyHeading = Html::tag('div', Craft::t('advanced-discounts', 'Customer buys'), [
			'style' => [
				'font-weight' => 'bold',
			],
		]);

		$discountedHeading = Html::tag('div', Craft::t('advanced-discounts', 'Customer gets'), [
			'style' => [
				'font-weight' => 'bold',
			],
		]);

		return Html::tag(
			'div',
			$buyHeading . $buyRow . $discountedHeading . $discountedRow . $discountRow . $repeatRow,
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
			[['buyPurchasableType', 'buyPurchasableIds', 'buyQuantity', 'discountedPurchasableType', 'discountedPurchasableIds', 'discountedQuantity', 'repeat', 'discountType', 'discountValue'], 'safe'],
		]);
	}

	private function buyCartQuantity(Order $order): int
	{
		$totalQty = 0;

		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable !== null && Purchasables::matches($purchasable, $this->buyPurchasableType, $this->buyPurchasableIds)) {
				$totalQty += $lineItem->qty;
			}
		}

		return $totalQty;
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
}
