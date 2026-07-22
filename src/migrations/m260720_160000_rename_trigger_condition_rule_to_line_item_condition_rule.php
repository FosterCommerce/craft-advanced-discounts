<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCondition;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\records\Discount;

class m260720_160000_rename_trigger_condition_rule_to_line_item_condition_rule extends Migration
{
	private const OLD_RULE_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\TriggerConditionRule';

	private const OLD_NESTED_CONDITION_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\TriggerCondition';

	private const OLD_NESTED_KEY = 'triggerCondition';

	private const NEW_NESTED_KEY = 'lineItemCondition';

	public function safeUp(): bool
	{
		$this->_remapStoredRows(
			self::OLD_RULE_CLASS,
			LineItemConditionRule::class,
			self::OLD_NESTED_CONDITION_CLASS,
			LineItemCondition::class,
			self::OLD_NESTED_KEY,
			self::NEW_NESTED_KEY
		);

		return true;
	}

	private function _remapStoredRows(
		string $oldRuleClass,
		string $newRuleClass,
		string $oldNestedConditionClass,
		string $newNestedConditionClass,
		string $oldNestedKey,
		string $newNestedKey
	): void {
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

				$columns[$column] = $this->_remapRecursively(
					$decoded,
					$oldRuleClass,
					$newRuleClass,
					$oldNestedConditionClass,
					$newNestedConditionClass,
					$oldNestedKey,
					$newNestedKey
				);
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
	private function _remapRecursively(
		array $value,
		string $oldRuleClass,
		string $newRuleClass,
		string $oldNestedConditionClass,
		string $newNestedConditionClass,
		string $oldNestedKey,
		string $newNestedKey
	): array {
		if (($value['class'] ?? null) === $oldRuleClass) {
			$value['class'] = $newRuleClass;

			if ($oldNestedKey !== $newNestedKey && array_key_exists($oldNestedKey, $value)) {
				$value[$newNestedKey] = $value[$oldNestedKey];
				unset($value[$oldNestedKey]);
			}
		} elseif (($value['class'] ?? null) === $oldNestedConditionClass) {
			$value['class'] = $newNestedConditionClass;
		}

		foreach ($value as $key => $item) {
			if (is_array($item)) {
				$value[$key] = $this->_remapRecursively(
					$item,
					$oldRuleClass,
					$newRuleClass,
					$oldNestedConditionClass,
					$newNestedConditionClass,
					$oldNestedKey,
					$newNestedKey
				);
			}
		}

		return $value;
	}
}
