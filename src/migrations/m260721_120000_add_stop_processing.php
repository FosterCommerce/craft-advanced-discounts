<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use fostercommerce\advanceddiscounts\records\Discount;

class m260721_120000_add_stop_processing extends Migration
{
	public function safeUp(): bool
	{
		$this->addColumn(Discount::TABLE_NAME, 'stopProcessing', $this->boolean()->notNull()->defaultValue(false)->after('enabled'));

		return true;
	}

	public function safeDown(): bool
	{
		if ($this->db->tableExists(Discount::TABLE_NAME)) {
			$this->dropColumn(Discount::TABLE_NAME, 'stopProcessing');
		}

		return true;
	}
}
