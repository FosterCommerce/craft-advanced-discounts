<?php

namespace fostercommerce\advanceddiscounts\records;

use craft\db\ActiveRecord;

/**
 * Discount record
 *
 * @property int $id
 * @property ?string $name
 * @property ?string $code
 * @property bool $enabled
 * @property bool $stopProcessing
 * @property ?int $sortOrder
 * @property string $type
 * @property ?array $settings
 * @property ?array $cartCondition
 * @property ?array $cartActionCondition
 * @property ?array $messageCondition
 */
class Discount extends ActiveRecord
{
	final public const TABLE_NAME = '{{%advanced_discounts}}';

	public static function tableName()
	{
		return self::TABLE_NAME;
	}
}
