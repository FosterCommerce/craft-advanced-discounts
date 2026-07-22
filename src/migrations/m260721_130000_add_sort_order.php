<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use fostercommerce\advanceddiscounts\records\Discount;

class m260721_130000_add_sort_order extends Migration
{
	public function safeUp(): bool
	{
		$this->addColumn(Discount::TABLE_NAME, 'sortOrder', $this->smallInteger()->unsigned()->null()->after('stopProcessing'));

		$rows = (new Query())
			->select(['id'])
			->from(Discount::TABLE_NAME)
			->orderBy([
				'dateUpdated' => SORT_DESC,
			])
			->all();

		foreach ($rows as $index => $row) {
			$this->update(Discount::TABLE_NAME, [
				'sortOrder' => $index + 1,
			], [
				'id' => $row['id'],
			], updateTimestamp: false);
		}

		return true;
	}
}
