<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\migrations;

use craft\commerce\elements\Variant;
use craft\db\Migration;
use craft\db\Query;
use craft\elements\Entry;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemMatchConditionRule;
use fostercommerce\advanceddiscounts\records\Discount;

/**
 * Folds the separate "Has Purchasable" and "Related To" line item rules into one rule that
 * compares a quantity against either match.
 */
class m260820_120000_merge_line_item_condition_rules extends Migration
{
	private const HAS_PURCHASABLE_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\HasPurchasableConditionRule';

	private const RELATED_TO_CLASS = 'fostercommerce\\advanceddiscounts\\elements\\conditions\\RelatedToConditionRule';

	public function safeUp(): bool
	{
		/** @var array<int, array{id: int, settings: ?string}> $rows */
		$rows = (new Query())
			->select(['id', 'settings'])
			->from(Discount::TABLE_NAME)
			->all();

		foreach ($rows as $row) {
			$settings = Json::decodeIfJson($row['settings'] ?? '');
			if (! is_array($settings)) {
				continue;
			}

			$migrated = $this->migrateRules($settings);
			if ($migrated === $settings) {
				continue;
			}

			$this->update(Discount::TABLE_NAME, [
				'settings' => Json::encode($migrated),
			], [
				'id' => $row['id'],
			], [], false);
		}

		return true;
	}

	/**
	 * Condition rules nest at several depths and under several keys, so every branch of the
	 * settings tree is walked rather than the known paths.
	 *
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	private function migrateRules(array $config): array
	{
		foreach ($config as $key => $value) {
			if (is_array($value)) {
				$config[$key] = $this->migrateRules($value);
			}
		}

		return match ($config['class'] ?? null) {
			self::HAS_PURCHASABLE_CLASS => $this->migrateHasPurchasable($config),
			self::RELATED_TO_CLASS => $this->migrateRelatedTo($config),
			default => $config,
		};
	}

	/**
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	private function migrateHasPurchasable(array $config): array
	{
		$elementType = $config['purchasableType'] ?? Variant::class;
		unset($config['purchasableType']);

		return [
			...$config,
			'class' => LineItemMatchConditionRule::class,
			'matchType' => LineItemMatchConditionRule::MATCH_PURCHASABLE,
			'elementType' => $elementType,
		];
	}

	/**
	 * A "Related To" rule only ever tested for presence, so it carries over with no quantity.
	 *
	 * @param array<mixed> $config
	 * @return array<mixed>
	 */
	private function migrateRelatedTo(array $config): array
	{
		return [
			...$config,
			'class' => LineItemMatchConditionRule::class,
			'matchType' => LineItemMatchConditionRule::MATCH_RELATED,
			'elementType' => $config['elementType'] ?? Entry::class,
			'operator' => '>=',
			'quantity' => null,
		];
	}
}
