<?php

namespace fostercommerce\advanceddiscounts\services;

use Craft;
use craft\db\Query;
use fostercommerce\advanceddiscounts\models\Coupon;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\records\Coupon as CouponRecord;
use yii\base\Component;
use yii\db\Expression;

class Coupons extends Component
{
	public function getCouponByCode(string $code): ?Coupon
	{
		$result = $this->_createCouponQuery()
			->where([
				'code' => $code,
			])
			->one();

		return $result !== null ? new Coupon($result) : null;
	}

	/**
	 * @return string[]
	 */
	public function getAllCodes(): array
	{
		return $this->_createCouponQuery()->select(['code'])->column();
	}

	/**
	 * @return array<int, int> Coupon count keyed by discount ID
	 */
	public function getCouponCountsByDiscountId(): array
	{
		$rows = (new Query())
			->select([
				'discountId',
				'count' => new Expression('COUNT(*)'),
			])
			->from([CouponRecord::TABLE_NAME])
			->groupBy('discountId')
			->all();

		return array_column($rows, 'count', 'discountId');
	}

	/**
	 * @return Coupon[]
	 */
	public function getCouponsByDiscountId(int $discountId): array
	{
		return array_map(
			static fn (array $row): Coupon => new Coupon($row),
			$this->_createCouponQuery()->where([
				'discountId' => $discountId,
			])->all()
		);
	}

	public function saveDiscountCoupons(Discount $discount): bool
	{
		$existingIds = $this->_createCouponQuery()
			->select(['id'])
			->where([
				'discountId' => $discount->id,
			])
			->column();

		$savedIds = [];
		foreach ($discount->getCoupons() as $index => $coupon) {
			$coupon->discountId = $discount->id;

			if (! $this->saveCoupon($coupon)) {
				$discount->addModelErrors($coupon, "coupons.{$index}");
				continue;
			}

			$savedIds[] = $coupon->id;
		}

		foreach (array_diff($existingIds, $savedIds) as $deletableId) {
			$this->deleteCouponById((int) $deletableId);
		}

		return ! $discount->hasErrors();
	}

	public function saveCoupon(Coupon $coupon, bool $runValidation = true): bool
	{
		if ($coupon->id !== null) {
			$record = CouponRecord::findOne($coupon->id);
			if ($record === null) {
				throw new \RuntimeException("No coupon exists with ID {$coupon->id}");
			}
		} else {
			$record = new CouponRecord();
		}

		if ($runValidation && ! $coupon->validate()) {
			return false;
		}

		$record->discountId = (int) $coupon->discountId;
		$record->code = (string) $coupon->code;
		$record->uses = $coupon->uses;
		$record->maxUses = $coupon->maxUses;
		$record->save(false);
		$coupon->id = $record->id;

		return true;
	}

	public function deleteCouponById(int $id): bool
	{
		$record = CouponRecord::findOne($id);
		if ($record === null) {
			return false;
		}

		return (bool) $record->delete();
	}

	/**
	 * @param int[] $discountIds Discounts that applied to the order this coupon code was entered on
	 */
	public function incrementUses(string $code, array $discountIds): void
	{
		Craft::$app->db->createCommand()
			->update(CouponRecord::TABLE_NAME, [
				'uses' => new Expression('[[uses]] + 1'),
			], [
				'code' => $code,
				'discountId' => $discountIds,
			])
			->execute();
	}

	/**
	 * @return Query<int, array<string, mixed>>
	 */
	private function _createCouponQuery(): Query
	{
		return (new Query())
			->select(['id', 'discountId', 'code', 'uses', 'maxUses'])
			->from([CouponRecord::TABLE_NAME]);
	}
}
