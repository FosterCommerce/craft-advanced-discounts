<?php

namespace fostercommerce\coupons\migrations;

use craft\db\Migration;
use fostercommerce\coupons\records\Coupon;

class m260625_000000_add_enabled_to_coupons extends Migration
{
	public function safeUp(): bool
	{
		$this->addColumn(Coupon::TABLE_NAME, 'enabled', $this->boolean()->notNull()->defaultValue(true)->after('code'));

		return true;
	}

	public function safeDown(): bool
	{
		$this->dropColumn(Coupon::TABLE_NAME, 'enabled');

		return true;
	}
}
