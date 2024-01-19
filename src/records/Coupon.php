<?php

namespace fostercommerce\coupons\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Coupon record
 */
class Coupon extends ActiveRecord
{
    public const RELATED_TO_ANY = 'relatedToAny';
    public const RELATED_TO_ALL = 'relatedToAll';
    public const RELATED_TO_NONE = 'relatedToNone';

    public const PURCHASABLES_ANY = 'purchasableAny';
    public const PURCHASABLES_ALL = 'purchasableAll';
    public const PURCHASABLES_NONE = 'purchasableNone';

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
