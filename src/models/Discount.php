<?php

namespace fostercommerce\advancedDiscounts\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\advancedDiscounts\elements\conditions\ActionCondition;
use fostercommerce\advancedDiscounts\elements\conditions\AndTriggerCondition;

/**
 * Coupon model
 */
class Discount extends Model
{
	/**
	 * @var int|null ID
	 */
	public ?int $id = null;

	/**
	 * @var string Name of the coupon
	 */
	public string $name = '';

	/**
	 * @var string The coupons unique code
	 */
	public string $code = '';

	/**
	 * @var bool Whether the coupon is enabled
	 */
	public bool $enabled = true;

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

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
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
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
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

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
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
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_actionCondition = $condition;
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['name', 'code'], 'required'],
			[['name', 'code'],
				'string',
				'max' => 255],
			[
				'triggerCondition',
				function (string $attribute): void {
					if (empty($this->getTriggerCondition()->getConditionRules())) {
						$this->addError($attribute, Craft::t('advanced-discounts', 'At least one condition is required.'));
					}
				},
			],
			[
				'actionCondition',
				function (string $attribute): void {
					if (empty($this->getActionCondition()->getConditionRules())) {
						$this->addError($attribute, Craft::t('advanced-discounts', 'At least one action rule is required.'));
					}
				},
			],
		]);
	}
}
