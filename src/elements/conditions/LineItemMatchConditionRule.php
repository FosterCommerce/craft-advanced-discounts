<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseElementSelectConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use craft\commerce\models\LineItem;
use craft\elements\Category;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\advanceddiscounts\helpers\Purchasables;

class LineItemMatchConditionRule extends BaseElementSelectConditionRule implements ElementConditionRuleInterface
{
	public const MATCH_PURCHASABLE = 'purchasable';

	public const MATCH_RELATED = 'related';

	public string $matchType = self::MATCH_PURCHASABLE;

	/**
	 * @var class-string<ElementInterface>
	 */
	public string $elementType = Variant::class;

	public string $operator = self::OPERATOR_GTE;

	public ?int $quantity = 1;

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'conditions.lineItemMatch');
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
		if (! $element instanceof Order) {
			return false;
		}

		return $this->quantityMatches($this->matchedQuantity($element->getLineItems()));
	}

	/**
	 * Total quantity across the line items this rule selects.
	 *
	 * @param LineItem[] $lineItems
	 */
	public function matchedQuantity(array $lineItems): int
	{
		$elementId = (int) $this->getElementId();
		if ($elementId === 0) {
			return 0;
		}

		$variantIds = $this->matchType === self::MATCH_RELATED
			? Purchasables::relatedVariantIds($elementId)
			: [];

		$matchedQuantity = 0;

		foreach ($lineItems as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable === null) {
				continue;
			}

			$matches = $this->matchType === self::MATCH_RELATED
				? in_array((int) $lineItem->purchasableId, $variantIds, true)
				: Purchasables::matches($purchasable, $this->elementType, [$elementId]);

			if ($matches) {
				$matchedQuantity += $lineItem->qty;
			}
		}

		return $matchedQuantity;
	}

	public function quantityMatches(int $matchedQuantity): bool
	{
		if ($this->getElementId() === null) {
			return false;
		}

		if ($this->quantity === null) {
			return $matchedQuantity > 0;
		}

		return match ($this->operator) {
			self::OPERATOR_EQ => $matchedQuantity === $this->quantity,
			self::OPERATOR_NE => $matchedQuantity !== $this->quantity,
			self::OPERATOR_LT => $matchedQuantity < $this->quantity,
			self::OPERATOR_LTE => $matchedQuantity <= $this->quantity,
			self::OPERATOR_GT => $matchedQuantity > $this->quantity,
			self::OPERATOR_GTE => $matchedQuantity >= $this->quantity,
			default => false,
		};
	}

	/**
	 * Variants this rule covers, so a Cart Action can discount whatever the condition matched.
	 *
	 * @return int[]
	 */
	public function variantIds(): array
	{
		$elementId = (int) $this->getElementId();
		if ($elementId === 0) {
			return [];
		}

		return $this->matchType === self::MATCH_RELATED
			? Purchasables::relatedVariantIds($elementId)
			: Purchasables::expandToVariantIds($this->elementType, [$elementId]);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return [
			...parent::getConfig(),
			'matchType' => $this->matchType,
			'elementType' => $this->elementType,
			'quantity' => $this->quantity,
		];
	}

	public function getHtml(): string
	{
		// Craft's own markup puts the operator before every other control, and here it follows the label.
		return $this->inputHtml();
	}

	/**
	 * @return array<int, string>
	 */
	protected function operators(): array
	{
		return [
			self::OPERATOR_GTE,
			self::OPERATOR_LTE,
			self::OPERATOR_GT,
			self::OPERATOR_LT,
			self::OPERATOR_EQ,
			self::OPERATOR_NE,
		];
	}

	/**
	 * The quantity follows the operator, so each label has to read the same at one as it does at many.
	 */
	protected function operatorLabel(string $operator): string
	{
		return match ($operator) {
			self::OPERATOR_EQ => Craft::t('advanced-discounts', 'conditions.lineItemMatch.operator.eq'),
			self::OPERATOR_NE => Craft::t('advanced-discounts', 'conditions.lineItemMatch.operator.ne'),
			self::OPERATOR_LT => Craft::t('advanced-discounts', 'conditions.lineItemMatch.operator.lt'),
			self::OPERATOR_LTE => Craft::t('advanced-discounts', 'conditions.lineItemMatch.operator.lte'),
			self::OPERATOR_GT => Craft::t('advanced-discounts', 'conditions.lineItemMatch.operator.gt'),
			self::OPERATOR_GTE => Craft::t('advanced-discounts', 'conditions.lineItemMatch.operator.gte'),
			default => parent::operatorLabel($operator),
		};
	}

	/**
	 * @return class-string<ElementInterface>
	 */
	protected function elementType(): string
	{
		$elementTypes = array_column($this->elementTypeOptions(), 'value');

		// The match type posts before the element type does, so a flipped rule arrives holding the other mode's type.
		if (! in_array($this->elementType, $elementTypes, true)) {
			return $this->matchType === self::MATCH_RELATED ? Entry::class : Variant::class;
		}

		return $this->elementType;
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['matchType', 'elementType', 'quantity'], 'safe'],
		]);
	}

	protected function inputHtml(): string
	{
		$quantityRow = Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('app', 'Operator'), 'operator') .
			Cp::selectHtml([
				'id' => 'operator',
				'name' => 'operator',
				'value' => $this->operator,
				'options' => array_map(fn (string $operator): array => [
					'value' => $operator,
					'label' => $this->operatorLabel($operator),
				], $this->operators()),
			]) .
			Html::hiddenLabel(Craft::t('advanced-discounts', 'conditions.lineItemMatch.quantity'), 'quantity') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'quantity',
				'name' => 'quantity',
				'value' => $this->quantity,
				'placeholder' => Craft::t('advanced-discounts', 'conditions.lineItemMatch.anyQty'),
				'autocomplete' => false,
				'class' => 'advanced-discount-qty-input',
			]),
			[
				'class' => ['flex', 'flex-start'],
			]
		);

		$elementRow = Html::tag(
			'div',
			Html::hiddenLabel(Craft::t('advanced-discounts', 'conditions.lineItemMatch.matchType'), 'matchType') .
			Cp::selectHtml([
				'id' => 'matchType',
				'name' => 'matchType',
				'options' => $this->matchTypeOptions(),
				'value' => $this->matchType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Html::hiddenLabel(Craft::t('advanced-discounts', 'conditions.lineItemMatch.elementType'), 'elementType') .
			Cp::selectHtml([
				'id' => 'elementType',
				'name' => 'elementType',
				'options' => $this->elementTypeOptions(),
				'value' => $this->elementType(),
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			parent::inputHtml(),
			[
				'class' => ['flex', 'flex-start'],
			]
		);

		return Html::hiddenLabel($this->getLabel(), 'operator') .
			Html::tag(
				'div',
				$quantityRow . $elementRow,
				[
					'class' => ['flex', 'flex-start', 'advanced-discount-line-item-match'],
				]
			);
	}

	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	private function matchTypeOptions(): array
	{
		return [
			[
				'value' => self::MATCH_PURCHASABLE,
				'label' => Craft::t('advanced-discounts', 'conditions.lineItemMatch.matchType.purchasable'),
			],
			[
				'value' => self::MATCH_RELATED,
				'label' => Craft::t('advanced-discounts', 'conditions.lineItemMatch.matchType.related'),
			],
		];
	}

	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	private function elementTypeOptions(): array
	{
		if ($this->matchType === self::MATCH_RELATED) {
			return [
				[
					'value' => Entry::class,
					'label' => Entry::displayName(),
				],
				[
					'value' => Category::class,
					'label' => Category::displayName(),
				],
			];
		}

		return Purchasables::typeOptions();
	}
}
