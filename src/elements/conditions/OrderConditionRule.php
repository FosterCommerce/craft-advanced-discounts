<?php

namespace fostercommerce\coupons\elements\conditions;

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
		$config['orderCondition'] = $config['attributes']['condition'] ?? [];

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

		return $condition;
	}

	public function setOrderCondition(ElementConditionInterface|array $condition): void
	{
		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = OrderCondition::class;
			/** @var OrderCondition $condition */
			$condition = Craft::$app->getConditions()->createCondition($condition);
		}
		$condition->forProjectConfig = false;

		$this->_orderCondition = $condition;
	}

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Order');
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

	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		// todo
		return $element::find()
			->id($element->id ?: false)
			->site('*')
			->drafts($element->getIsDraft())
			->provisionalDrafts($element->isProvisionalDraft)
			->revisions($element->getIsRevision())
			->status(null)
			->exists();
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getOrderCondition()->getBuilderHtml());
	}

	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
		]);
	}
}
