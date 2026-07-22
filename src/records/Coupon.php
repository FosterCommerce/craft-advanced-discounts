<?php

namespace fostercommerce\advanceddiscounts\records;

use craft\db\ActiveRecord;

/**
 * Coupon record
 *
 * @property int $id
 * @property int $discountId
 * @property string $code
 * @property int $uses
 * @property ?int $maxUses
 */
class Coupon extends ActiveRecord
{
	final public const TABLE_NAME = '{{%advanced_discounts_coupons}}';

	public static function tableName()
	{
		return self::TABLE_NAME;
	}
}
