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

    public static function tableName()
    {
        return '{{%coupons_coupons}}';
    }
}
