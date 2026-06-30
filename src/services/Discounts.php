<?php

namespace fostercommerce\advancedDiscounts\services;

use Craft;
use craft\db\Query;
use fostercommerce\advancedDiscounts\models\Discount;
use fostercommerce\advancedDiscounts\records\Discount as DiscountRecord;
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
	public function getAllCoupons(): array
	{
		if ($this->_discounts === null) {
			$this->_discounts = [];
			$query = $this->_createCouponQuery();
			foreach ($query->all() as $couponRecord) {
				$this->_discounts[] = $this->_populateCoupon($couponRecord);
			}
		}

		return $this->_discounts;
	}

	public function getCouponById(int $id): ?Discount
	{
		$record = $this->_createCouponQuery()->andWhere([
			'[[discounts.id]]' => $id,
		])->one();

		return $record !== null ? $this->_populateCoupon($record) : null;
	}

	public function getCouponByCode(string $code): ?Discount
	{
		foreach ($this->getAllCoupons() as $coupon) {
			if (strcasecmp($coupon->code, $code) === 0) {
				return $coupon;
			}
		}

		return null;
	}

	public function deleteCoupon(int $id): bool
	{
		$record = DiscountRecord::findOne($id);
		if ($record === null) {
			return false;
		}

		$record->delete();
		$this->_discounts = null;

		return true;
	}

	public function saveCoupon(Discount $coupon, bool $runValidation = true): bool
	{
		$isNew = $coupon->id === null;

		if ($isNew) {
			$record = new DiscountRecord();
		} else {
			$record = DiscountRecord::findOne($coupon->id);
			if ($record === null) {
				throw new \RuntimeException("No coupon exists with ID {$coupon->id}");
			}
		}

		if ($runValidation && ! $coupon->validate()) {
			Craft::debug('Discount not saved due to validation error.', __METHOD__);
			return false;
		}

		$record->name = $coupon->name;
		$record->code = $coupon->code;
		$record->enabled = $coupon->enabled;
		$record->triggerCondition = $coupon->getTriggerCondition()->getConfig();
		$record->actionCondition = $coupon->getActionCondition()->getConfig();

		// In the future we may have multiple things that would need to be saved here.
		$db = Craft::$app->db;
		$transaction = $db->beginTransaction();
		try {
			$record->save();
			$coupon->id = $record->id;
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
	private function _populateCoupon(array $record): Discount
	{
		return new Discount($record);
	}

	/**
	 * @return Query<int, array<string, mixed>>
	 */
	private function _createCouponQuery(): Query
	{
		return (new Query())
			->select([
				'[[discounts.id]]',
				'[[discounts.name]]',
				'[[discounts.code]]',
				'[[discounts.enabled]]',
				'[[discounts.triggerCondition]]',
				'[[discounts.actionCondition]]',
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
