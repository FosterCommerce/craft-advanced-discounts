<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;

class LineItemConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	public ?ElementConditionInterface $_lineItemCondition = null;

	public function __construct($config = [])
	{
		$config['lineItemCondition'] = isset($config['lineItemCondition']) ? $config['lineItemCondition'] : ($config['attributes']['lineItemCondition'] ?? []);
		parent::__construct($config);
	}

	public function getNestedCondition(): ElementConditionInterface
	{
		return $this->getLineItemCondition();
	}

	public function getLineItemCondition(): ElementConditionInterface
	{
		$condition = $this->_lineItemCondition ?? new LineItemCondition();
		$condition->mainTag = 'div';
		$condition->name = 'lineItemCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setLineItemCondition(ElementConditionInterface|array $condition): void
	{
		if (is_array($condition)) {
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
		// TODO
		/*        $elementId = $this->getElementId();
				if ($elementId !== null) {
					$query->andRelatedTo($elementId);
				}*/
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'lineItemCondition' => $this->_lineItemCondition?->getConfig() ?? [],
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		return $this->getLineItemCondition()->matchElement($element);
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getLineItemCondition()->getBuilderHtml());
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
		]);
	}
}
