<?php

namespace fostercommerce\coupons\records;

use craft\db\ActiveRecord;

/**
 * Coupon record
 *
 * @property int $id
 * @property ?string $name
 * @property string $code
 * @property bool $enabled
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
