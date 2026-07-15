<?php

namespace fostercommerce\advanceddiscounts\services;

use craft\commerce\models\Discount;
use craft\commerce\services\Discounts;
use fostercommerce\advanceddiscounts\Plugin;

class AdvancedDiscountsService extends Discounts
{
	public function getDiscountByCode(?string $code, ?int $storeId = null): ?Discount
	{
		$discount = parent::getDiscountByCode($code, $storeId);
		if ($discount !== null) {
			return $discount;
		}

		$advancedDiscount = Plugin::getInstance()->discounts->getDiscountByCode($code ?? '');
		if ($advancedDiscount === null || ! $advancedDiscount->enabled) {
			return null;
		}

		$synthetic = new Discount();
		$synthetic->name = $advancedDiscount->name;

		return $synthetic;
	}
}
