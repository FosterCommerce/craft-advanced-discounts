<?php

namespace fostercommerce\advanceddiscounts\services;

use Craft;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\records\Discount as DiscountRecord;
use yii\base\Component;

class Discounts extends Component
{
	/**
	 * @var Discount[]|null
	 */
	private ?array $_discounts = null;

	/**
	 * @return Discount[]
	 */
	public function getAllDiscounts(): array
	{
		if ($this->_discounts === null) {
			$this->_discounts = [];
			$query = $this->_createDiscountQuery();
			foreach ($query->all() as $discountRecord) {
				$this->_discounts[] = $this->_populateDiscount($discountRecord);
			}
		}

		return $this->_discounts;
	}

	public function getDiscountById(int $id): ?Discount
	{
		$record = $this->_createDiscountQuery()->andWhere([
			'[[discounts.id]]' => $id,
		])->one();

		return $record !== null ? $this->_populateDiscount($record) : null;
	}

	public function getDiscountByCode(string $code): ?Discount
	{
		foreach ($this->getAllDiscounts() as $discount) {
			if ($discount->code !== null && strcasecmp($discount->code, $code) === 0) {
				return $discount;
			}
		}

		return null;
	}

	public function deleteDiscount(int $id): bool
	{
		$record = DiscountRecord::findOne($id);
		if ($record === null) {
			return false;
		}

		$record->delete();
		$this->_discounts = null;

		return true;
	}

	public function saveDiscount(Discount $discount, bool $runValidation = true): bool
	{
		$isNew = $discount->id === null;

		if ($isNew) {
			$record = new DiscountRecord();
		} else {
			$record = DiscountRecord::findOne($discount->id);
			if ($record === null) {
				throw new \RuntimeException("No discount exists with ID {$discount->id}");
			}
		}

		if ($runValidation && ! $discount->validate()) {
			Craft::debug('Discount not saved due to validation error.', __METHOD__);
			return false;
		}

		$record->name = $discount->name;
		$record->code = $discount->code;
		$record->enabled = $discount->enabled;
		$record->type = $discount->type;
		$record->settings = [
			'globalCartCondition' => $discount->getGlobalCartCondition()->getConfig(),
			'panels' => array_map(static fn ($panel): array => $panel->getConfig(), $discount->panels),
		];

		// In the future we may have multiple things that would need to be saved here.
		$db = Craft::$app->db;
		$transaction = $db->beginTransaction();
		try {
			$record->save();
			$discount->id = $record->id;
			$transaction->commit();
			$this->_discounts = null;
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}

		return true;
	}

	/**
	 * @param array<string, mixed> $record
	 */
	private function _populateDiscount(array $record): Discount
	{
		$discount = new Discount([
			'id' => $record['id'],
			'name' => $record['name'],
			'code' => $record['code'],
			'enabled' => $record['enabled'],
			'type' => $record['type'] ?? 'advanced',
			'dateCreated' => $record['dateCreated'],
			'dateUpdated' => $record['dateUpdated'],
		]);

		$settings = Json::decodeIfJson($record['settings'] ?? '');
		$settings = is_array($settings) ? $settings : [];

		$discount->setGlobalCartCondition($settings['globalCartCondition'] ?? []);
		$discount->setPanels($settings['panels'] ?? []);

		return $discount;
	}

	/**
	 * @return Query<int, array<string, mixed>>
	 */
	private function _createDiscountQuery(): Query
	{
		return (new Query())
			->select([
				'[[discounts.id]]',
				'[[discounts.name]]',
				'[[discounts.code]]',
				'[[discounts.enabled]]',
				'[[discounts.type]]',
				'[[discounts.settings]]',
				'[[discounts.dateCreated]]',
				'[[discounts.dateUpdated]]',
			])
			->from([
				'discounts' => DiscountRecord::TABLE_NAME,
			])
			->orderBy([
				'dateUpdated' => SORT_DESC,
			]);
	}
}
