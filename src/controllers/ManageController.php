<?php

namespace fostercommerce\coupons\controllers;

use Craft;
use craft\web\Controller;
use fostercommerce\coupons\models\Coupon;
use fostercommerce\coupons\records\Coupon as CouponRecord;
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

        return $this->renderTemplate('coupons/edit', $variables);
    }
}
