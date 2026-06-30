<?php

namespace fostercommerce\advancedDiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseElementSelectConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as CommercePlugin;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;

class SpecificPurchasableConditionRule extends BaseElementSelectConditionRule implements ElementConditionRuleInterface
{
	/**
	 * @var class-string<ElementInterface>
	 */
	public string $purchasableType = Variant::class;

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'Specific Purchasable');
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
		$elementId = $this->getElementId();
		return $elementId !== null && (int) $element->id === (int) $elementId;
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

	/**
	 * @return class-string<ElementInterface>
	 */
	protected function elementType(): string
	{
		return $this->purchasableType;
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

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['purchasableType'], 'safe'],
		]);
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
