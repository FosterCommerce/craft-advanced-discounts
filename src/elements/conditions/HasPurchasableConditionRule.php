<?php

namespace fostercommerce\coupons\elements\conditions;

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
	public string $purchasableType = Variant::class;

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
		return Order::find()
			->id($element->id)
			->hasPurchasables([(int) $this->getElementId()])
			->exists();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'purchasableType' => $this->purchasableType,
		]);
	}

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
		$rules[] = [['purchasableType'], 'safe'];

		return $rules;
	}

	protected function inputHtml(): string
	{
		$id = 'purchasable-type';
		return Html::hiddenLabel($this->getLabel(), $id) .
			Html::tag(
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
				[
					'class' => ['flex', 'flex-start'],
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
