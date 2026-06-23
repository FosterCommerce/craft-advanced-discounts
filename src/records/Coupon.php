<?php

namespace fostercommerce\coupons\records;

use Craft;
use craft\db\ActiveRecord;
use fostercommerce\coupons\elements\conditions\ActionCondition;
use fostercommerce\coupons\elements\conditions\AndTriggerCondition;

/**
 * Coupon record
 *
 * @property int $id
 * @property ?string $title
 * @property string $code
 * @property ?array $triggerCondition
 * @property ?array $actionCondition
 */
class Coupon extends ActiveRecord
{
    final public const TABLE_NAME = '{{%coupons_coupons}}';
    public static function tableName()
    {
        return self::TABLE_NAME;
    }
}
