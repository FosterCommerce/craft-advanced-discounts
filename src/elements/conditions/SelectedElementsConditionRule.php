<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\base\conditions\BaseElementSelectConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementCondition;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;

class SelectedElementsConditionRule extends BaseElementSelectConditionRule implements ElementConditionRuleInterface
{
	public function getLabel(): string
	{
		$elementType = $this->elementType();

		return $elementType::displayName();
	}

	public function getExclusiveQueryParams(): array
	{
		return ['id'];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
		$query->id($this->getElementIds());
	}

	public function matchElement(ElementInterface $element): bool
	{
		return $this->matchValue($element->id);
	}

	/**
	 * @return class-string<ElementInterface>
	 */
	protected function elementType(): string
	{
		/** @var ElementCondition $condition */
		$condition = $this->getCondition();
		/** @var class-string<ElementInterface> $elementType */
		$elementType = $condition->elementType;

		return $elementType;
	}

	protected function allowMultiple(): bool
	{
		return true;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function elementSelectConfig(): array
	{
		return array_merge(parent::elementSelectConfig(), [
			'showSiteMenu' => true,
		]);
	}
}
