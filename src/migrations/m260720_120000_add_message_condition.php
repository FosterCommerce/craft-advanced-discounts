<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageCondition;
use fostercommerce\advanceddiscounts\records\Discount;

class m260720_120000_add_message_condition extends Migration
{
	public function safeUp(): bool
	{
		$this->addColumn(Discount::TABLE_NAME, 'messageCondition', $this->json()->null()->after('actionCondition'));

		$rows = (new Query())
			->select(['id', 'actionCondition'])
			->from([Discount::TABLE_NAME])
			->all();

		foreach ($rows as $row) {
			$actionCondition = Json::decodeIfJson($row['actionCondition']);
			if (! is_array($actionCondition) || empty($actionCondition['conditionRules'])) {
				continue;
			}

			$messageRules = [];
			$remainingRules = [];
			foreach ($actionCondition['conditionRules'] as $ruleConfig) {
				if (($ruleConfig['class'] ?? null) === MessageActionRule::class) {
					$messageRules[] = $ruleConfig;
				} else {
					$remainingRules[] = $ruleConfig;
				}
			}

			if ($messageRules === []) {
				continue;
			}

			$actionCondition['conditionRules'] = $remainingRules;
			$messageCondition = [
				'class' => MessageCondition::class,
				'conditionRules' => $messageRules,
			];

			$this->update(Discount::TABLE_NAME, [
				'actionCondition' => $actionCondition,
				'messageCondition' => $messageCondition,
			], [
				'id' => $row['id'],
			], updateTimestamp: false);
		}

		return true;
	}

	public function safeDown(): bool
	{
		if ($this->db->tableExists(Discount::TABLE_NAME)) {
			$this->dropColumn(Discount::TABLE_NAME, 'messageCondition');
		}

		return true;
	}
}
