<?php

namespace fostercommerce\advanceddiscounts\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\elements\conditions\CartActionCondition;
use fostercommerce\advanceddiscounts\elements\conditions\CartCondition;
use fostercommerce\advanceddiscounts\elements\conditions\MessageCondition;

class Discount extends Model
{
	/**
	 * @var int|null ID
	 */
	public ?int $id = null;

	/**
	 * @var string Name of the discount
	 */
	public string $name = '';

	/**
	 * @var string|null The discount's unique code, if any
	 */
	public ?string $code = null;

	/**
	 * @var bool Whether the discount is enabled
	 */
	public bool $enabled = true;

	/**
	 * @see getCartCondition()
	 * @see setCartCondition()
	 */
	public null|ElementConditionInterface $_cartCondition = null;

	/**
	 * @see getCartActionCondition()
	 * @see setCartActionCondition()
	 */
	public null|ElementConditionInterface $_cartActionCondition = null;

	/**
	 * @see getMessageCondition()
	 * @see setMessageCondition()
	 */
	public null|ElementConditionInterface $_messageCondition = null;

	public ?\DateTime $dateCreated = null;

	public ?\DateTime $dateUpdated = null;

	public function getCartCondition(): ElementConditionInterface
	{
		$condition = $this->_cartCondition ?? new CartCondition();
		$condition->mainTag = 'div';
		$condition->name = 'cartCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setCartCondition(ElementConditionInterface|string|array|null $condition): void
	{
		if ($condition === null) {
			$condition = [];
		}

		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = CartCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_cartCondition = $condition;
	}

	public function getCartActionCondition(): ElementConditionInterface
	{
		$condition = $this->_cartActionCondition ?? new CartActionCondition();
		$condition->mainTag = 'div';
		$condition->name = 'cartActionCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setCartActionCondition(ElementConditionInterface|string|array|null $condition): void
	{
		if ($condition === null) {
			$condition = [];
		}
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = CartActionCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_cartActionCondition = $condition;
	}

	public function getMessageCondition(): ElementConditionInterface
	{
		$condition = $this->_messageCondition ?? new MessageCondition();
		$condition->mainTag = 'div';
		$condition->name = 'messageCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setMessageCondition(ElementConditionInterface|string|array|null $condition): void
	{
		if ($condition === null) {
			$condition = [];
		}
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = MessageCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_messageCondition = $condition;
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['name'], 'required'],
			[['name', 'code'],
				'string',
				'max' => 255],
			[
				'cartCondition',
				function (string $attribute): void {
					if (empty($this->getCartCondition()->getConditionRules())) {
						$this->addError($attribute, Craft::t('advanced-discounts', 'At least one condition is required.'));
					}
				},
			],
			[
				'cartActionCondition',
				function (string $attribute): void {
					if (empty($this->getCartActionCondition()->getConditionRules())) {
						$this->addError($attribute, Craft::t('advanced-discounts', 'At least one cart action rule is required.'));
					}
				},
			],
		]);
	}
}
