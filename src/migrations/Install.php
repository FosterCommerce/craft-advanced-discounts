<?php

namespace fostercommerce\coupons\migrations;

use craft\db\Migration;
use fostercommerce\coupons\records\Coupon;

class Install extends Migration
{
	public function safeUp(): bool
	{
		$this->createTable(Coupon::TABLE_NAME, [
			'id' => $this->primaryKey(),
			'name' => $this->string()->notNull(),
			'code' => $this->string()->null(),
			'triggerCondition' => $this->json()->null(),
			'actionCondition' => $this->json()->null(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
		]);
		$this->createIndex(null, Coupon::TABLE_NAME, ['dateUpdated'], false);

		return true;
	}

	public function safeDown(): bool
	{
		if ($this->db->tableExists(Coupon::TABLE_NAME)) {
			$this->dropIndexIfExists(Coupon::TABLE_NAME, ['dateUpdated'], false);
			$this->dropTable(Coupon::TABLE_NAME);
		}

		return true;
	}
}
