<?php

namespace fostercommerce\advancedDiscounts\records;

use craft\db\ActiveRecord;

/**
 * Discount record
 *
 * @property int $id
 * @property ?string $name
 * @property string $code
 * @property bool $enabled
 * @property ?array $triggerCondition
 * @property ?array $actionCondition
 */
class Discount extends ActiveRecord
{
	final public const TABLE_NAME = '{{%advanced_discounts}}';

	public static function tableName()
	{
		return self::TABLE_NAME;
	}
}
