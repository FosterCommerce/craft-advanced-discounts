<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use fostercommerce\advanceddiscounts\records\Coupon;
use fostercommerce\advanceddiscounts\records\Discount;

class m260722_120000_add_coupons extends Migration
{
	public function safeUp(): bool
	{
		$this->createTable(Coupon::TABLE_NAME, [
			'id' => $this->primaryKey(),
			'discountId' => $this->integer()->notNull(),
			'code' => $this->string()->notNull(),
			'uses' => $this->integer()->notNull()->defaultValue(0),
			'maxUses' => $this->integer()->null(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid' => $this->uid(),
		]);
		$this->createIndex(null, Coupon::TABLE_NAME, ['code'], true);
		$this->addForeignKey(null, Coupon::TABLE_NAME, ['discountId'], Discount::TABLE_NAME, ['id'], 'CASCADE');

		$this->addColumn(Discount::TABLE_NAME, 'requireCouponCode', $this->boolean()->notNull()->defaultValue(false)->after('name'));

		$rows = (new Query())
			->select(['id', 'code'])
			->from(Discount::TABLE_NAME)
			->where([
				'not', [
					'code' => null,
				]])
			->andWhere([
				'not', [
					'code' => '',
				]])
			->all();

		foreach ($rows as $row) {
			$this->insert(Coupon::TABLE_NAME, [
				'discountId' => $row['id'],
				'code' => $row['code'],
			]);

			$this->update(Discount::TABLE_NAME, [
				'requireCouponCode' => true,
			], [
				'id' => $row['id'],
			], updateTimestamp: false);
		}

		$this->dropColumn(Discount::TABLE_NAME, 'code');

		return true;
	}
}
