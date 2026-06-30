<?php

namespace fostercommerce\advancedDiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;

class OrderConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	public ?ElementConditionInterface $_orderCondition = null;

	public function __construct($config = [])
	{
		$config['orderCondition'] = $config['attributes']['orderCondition'] ?? [];
		parent::__construct($config);
	}

	public function getNestedCondition(): ElementConditionInterface
	{
		return $this->getOrderCondition();
	}

	public function getOrderCondition(): ElementConditionInterface
	{
		$condition = $this->_orderCondition ?? new OrderCondition();
		$condition->mainTag = 'div';
		$condition->name = 'orderCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setOrderCondition(ElementConditionInterface|array $condition): void
	{
		if (is_array($condition)) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = OrderCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_orderCondition = $condition;
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'Order');
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
			'orderCondition' => $this->_orderCondition?->getConfig() ?? [],
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		return $this->getOrderCondition()->matchElement($element);
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getOrderCondition()->getBuilderHtml());
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
