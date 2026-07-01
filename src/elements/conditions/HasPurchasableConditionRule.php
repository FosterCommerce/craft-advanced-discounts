<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseElementSelectConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\db\OrderQuery;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;

/**
 * @method array|string|null paramValue(?callable $normalizeValue = null)
 */
class HasPurchasableConditionRule extends BaseElementSelectConditionRule implements ElementConditionRuleInterface
{
	/**
	 * @var class-string<ElementInterface>
	 */
	public string $purchasableType = Variant::class;

	public ?int $quantity = null;

	public function getLabel(): string
	{
		return Craft::t('commerce', 'Has Purchasable');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
		if ($this->getElementId() === null) {
			return;
		}

		/** @var OrderQuery $query */
		$query->hasPurchasables([(int) $this->getElementId()]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		$purchasableId = (int) $this->getElementId();
		if ($purchasableId === 0 || ! $element instanceof Order) {
			return false;
		}

		$totalQty = 0;

		foreach ($element->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable !== null && (int) $purchasable->id === $purchasableId) {
				if ($this->quantity === null) {
					return true;
				}
				$totalQty += $lineItem->qty;
			}
		}

		if ($this->quantity === null) {
			return false;
		}

		return $totalQty === $this->quantity;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return [
			...parent::getConfig(),
			'purchasableType' => $this->purchasableType,
			'quantity' => $this->quantity,
		];
	}

	/**
	 * @return class-string<ElementInterface>
	 */
	protected function elementType(): string
	{
		return $this->purchasableType;
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		$rules = parent::defineRules();
		$rules[] = [['purchasableType', 'quantity'], 'safe'];

		return $rules;
	}

	protected function inputHtml(): string
	{
		$id = 'purchasable-type';

		$elementRow = Html::tag(
			'div',
			Cp::selectHtml([
				'id' => $id,
				'name' => 'purchasableType',
				'options' => $this->_purchasableTypeOptions(),
				'value' => $this->purchasableType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			parent::inputHtml(),
			['class' => ['flex', 'flex-start']]
		);

		$quantityRow = Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('advanced-discounts', 'Quantity'), 'quantity') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'quantity',
				'name' => 'quantity',
				'value' => $this->quantity,
				'placeholder' => Craft::t('advanced-discounts', 'Any qty'),
				'autocomplete' => false,
			]),
			['class' => ['flex', 'flex-start']]
		);

		return Html::hiddenLabel($this->getLabel(), $id) .
			Html::tag(
				'div',
				$elementRow . $quantityRow,
				[
					'class' => ['flex', 'flex-start'],
					'style' => ['flex-direction' => 'column'],
				]
			);
	}

	protected function selectionCondition(): ?ElementConditionInterface
	{
		return Craft::$app->getConditions()->createCondition([
			'class' => OrderCondition::class,
		]);
	}

	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	private function _purchasableTypeOptions(): array
	{
		$options = [];

		foreach ((Plugin::getInstance()?->getPurchasables()->getAllPurchasableElementTypes() ?? []) as $elementType) {
			/** @var string|ElementInterface $elementType */
			/** @phpstan-var class-string<ElementInterface>|ElementInterface $elementType */
			$options[] = [
				'value' => $elementType,
				'label' => $elementType::displayName(),
			];
		}

		return $options;
	}
}
