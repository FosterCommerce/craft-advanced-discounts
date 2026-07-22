<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\elements\conditions\CartActionCondition;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodCartActionRule;
use fostercommerce\advanceddiscounts\records\Discount;

class m260720_140000_rename_action_condition_to_cart_action_condition extends Migration
{
	private const OLD_CONDITION_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\ActionCondition';

	private const OLD_RULE_CLASS_MAP = [
		'fostercommerce\\advanceddiscounts\\elements\\conditions\\OrderActionRule' => OrderCartActionRule::class,
		'fostercommerce\\advanceddiscounts\\elements\\conditions\\LineItemActionRule' => LineItemCartActionRule::class,
		'fostercommerce\\advanceddiscounts\\elements\\conditions\\ShippingMethodActionRule' => ShippingMethodCartActionRule::class,
	];

	public function safeUp(): bool
	{
		$this->renameColumn(Discount::TABLE_NAME, 'actionCondition', 'cartActionCondition');

		$this->_remapStoredConditions(self::OLD_CONDITION_CLASS, CartActionCondition::class, self::OLD_RULE_CLASS_MAP);

		return true;
	}

	/**
	 * @param array<string, string> $ruleClassMap
	 */
	private function _remapStoredConditions(string $oldConditionClass, string $newConditionClass, array $ruleClassMap): bool
	{
		$rows = (new Query())
			->select(['id', 'cartActionCondition'])
			->from([Discount::TABLE_NAME])
			->all();

		foreach ($rows as $row) {
			$condition = Json::decodeIfJson($row['cartActionCondition']);
			if (! is_array($condition)) {
				continue;
			}

			if (($condition['class'] ?? null) === $oldConditionClass) {
				$condition['class'] = $newConditionClass;
			}

			if (! empty($condition['conditionRules']) && is_array($condition['conditionRules'])) {
				foreach ($condition['conditionRules'] as &$ruleConfig) {
					if (! is_array($ruleConfig)) {
						continue;
					}

					$ruleClass = $ruleConfig['class'] ?? null;
					if ($ruleClass !== null && isset($ruleClassMap[$ruleClass])) {
						$ruleConfig['class'] = $ruleClassMap[$ruleClass];
					}
				}
				unset($ruleConfig);
			}

			$this->update(Discount::TABLE_NAME, [
				'cartActionCondition' => $condition,
			], [
				'id' => $row['id'],
			], updateTimestamp: false);
		}

		return true;
	}
}
