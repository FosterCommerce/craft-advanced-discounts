<?php

namespace fostercommerce\coupons\services;

use craft\commerce\models\Discount;
use craft\commerce\services\Discounts;
use fostercommerce\coupons\Plugin;

class CouponsAwareDiscountsService extends Discounts
{
	public function getDiscountByCode(?string $code, ?int $storeId = null): ?Discount
	{
		$discount = parent::getDiscountByCode($code, $storeId);
		if ($discount !== null) {
			return $discount;
		}

		$coupon = Plugin::getInstance()->coupons->getCouponByCode($code ?? '');
		if ($coupon === null || ! $coupon->enabled) {
			return null;
		}

		$synthetic = new Discount();
		$synthetic->name = $coupon->name;

		return $synthetic;
	}
}
