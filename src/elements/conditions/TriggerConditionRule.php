<?php

namespace fostercommerce\advancedDiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;

class TriggerConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	public ?ElementConditionInterface $_triggerCondition = null;

	public function __construct($config = [])
	{
		$config['triggerCondition'] = isset($config['triggerCondition']) ? $config['triggerCondition'] : ($config['attributes']['triggerCondition'] ?? []);
		parent::__construct($config);
	}

	public function getNestedCondition(): ElementConditionInterface
	{
		return $this->getTriggerCondition();
	}

	public function getTriggerCondition(): ElementConditionInterface
	{
		$condition = $this->_triggerCondition ?? new TriggerCondition();
		$condition->mainTag = 'div';
		$condition->name = 'triggerCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setTriggerCondition(ElementConditionInterface|array $condition): void
	{
		if (is_array($condition)) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = TriggerCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_triggerCondition = $condition;
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'Trigger');
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
			'triggerCondition' => $this->_triggerCondition?->getConfig() ?? [],
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		return $this->getTriggerCondition()->matchElement($element);
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getTriggerCondition()->getBuilderHtml());
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
