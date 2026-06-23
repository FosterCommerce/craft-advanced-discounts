<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\BaseElementSelectConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;

class RelatedToConditionRule extends BaseElementSelectConditionRule implements ElementConditionRuleInterface
{
	/**
	 * @phpstan-var class-string<ElementInterface>
	 */
	public string $elementType = Entry::class;

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Related To');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
		$elementId = $this->getElementId();
		if ($elementId !== null) {
			$query->andRelatedTo($elementId);
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'elementType' => $this->elementType,
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		$elementId = $this->getElementId();
		if (! $elementId) {
			return true;
		}

		return $element::find()
			->id($element->id ?: false)
			->site('*')
			->drafts($element->getIsDraft())
			->provisionalDrafts($element->isProvisionalDraft)
			->revisions($element->getIsRevision())
			->status(null)
			->relatedTo($elementId)
			->exists();
	}

	protected function elementType(): string
	{
		return $this->elementType;
	}

	protected function inputHtml(): string
	{
		$id = 'element-type';
		return Html::hiddenLabel($this->getLabel(), $id) .
			Html::tag(
				'div',
				Cp::selectHtml([
					'id' => $id,
					'name' => 'elementType',
					'options' => $this->_elementTypeOptions(),
					'value' => $this->elementType,
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
			[['elementType'], 'safe'],
		]);
	}

	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	private function _elementTypeOptions(): array
	{
		$options = [
			[
				'value' => Entry::class,
				'label' => Entry::displayName(),
			],
		];

		return $options;
	}
}
