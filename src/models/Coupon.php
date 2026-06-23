<?php

namespace fostercommerce\coupons\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\coupons\elements\conditions\ActionCondition;
use fostercommerce\coupons\elements\conditions\AndTriggerCondition;

/**
 * Coupon model
 */
class Coupon extends Model
{
	/**
	 * @var int|null ID
	 */
	public ?int $id = null;

	/**
	 * @var string Name of the coupon
	 */
	public string $title = '';

	/**
	 * @var string The coupons unique code
	 */
	public string $code = '';

	/**
	 * @see getTriggerCondition()
	 * @see setTriggerCondition()
	 */
	public null|ElementConditionInterface $_triggerCondition = null;

	/**
	 * @see getActionCondition()
	 * @see setActionCondition()
	 */
	public null|ElementConditionInterface $_actionCondition = null;

	public ?\DateTime $dateCreated = null;

	public ?\DateTime $dateUpdated = null;

	public function getTriggerCondition(): ElementConditionInterface
	{
		$condition = $this->_triggerCondition ?? new AndTriggerCondition();
		$condition->mainTag = 'div';
		$condition->name = 'triggerCondition';

		return $condition;
	}

	public function setTriggerCondition(ElementConditionInterface|string|array|null $condition): void
	{
		if ($condition === null) {
			$condition = [];
		}

		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = AndTriggerCondition::class;
			/** @var AndTriggerCondition $condition */
			$condition = Craft::$app->getConditions()->createCondition($condition);
		}
		$condition->forProjectConfig = false;

		$this->_triggerCondition = $condition;
	}

	public function getActionCondition(): ElementConditionInterface
	{
		$condition = $this->_actionCondition ?? new ActionCondition();
		$condition->mainTag = 'div';
		$condition->name = 'actionCondition';

		return $condition;
	}

	public function setActionCondition(ElementConditionInterface|string|array|null $condition): void
	{
		if ($condition === null) {
			$condition = [];
		}
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = ActionCondition::class;
			/** @var ActionCondition $condition */
			$condition = Craft::$app->getConditions()->createCondition($condition);
		}
		$condition->forProjectConfig = false;

		$this->_actionCondition = $condition;
	}

	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			// ...
		]);
	}
}
