<?php

namespace fostercommerce\coupons\controllers;

use Craft;
use craft\web\Controller;
use fostercommerce\coupons\models\Coupon;
use fostercommerce\coupons\records\Coupon as CouponRecord;
use craft\commerce\Plugin as Commerce;
use yii\web\Response;

class ManageController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionIndex(): Response
    {
        return $this->renderTemplate('coupons/index');
    }

    public function actionEdit(): Response
    {
        $variables['coupon'] = new Coupon();
        $variables['isNewCoupon'] = true;
        $variables['relatedTo'] = [
            CouponRecord::RELATED_TO_ANY => Craft::t('coupons', 'Match any of the related items'),
            CouponRecord::RELATED_TO_ALL => Craft::t('coupons', 'Match all of the related items'),
            CouponRecord::RELATED_TO_NONE => Craft::t('coupons', 'Match none of the related items'),
        ];
        $variables['purchasables'] = [
            CouponRecord::PURCHASABLES_ANY => Craft::t('coupons', 'Match any of the selected purchasables'),
            CouponRecord::PURCHASABLES_ALL => Craft::t('coupons', 'Match all of the selected purchasables'),
            CouponRecord::PURCHASABLES_NONE => Craft::t('coupons', 'Match none of the selected purchasables'),
        ];
        $variables['discountTypes'] = [
            CouponRecord::DISCOUNT_TYPE_NONE => Craft::t('coupons', 'No discount'),
            CouponRecord::DISCOUNT_TYPE_PERCENTAGE => Craft::t('coupons', 'Apply a percentage discount'),
            CouponRecord::DISCOUNT_TYPE_FLAT_AMOUNT => Craft::t('coupons', 'Apply a flat amount discount'),
        ];
        $variables['applyTo'] = [
            CouponRecord::APPLY_TO_ORDER => Craft::t('coupons', 'Apply discount to whole order'),
            CouponRecord::APPLY_TO_TRIGGER_ITEMS => Craft::t('coupons', 'Apply discount to matching trigger items'),
            CouponRecord::APPLY_TO_CONDITIONAL_ITEMS => Craft::t('coupons', 'Apply discount to matched conditional items'),
        ];
        $variables['applyShipping'] = [
            'any' => Craft::t('coupons', 'Apply discount to any shipping method'),
            ...array_merge(...array_map( // This flattens the array of shipping methods so that it can be merged into the shipping options array.
                static fn($shippingMethod) => ["handle:{$shippingMethod->handle}" => Craft::t('coupons', 'Apply discount to ').$shippingMethod->name],
                Commerce::getInstance()->shippingMethods->getAllShippingMethods()
            )),
        ];

        return $this->renderTemplate('coupons/edit', $variables);
    }
}
