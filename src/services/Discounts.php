<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\services;

use Craft;
use craft\commerce\elements\Order;
use craft\db\Query;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\enums\TaxBasis;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\Plugin;
use fostercommerce\advanceddiscounts\records\Discount as DiscountRecord;
use RuntimeException;
use Throwable;
use yii\base\Component;
use yii\db\Expression;

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
				$discount = $this->_populateDiscount($discountRecord);
				if ($discount !== null) {
					$this->_discounts[] = $discount;
				}
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
		$coupon = Plugin::getInstance()->coupons->getCouponByCode($code);
		if ($coupon === null || $coupon->discountId === null) {
			return null;
		}

		return $this->getDiscountById($coupon->discountId);
	}

	public function getTaxBasis(Order $order): TaxBasis
	{
		/** @var Plugin $plugin */
		$plugin = Plugin::getInstance();
		$defaultTaxBasis = $plugin->getSettings()->getTaxBasis();

		foreach ($this->getAllDiscounts() as $discount) {
			if (! $discount->enabled || ! $discount->matchesCouponCode($order->couponCode)) {
				continue;
			}

			if (($discount->taxBasis ?? $defaultTaxBasis) === TaxBasis::BeforeDiscount) {
				return TaxBasis::BeforeDiscount;
			}
		}

		return TaxBasis::AfterDiscount;
	}

	/**
	 * @param int[] $ids Discount IDs in their new order
	 */
	public function reorderDiscounts(array $ids): bool
	{
		$db = Craft::$app->db;
		$transaction = $db->beginTransaction();

		try {
			foreach ($ids as $sortOrder => $id) {
				$db->createCommand()
					->update(DiscountRecord::TABLE_NAME, [
						'sortOrder' => $sortOrder + 1,
					], [
						'id' => $id,
					])
					->execute();
			}

			$transaction->commit();
		} catch (Throwable $throwable) {
			$transaction->rollBack();
			Craft::error("Couldn't reorder discounts: {$throwable->getMessage()}", __METHOD__);

			return false;
		}

		$this->_discounts = null;

		return true;
	}

	/**
	 * @param int[] $discountIds Discounts that applied to a completed order
	 */
	public function incrementUses(array $discountIds): void
	{
		Craft::$app->db->createCommand()
			->update(DiscountRecord::TABLE_NAME, [
				'uses' => new Expression('[[uses]] + 1'),
			], [
				'id' => $discountIds,
			])
			->execute();

		$this->_discounts = null;
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
				throw new RuntimeException("No discount exists with ID {$discount->id}");
			}
		}

		if ($runValidation && ! $discount->validate()) {
			Craft::debug('Discount not saved due to validation error.', __METHOD__);
			return false;
		}

		$record->name = $discount->name;
		$record->requireCouponCode = $discount->requireCouponCode;
		$record->enabled = $discount->enabled;
		$record->stopProcessing = $discount->stopProcessing;
		$record->type = $discount->type;
		$record->settings = [
			'taxBasis' => $discount->taxBasis?->value,
			'globalCartCondition' => $discount->getGlobalCartCondition()->getConfig(),
			'panels' => array_map(static fn ($panel): array => $panel->getConfig(), $discount->panels),
		];

		$db = Craft::$app->db;
		$transaction = $db->beginTransaction();
		try {
			if ($isNew) {
				$db->createCommand()
					->update(DiscountRecord::TABLE_NAME, [
						'sortOrder' => new Expression('[[sortOrder]] + 1'),
					])
					->execute();
				$record->sortOrder = 1;
			}

			$record->save();
			$discount->id = $record->id;
			$discount->sortOrder = $record->sortOrder;

			if (! Plugin::getInstance()->coupons->saveDiscountCoupons($discount)) {
				$transaction->rollBack();
				return false;
			}

			$transaction->commit();
			$this->_discounts = null;
		} catch (Throwable $throwable) {
			$transaction->rollBack();
			throw $throwable;
		}

		return true;
	}

	/**
	 * @param array<string, mixed> $record
	 */
	private function _populateDiscount(array $record): ?Discount
	{
		$type = is_string($record['type'] ?? null) ? $record['type'] : 'advanced';
		if (Plugin::getInstance()->discountTypes->findDiscountTypeByHandle($type) === null) {
			$id = is_scalar($record['id'] ?? null) ? (string) $record['id'] : '';
			Craft::warning(
				"Skipping discount {$id}: nothing registers the discount type “{$type}”, so its rules cannot be read and it will not apply to any order.",
				__METHOD__
			);

			return null;
		}

		$discount = new Discount([
			'id' => $record['id'],
			'name' => $record['name'],
			'requireCouponCode' => $record['requireCouponCode'] ?? false,
			'enabled' => $record['enabled'],
			'stopProcessing' => $record['stopProcessing'] ?? false,
			'uses' => $record['uses'] ?? 0,
			'sortOrder' => $record['sortOrder'] ?? null,
			'type' => $type,
			'dateCreated' => $record['dateCreated'],
			'dateUpdated' => $record['dateUpdated'],
		]);

		$settings = Json::decodeIfJson($record['settings'] ?? '');
		$settings = is_array($settings) ? $settings : [];

		$discount->taxBasis = is_string($settings['taxBasis'] ?? null)
			? TaxBasis::tryFrom($settings['taxBasis'])
			: null;
		$discount->setGlobalCartCondition($settings['globalCartCondition'] ?? []);
		$discount->setPanels($settings['panels'] ?? []);

		return $discount;
	}

	/**
	 * @return Query<int, array<string, mixed>>
	 */
	private function _createDiscountQuery(): Query
	{
		/** @var Query<int, array<string, mixed>> $query */
		$query = (new Query())
			->select([
				'[[discounts.id]]',
				'[[discounts.name]]',
				'[[discounts.requireCouponCode]]',
				'[[discounts.enabled]]',
				'[[discounts.stopProcessing]]',
				'[[discounts.uses]]',
				'[[discounts.sortOrder]]',
				'[[discounts.type]]',
				'[[discounts.settings]]',
				'[[discounts.dateCreated]]',
				'[[discounts.dateUpdated]]',
			])
			->from([
				'discounts' => DiscountRecord::TABLE_NAME,
			])
			->orderBy([
				'sortOrder' => SORT_ASC,
			]);

		return $query;
	}
}
