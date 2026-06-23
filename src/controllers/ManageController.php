<?php

namespace fostercommerce\coupons\controllers;

use Craft;
use craft\i18n\Locale;
use craft\web\Controller;
use fostercommerce\coupons\models\Coupon;
use fostercommerce\coupons\Plugin;
use yii\web\Response;

class ManageController extends Controller
{
	public $defaultAction = 'index';

	protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

	public function actionIndex(): Response
	{
		return $this->renderTemplate('coupons/index');
	}

	public function actionList(): Response
	{
		$coupons = Plugin::getInstance()->coupons->getAllCoupons();

		foreach ($coupons as &$coupon) {
			$coupon = $coupon->toArray();
			$coupon['url'] = "coupons/{$coupon['id']}";
			$coupon['dateCreated'] = Craft::$app->getFormatter()
				->asDate($coupon['dateCreated'], Locale::LENGTH_SHORT);
			$coupon['dateUpdated'] = Craft::$app->getFormatter()
				->asDate($coupon['dateUpdated'], Locale::LENGTH_SHORT);
		}
		return $this->asJson([
			'data' => $coupons,
			'pagination' => false,
		]);
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
		$coupon->title = $this->request->getBodyParam('title');
		$coupon->code = $this->request->getBodyParam('code');
		$coupon->setTriggerCondition($this->request->getBodyParam('triggerCondition'));
		$coupon->setActionCondition($this->request->getBodyParam('actionCondition'));

		if (Plugin::getInstance()->coupons->saveCoupon($coupon)) {
			$this->setSuccessFlash(Craft::t('coupons', 'Coupon saved.'));
			$this->redirectToPostedUrl($coupon);
		} else {
			$this->setFailFlash(Craft::t('coupons', 'Couldn’t save coupon.'));
		}
	}
}
