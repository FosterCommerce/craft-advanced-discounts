<?php

namespace fostercommerce\advancedDiscounts\migrations;

use craft\db\Migration;
use fostercommerce\advancedDiscounts\records\Discount;

class Install extends Migration
{
	public function safeUp(): bool
	{
		$this->createTable(Discount::TABLE_NAME, [
			'id' => $this->primaryKey(),
			'name' => $this->string()->notNull(),
			'code' => $this->string()->null(),
			'enabled' => $this->boolean()->notNull()->defaultValue(true),
			'triggerCondition' => $this->json()->null(),
			'actionCondition' => $this->json()->null(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
		]);
		$this->createIndex(null, Discount::TABLE_NAME, ['dateUpdated'], false);

		return true;
	}

	public function safeDown(): bool
	{
		if ($this->db->tableExists(Discount::TABLE_NAME)) {
			$this->dropIndexIfExists(Discount::TABLE_NAME, ['dateUpdated'], false);
			$this->dropTable(Discount::TABLE_NAME);
		}

		return true;
	}
}
