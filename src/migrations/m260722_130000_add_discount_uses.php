<?php

namespace fostercommerce\advanceddiscounts\migrations;

use craft\db\Migration;
use craft\db\Query;
use fostercommerce\advanceddiscounts\records\Coupon;
use fostercommerce\advanceddiscounts\records\Discount;

class m260722_130000_add_discount_uses extends Migration
{
	public function safeUp(): bool
	{
		$this->addColumn(Discount::TABLE_NAME, 'uses', $this->integer()->notNull()->defaultValue(0)->after('stopProcessing'));

		$sums = (new Query())
			->select([
				'discountId',
				'uses' => 'SUM([[uses]])',
			])
			->from(Coupon::TABLE_NAME)
			->groupBy('discountId')
			->all();

		foreach ($sums as $sum) {
			$this->update(Discount::TABLE_NAME, [
				'uses' => $sum['uses'],
			], [
				'id' => $sum['discountId'],
			], updateTimestamp: false);
		}

		return true;
	}
}
