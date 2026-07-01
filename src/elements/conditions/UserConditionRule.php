<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\users\UserCondition;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;

class UserConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	public ?ElementConditionInterface $_userCondition = null;

	public function __construct($config = [])
	{
		$config['userCondition'] = $config['attributes']['userCondition'] ?? [];
		parent::__construct($config);
	}

	public function getNestedCondition(): ElementConditionInterface
	{
		return $this->getUserCondition();
	}

	public function getUserCondition(): ElementConditionInterface
	{
		$condition = $this->_userCondition ?? new UserCondition();
		$condition->mainTag = 'div';
		$condition->name = 'userCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setUserCondition(ElementConditionInterface|array $condition): void
	{
		if (is_array($condition)) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = UserCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_userCondition = $condition;
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'User');
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
		$user = Craft::$app->getUser()->getIdentity();
		if ($user === null) {
			return false;
		}
		return $this->getUserCondition()->matchElement($user);
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getUserCondition()->getBuilderHtml());
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
