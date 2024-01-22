<?php

namespace fostercommerce\coupons\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\coupons\elements\conditions\AndTriggerCondition;
use fostercommerce\coupons\elements\conditions\ApplyCondition;
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
    public string $name = '';

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
     * @var string The type of discount to apply for this coupon.
     */
    public string $discountType = CouponRecord::DISCOUNT_TYPE_NONE;

    /**
     * @var double The discount value
     */
    public ?double $discountAmount = null;

    /**
     * @var string How to apply the discount
     */
    public string $applyTo = CouponRecord::APPLY_TO_ORDER;

    /**
     * @var int The optional amount of items that a discount can be applied to.
     */
    public ?int $itemLimit = null;

    /**
     * @var ElementConditionInterface|null
     * @see getApplyCondition()
     * @see setApplyCondition()
     */
    public null|ElementConditionInterface $_applyCondition = null;

    /**
     * @var string The type of discount to apply to shipping for this coupon.
     */
    public string $shippingDiscountType = CouponRecord::DISCOUNT_TYPE_NONE;

    /**
     * @var string|bool The shipping handle to discount. If it's true, then discount applies to any handle.
     */
    public string|bool $applyShipping = false;

    /**
     * @var double The shipping discount value
     */
    public ?double $shippingDiscountAmount = null;

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
    public function setTriggerCondition(ElementConditionInterface|string|array $condition): void
    {
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
    public function getApplyCondition(): ElementConditionInterface
    {
        $condition = $this->_applyCondition ?? new ApplyCondition();
        $condition->mainTag = 'div';
        $condition->name = 'applyCondition';

        return $condition;
    }

    /**
     * @param ElementConditionInterface|string|array $condition
     * @return void
     */
    public function setApplyCondition(ElementConditionInterface|string|array $condition): void
    {
        if (is_string($condition)) {
            $condition = Json::decodeIfJson($condition);
        }

        if (!$condition instanceof ElementConditionInterface) {
            $condition['class'] = ApplyCondition::class;
            /** @var ApplyCondition $condition */
            $condition = Craft::$app->getConditions()->createCondition($condition);
        }
        $condition->forProjectConfig = false;

        $this->_applyCondition = $condition;
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
}
