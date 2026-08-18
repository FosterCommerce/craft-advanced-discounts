<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;

class OrderConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	private ?ElementConditionInterface $_orderCondition = null;

	public function __construct($config = [])
	{
		$config['orderCondition'] = NestedConditionConfig::extract($config, 'orderCondition');
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
		if ($condition === []) {
			return;
		}

		$this->_orderCondition = NestedConditionConfig::build($condition, OrderCondition::class);
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'conditions.order');
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
}
