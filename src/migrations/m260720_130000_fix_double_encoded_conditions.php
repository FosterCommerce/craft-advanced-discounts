<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\records\Discount;

class m260720_130000_fix_double_encoded_conditions extends Migration
{
	public function safeUp(): bool
	{
		$rows = (new Query())
			->select(['id', 'actionCondition', 'messageCondition'])
			->from([Discount::TABLE_NAME])
			->all();

		foreach ($rows as $row) {
			$columns = [];

			foreach (['actionCondition', 'messageCondition'] as $column) {
				if (! is_string($row[$column])) {
					continue;
				}

				$decoded = Json::decodeIfJson($row[$column]);
				if (! is_string($decoded)) {
					continue;
				}

				$decoded = Json::decodeIfJson($decoded);
				if (is_array($decoded)) {
					$columns[$column] = $decoded;
				}
			}

			if ($columns === []) {
				continue;
			}

			$this->update(Discount::TABLE_NAME, $columns, [
				'id' => $row['id'],
			], updateTimestamp: false);
		}

		return true;
	}

	public function safeDown(): bool
	{
		return true;
	}
}
