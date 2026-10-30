<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Order;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\conditions\users\UserCondition;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Html;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;

class UserConditionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	private ?ElementConditionInterface $_userCondition = null;

	public function __construct($config = [])
	{
		$config['userCondition'] = NestedConditionConfig::extract($config, 'userCondition');
		parent::__construct($config);
	}

	public function getUserCondition(): ElementConditionInterface
	{
		$condition = $this->_userCondition ?? new UserCondition();
		$condition->elementType = User::class;
		$condition->mainTag = 'div';
		$condition->name = 'userCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setUserCondition(ElementConditionInterface|array $condition): void
	{
		if ($condition === []) {
			return;
		}

		$this->_userCondition = NestedConditionConfig::build($condition, UserCondition::class);
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'rules.user');
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
			'userCondition' => $this->_userCondition?->getConfig() ?? [],
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		/** @var Order $element */
		// Use the order's customer, since the session user is not the shopper in the CP, a queue job or the console
		$customer = $element->getCustomer();

		return $customer instanceof User && $this->getUserCondition()->matchElement($customer);
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getUserCondition()->getBuilderHtml());
	}
}
