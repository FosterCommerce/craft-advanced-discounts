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

class LineItemConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	private ?ElementConditionInterface $_lineItemCondition = null;

	public function __construct($config = [])
	{
		$config['lineItemCondition'] = NestedConditionConfig::extract($config, 'lineItemCondition');
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
		if ($condition === []) {
			return;
		}

		$this->_lineItemCondition = NestedConditionConfig::build($condition, LineItemCondition::class);
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'rules.lineItems');
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
}
