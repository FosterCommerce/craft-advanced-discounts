<?php

namespace fostercommerce\coupons\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\coupons\elements\conditions\ApplyCondition;
use fostercommerce\coupons\elements\conditions\OrderCondition;
use fostercommerce\coupons\elements\conditions\PurchasablesCondition;
use fostercommerce\coupons\elements\conditions\RelatedToCondition;
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
     * @var string Whether all related items, some related items or none of the related items trigger a coupon
     */
    public string $relatedTo = CouponRecord::RELATED_TO_ANY;

    /**
     * @var ElementConditionInterface|null
     * @see getRelatedToCondition()
     * @see setRelatedToCondition()
     */
    public null|ElementConditionInterface $_relatedToCondition = null;

    /**
     * @var string Whether all purchasables, some purchasables or none of the purchasables trigger a coupon
     */
    public string $purchasables = CouponRecord::PURCHASABLES_ANY;

    /**
     * @var ElementConditionInterface|null
     * @see getPurchasablesCondition()
     * @see setPurchasablesCondition()
     */
    public null|ElementConditionInterface $_purchasablesCondition = null;

    /**
     * @var ElementConditionInterface|null
     * @see getOrderCondition()
     * @see setOrderCondition()
     */
    public null|ElementConditionInterface $_orderCondition = null;

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
    public function getRelatedToCondition(): ElementConditionInterface
    {
        $condition = $this->_relatedToCondition ?? new RelatedToCondition();
        $condition->mainTag = 'div';
        $condition->name = 'relatedToCondition';

        return $condition;
    }

    /**
     * @param ElementConditionInterface|string|array $condition
     * @return void
     */
    public function setRelatedToCondition(ElementConditionInterface|string|array $condition): void
    {
        if (is_string($condition)) {
            $condition = Json::decodeIfJson($condition);
        }

        if (!$condition instanceof ElementConditionInterface) {
            $condition['class'] = RelatedToCondition::class;
            /** @var RelatedToCondition $condition */
            $condition = Craft::$app->getConditions()->createCondition($condition);
        }
        $condition->forProjectConfig = false;

        $this->_relatedToCondition = $condition;
    }

    /**
     * @return ElementConditionInterface
     */
    public function getPurchasablesCondition(): ElementConditionInterface
    {
        $condition = $this->_relatedToCondition ?? new PurchasablesCondition();
        $condition->mainTag = 'div';
        $condition->name = 'purchasablesCondition';

        return $condition;
    }

    /**
     * @param ElementConditionInterface|string|array $condition
     * @return void
     */
    public function setPurchasablesCondition(ElementConditionInterface|string|array $condition): void
    {
        if (is_string($condition)) {
            $condition = Json::decodeIfJson($condition);
        }

        if (!$condition instanceof ElementConditionInterface) {
            $condition['class'] = PurchasablesCondition::class;
            /** @var PurchasablesCondition $condition */
            $condition = Craft::$app->getConditions()->createCondition($condition);
        }
        $condition->forProjectConfig = false;

        $this->_purchasablesCondition = $condition;
    }

    /**
     * @return ElementConditionInterface
     */
    public function getOrderCondition(): ElementConditionInterface
    {
        $condition = $this->_orderCondition ?? new OrderCondition();
        $condition->mainTag = 'div';
        $condition->name = 'orderCondition';

        return $condition;
    }

    /**
     * @param ElementConditionInterface|string|array $condition
     * @return void
     */
    public function setOrderCondition(ElementConditionInterface|string|array $condition): void
    {
        if (is_string($condition)) {
            $condition = Json::decodeIfJson($condition);
        }

        if (!$condition instanceof ElementConditionInterface) {
            $condition['class'] = OrderCondition::class;
            /** @var OrderCondition $condition */
            $condition = Craft::$app->getConditions()->createCondition($condition);
        }
        $condition->forProjectConfig = false;

        $this->_orderCondition = $condition;
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
