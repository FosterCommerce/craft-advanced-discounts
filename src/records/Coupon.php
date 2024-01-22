<?php

namespace fostercommerce\coupons\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Coupon record
 */
class Coupon extends ActiveRecord
{
    public const DISCOUNT_TYPE_NONE = 'discountTypeNone';
    public const DISCOUNT_TYPE_PERCENTAGE = 'discountTypePercentage';
    public const DISCOUNT_TYPE_FLAT_AMOUNT = 'discountTypeFlatAmount';

    public const APPLY_TO_ORDER = 'applyToOrder';
    public const APPLY_TO_TRIGGER_ITEMS = 'applyToTriggerItems';
    public const APPLY_TO_CONDITIONAL_ITEMS = 'applyToConditionalItems';

    public static function tableName()
    {
        return '{{%coupons_coupons}}';
    }
}
