<?php

namespace fostercommerce\advancedDiscounts\controllers;

use Craft;
use craft\i18n\Locale;
use craft\web\Controller;
use fostercommerce\advancedDiscounts\models\Discount;
use fostercommerce\advancedDiscounts\Plugin;
use yii\web\Response;

class ManageController extends Controller
{
	public $defaultAction = 'index';

	protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

	public function actionIndex(): Response
	{
		return $this->renderTemplate('advanced-discounts/index');
	}

	public function actionList(): Response
	{
		$coupons = Plugin::getInstance()->coupons->getAllCoupons();

		foreach ($coupons as &$coupon) {
			$coupon = $coupon->toArray();
			$coupon['url'] = "advanced-discounts/{$coupon['id']}";
			$coupon['title'] = $coupon['name'];
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
		$coupon = Craft::$app->getUrlManager()->getRouteParams()['coupon']
			?? ($id !== null ? Plugin::getInstance()->coupons->getCouponById($id) : new Discount());

		return $this->renderTemplate('advanced-discounts/edit', [
			'coupon' => $coupon,
			'isNewCoupon' => $coupon->id === null,
		]);
	}

	public function actionDelete(): ?Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$id = (int) $this->request->getRequiredBodyParam('id');

		if (! Plugin::getInstance()->coupons->deleteCoupon($id)) {
			return $this->asFailure(Craft::t('advanced-discounts', 'Coupon not found.'));
		}

		return $this->asSuccess(Craft::t('advanced-discounts', 'Coupon deleted.'));
	}

	public function actionSave(): void
	{
		$this->requirePostRequest();

		$coupon = new Discount();

		$coupon->id = $this->request->getBodyParam('id');
		$coupon->name = $this->request->getBodyParam('name');
		$coupon->code = $this->request->getBodyParam('code');
		$coupon->enabled = (bool) $this->request->getBodyParam('enabled');
		$coupon->setTriggerCondition($this->request->getBodyParam('triggerCondition'));
		$coupon->setActionCondition($this->request->getBodyParam('actionCondition'));

		if (Plugin::getInstance()->coupons->saveCoupon($coupon)) {
			$this->setSuccessFlash(Craft::t('advanced-discounts', 'Coupon saved.'));
			$this->redirectToPostedUrl($coupon);
		} else {
			$this->setFailFlash(Craft::t('advanced-discounts', "Couldn\'t save coupon."));
			Craft::$app->getUrlManager()->setRouteParams([
				'coupon' => $coupon,
			]);
		}
	}
}
