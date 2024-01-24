<?php

namespace fostercommerce\coupons\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\coupons\elements\conditions\ActionCondition;
use fostercommerce\coupons\elements\conditions\AndTriggerCondition;
use fostercommerce\coupons\elements\conditions\OrderCondition;
use fostercommerce\coupons\elements\conditions\RelatedToCondition;
use fostercommerce\coupons\elements\conditions\TriggerCondition;
use fostercommerce\coupons\elements\conditions\TriggerConditionRule;
use fostercommerce\coupons\records\Coupon as CouponRecord;

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
     * @var ElementConditionInterface|null
     * @see getTriggerCondition()
     * @see setTriggerCondition()
     */
    public null|ElementConditionInterface $_triggerCondition = null;

    /**
     * @var ElementConditionInterface|null
     * @see getActionCondition()
     * @see setActionCondition()
     */
    public null|ElementConditionInterface $_actionCondition = null;

    public ?\DateTime $dateCreated = null;

    public ?\DateTime $dateUpdated = null;

    /**
     * @return ElementConditionInterface
     */
    public function getTriggerCondition(): ElementConditionInterface
    {
        $condition = $this->_triggerCondition ?? new AndTriggerCondition();
        $condition->mainTag = 'div';
        $condition->name = 'triggerCondition';

        return $condition;
    }

    /**
     * @param ElementConditionInterface|string|array $condition
     * @return void
     */
    public function setTriggerCondition(ElementConditionInterface|string|array|null $condition): void
    {
        if ($condition === null) {
            $condition = [];
        }

        if (is_string($condition)) {
            $condition = Json::decodeIfJson($condition);
        }

        if (!$condition instanceof ElementConditionInterface) {
            $condition['class'] = AndTriggerCondition::class;
            /** @var AndTriggerCondition $condition */
            $condition = Craft::$app->getConditions()->createCondition($condition);
        }
        $condition->forProjectConfig = false;

        $this->_triggerCondition = $condition;
    }

    /**
     * @return ElementConditionInterface
     */
    public function getActionCondition(): ElementConditionInterface
    {
        $condition = $this->_actionCondition ?? new ActionCondition();
        $condition->mainTag = 'div';
        $condition->name = 'actionCondition';

        return $condition;
    }

    /**
     * @param ElementConditionInterface|string|array $condition
     * @return void
     */
    public function setActionCondition(ElementConditionInterface|string|array|null $condition): void
    {
        if ($condition === null) {
            $condition = [];
        }
        if (is_string($condition)) {
            $condition = Json::decodeIfJson($condition);
        }

        if (!$condition instanceof ElementConditionInterface) {
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
