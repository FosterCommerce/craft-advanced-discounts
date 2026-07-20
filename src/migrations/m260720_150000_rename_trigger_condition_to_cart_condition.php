<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\records\Discount;

class m260720_150000_rename_trigger_condition_to_cart_condition extends Migration
{
	private const OLD_AND_CONDITION_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\AndTriggerCondition';

	private const NEW_AND_CONDITION_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\AndCondition';

	public function safeUp(): bool
	{
		$this->renameColumn(Discount::TABLE_NAME, 'triggerCondition', 'cartCondition');

		$this->_remapStoredClasses(self::OLD_AND_CONDITION_CLASS, self::NEW_AND_CONDITION_CLASS);

		return true;
	}

	public function safeDown(): bool
	{
		if ($this->db->tableExists(Discount::TABLE_NAME)) {
			$this->_remapStoredClasses(self::NEW_AND_CONDITION_CLASS, self::OLD_AND_CONDITION_CLASS);

			$this->renameColumn(Discount::TABLE_NAME, 'cartCondition', 'triggerCondition');
		}

		return true;
	}

	/**
	 * AndCondition (formerly AndTriggerCondition, later renamed to CartCondition) is
	 * used both as the discount's top-level cartCondition and, nested, as each
	 * message rule's messageCondition.
	 */
	private function _remapStoredClasses(string $oldClass, string $newClass): void
	{
		$rows = (new Query())
			->select(['id', 'cartCondition', 'messageCondition'])
			->from([Discount::TABLE_NAME])
			->all();

		foreach ($rows as $row) {
			$columns = [];

			foreach (['cartCondition', 'messageCondition'] as $column) {
				$decoded = Json::decodeIfJson($row[$column]);
				if (! is_array($decoded)) {
					continue;
				}

				$columns[$column] = $this->_remapClassRecursively($decoded, $oldClass, $newClass);
			}

			if ($columns === []) {
				continue;
			}

			$this->update(Discount::TABLE_NAME, $columns, [
				'id' => $row['id'],
			], updateTimestamp: false);
		}
	}

	/**
	 * @param array<string, mixed> $value
	 * @return array<string, mixed>
	 */
	private function _remapClassRecursively(array $value, string $oldClass, string $newClass): array
	{
		if (($value['class'] ?? null) === $oldClass) {
			$value['class'] = $newClass;
		}

		foreach ($value as $key => $item) {
			if (is_array($item)) {
				$value[$key] = $this->_remapClassRecursively($item, $oldClass, $newClass);
			}
		}

		return $value;
	}
}
