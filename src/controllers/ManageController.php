<?php

namespace fostercommerce\coupons\controllers;

use Craft;
use craft\web\Controller;
use fostercommerce\coupons\models\Coupon;
use fostercommerce\coupons\Plugin;
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

    public function actionEdit(?int $id = null): Response
    {
        $variables = [];

        if ($id !== null) {
            $variables['coupon'] = Plugin::getInstance()->coupons->getCouponById($id);
        } else {
            $variables['coupon'] = new Coupon();
        }
        $variables['isNewCoupon'] = true;

        return $this->renderTemplate('coupons/edit', $variables);
    }

    public function actionSave(): void
    {
        $this->requirePostRequest();

        $coupon = new Coupon();

        $coupon->id = $this->request->getBodyParam('id');
        $coupon->name = $this->request->getBodyParam('name');
        $coupon->code = $this->request->getBodyParam('code');
        $coupon->setTriggerCondition($this->request->getBodyParam('triggerCondition'));
        $coupon->setActionCondition($this->request->getBodyParam('actionCondition'));

        Plugin::getInstance()->coupons->saveCoupon($coupon);
    }
}
