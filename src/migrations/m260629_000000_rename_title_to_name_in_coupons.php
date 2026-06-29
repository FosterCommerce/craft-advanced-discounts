<?php

namespace fostercommerce\coupons\migrations;

use craft\db\Migration;
use fostercommerce\coupons\records\Coupon;

class m260629_000000_rename_title_to_name_in_coupons extends Migration
{
	public function safeUp(): bool
	{
		$this->renameColumn(Coupon::TABLE_NAME, 'title', 'name');

		return true;
	}

	public function safeDown(): bool
	{
		$this->renameColumn(Coupon::TABLE_NAME, 'name', 'title');

		return true;
	}
}
