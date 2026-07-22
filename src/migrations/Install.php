<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use fostercommerce\advanceddiscounts\records\Coupon;
use fostercommerce\advanceddiscounts\records\Discount;

class Install extends Migration
{
	public function safeUp(): bool
	{
		$this->createTable(Discount::TABLE_NAME, [
			'id' => $this->primaryKey(),
			'name' => $this->string()->notNull(),
			'requireCouponCode' => $this->boolean()->notNull()->defaultValue(false),
			'enabled' => $this->boolean()->notNull()->defaultValue(true),
			'stopProcessing' => $this->boolean()->notNull()->defaultValue(false),
			'uses' => $this->integer()->notNull()->defaultValue(0),
			'sortOrder' => $this->smallInteger()->unsigned()->null(),
			'type' => $this->string()->notNull()->defaultValue('advanced'),
			'settings' => $this->json()->null(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
		]);
		$this->createIndex(null, Discount::TABLE_NAME, ['dateUpdated'], false);

		$this->createTable(Coupon::TABLE_NAME, [
			'id' => $this->primaryKey(),
			'discountId' => $this->integer()->notNull(),
			'code' => $this->string()->notNull(),
			'uses' => $this->integer()->notNull()->defaultValue(0),
			'maxUses' => $this->integer()->null(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid' => $this->uid(),
		]);
		$this->createIndex(null, Coupon::TABLE_NAME, ['code'], true);
		$this->addForeignKey(null, Coupon::TABLE_NAME, ['discountId'], Discount::TABLE_NAME, ['id'], 'CASCADE');

		return true;
	}

	public function safeDown(): bool
	{
		if ($this->db->tableExists(Coupon::TABLE_NAME)) {
			$this->dropTable(Coupon::TABLE_NAME);
		}

		if ($this->db->tableExists(Discount::TABLE_NAME)) {
			$this->dropIndexIfExists(Discount::TABLE_NAME, ['dateUpdated'], false);
			$this->dropTable(Discount::TABLE_NAME);
		}

		return true;
	}
}
