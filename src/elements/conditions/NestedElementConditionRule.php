<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Variant;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;

/**
 * A Global Product Conditions row that matches a line item's variant against its own product or variant condition.
 */
abstract class NestedElementConditionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	private ?ElementConditionInterface $_elementCondition = null;

	public function __construct($config = [])
	{
		$config['elementCondition'] = NestedConditionConfig::extract($config, 'elementCondition');
		parent::__construct($config);
	}

	public function getElementCondition(): ElementConditionInterface
	{
		$conditionClass = $this->conditionClass();
		$condition = $this->_elementCondition ?? new $conditionClass();
		$condition->mainTag = 'div';
		$condition->name = 'elementCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setElementCondition(ElementConditionInterface|array $condition): void
	{
		if ($condition === []) {
			return;
		}

		$this->_elementCondition = NestedConditionConfig::build($condition, $this->conditionClass());
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'elementCondition' => $this->_elementCondition?->getConfig() ?? [],
		]);
	}

	/**
	 * @param Variant $element
	 */
	public function matchElement(ElementInterface $element): bool
	{
		$elementToMatch = $this->elementToMatch($element);

		return $elementToMatch instanceof ElementInterface && $this->getElementCondition()->matchElement($elementToMatch);
	}

	/**
	 * @return class-string<ElementConditionInterface>
	 */
	abstract protected function conditionClass(): string;

	abstract protected function elementToMatch(Variant $variant): ?ElementInterface;

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getElementCondition()->getBuilderHtml());
	}
}
