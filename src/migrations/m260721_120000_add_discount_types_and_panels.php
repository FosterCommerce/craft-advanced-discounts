<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\records\Discount;

class m260721_120000_add_discount_types_and_panels extends Migration
{
	public function safeUp(): bool
	{
		$table = Discount::TABLE_NAME;

		$this->addColumn($table, 'type', $this->string()->notNull()->defaultValue('advanced')->after('enabled'));
		$this->addColumn($table, 'settings', $this->json()->null()->after('type'));

		$rows = (new Query())
			->select(['id', 'cartCondition', 'cartActionCondition', 'messageCondition'])
			->from($table)
			->all();

		foreach ($rows as $row) {
			$settings = [
				'panels' => [
					[
						'cartCondition' => Json::decodeIfJson($row['cartCondition'] ?? '') ?: [],
						'cartActionCondition' => Json::decodeIfJson($row['cartActionCondition'] ?? '') ?: [],
						'messageCondition' => Json::decodeIfJson($row['messageCondition'] ?? '') ?: [],
					],
				],
			];

			$this->update($table, [
				'settings' => $settings,
			], [
				'id' => $row['id'],
			], updateTimestamp: false);
		}

		return true;
	}
}
