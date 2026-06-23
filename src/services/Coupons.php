<?php

namespace fostercommerce\coupons\services;

use Craft;
use craft\db\Query;
use fostercommerce\coupons\models\Coupon;
use fostercommerce\coupons\records\Coupon as CouponRecord;
use yii\base\Component;

class Coupons extends Component
{
	/**
	 * @var Coupon[]|null
	 */
	private ?array $_discounts = null;

	/**
	 * @return Coupon[]
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

	public function getCouponById(int $id): Coupon
	{
		return $this->_populateCoupon($this->_createCouponQuery()->andWhere([
			'[[coupons.id]]' => $id,
		])->one());
	}

	public function saveCoupon(Coupon $coupon, bool $runValidation = true): bool
	{
		$isNew = $coupon->id === null;

		if ($isNew) {
			$record = new CouponRecord();
		} else {
			$record = CouponRecord::findOne($coupon->id);
			if ($record === null) {
				throw new \RuntimeException("No coupon exists with ID {$coupon->id}");
			}
		}

		if ($runValidation && ! $coupon->validate()) {
			Craft::debug('Coupon not saved due to validation error.', __METHOD__);
			return false;
		}

		$record->title = $coupon->title;
		$record->code = $coupon->code;
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

	private function _populateCoupon(array $record): Coupon
	{
		return new Coupon($record);
	}

	private function _createCouponQuery(): Query
	{
		return (new Query())
			->select([
				'[[coupons.id]]',
				'[[coupons.title]]',
				'[[coupons.code]]',
				'[[coupons.triggerCondition]]',
				'[[coupons.actionCondition]]',
				'[[coupons.dateCreated]]',
				'[[coupons.dateUpdated]]',
			])
			->from([
				'coupons' => CouponRecord::TABLE_NAME,
			])
			->orderBy([
				'dateUpdated' => SORT_DESC,
			]);
	}
}
