<?php

namespace fostercommerce\advanceddiscounts\controllers;

use Craft;
use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\services\Coupons as CommerceCoupons;
use craft\helpers\Json;
use craft\i18n\Locale;
use craft\web\Controller;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\Plugin;
use yii\web\Response;

class ManageController extends Controller
{
	public $defaultAction = 'index';

	protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

	public function actionIndex(): Response
	{
		Craft::$app->getView()->registerTranslations('advanced-discounts', [
			'Type',
			'Require Coupon Code',
			'Coupons',
			'Times Used',
			'Stops Processing?',
			'Date Created',
			'No discounts yet.',
			'Discounts reordered.',
			"Couldn't reorder discounts.",
		]);

		$formatter = Craft::$app->getFormatter();
		$couponCounts = Plugin::getInstance()->coupons->getCouponCountsByDiscountId();
		$discounts = Plugin::getInstance()->discounts->getAllDiscounts();
		$tableData = array_map(static fn (Discount $discount): array => [
			'id' => $discount->id,
			'url' => "advanced-discounts/{$discount->id}",
			'title' => $discount->name,
			'status' => $discount->enabled ? 'enabled' : 'disabled',
			'type' => $discount->getType()::displayName(),
			'requireCouponCode' => $discount->requireCouponCode,
			'coupons' => $couponCounts[$discount->id] ?? 0,
			'timesUsed' => $discount->uses,
			'stopProcessing' => $discount->stopProcessing,
			'dateCreated' => $discount->dateCreated !== null ? $formatter->asDate($discount->dateCreated, Locale::LENGTH_SHORT) : '',
		], $discounts);

		return $this->renderTemplate('advanced-discounts/index', [
			'tableData' => $tableData,
		]);
	}

	public function actionEdit(?int $id = null): Response
	{
		Craft::$app->getView()->registerTranslations('advanced-discounts', [
			'Number of Coupons',
			'Format',
			'Each # becomes a random character.',
			'Generate',
		]);

		$discount = Craft::$app->getUrlManager()->getRouteParams()['discount']
			?? ($id !== null ? Plugin::getInstance()->discounts->getDiscountById($id) : new Discount());

		$typeOptions = [];
		foreach (Plugin::getInstance()->discountTypes->getAllDiscountTypeInstances() as $type) {
			$typeOptions[] = [
				'value' => $type::handle(),
				'label' => $type::displayName(),
			];
		}

		return $this->renderTemplate('advanced-discounts/edit', [
			'discount' => $discount,
			'isNewDiscount' => $discount->id === null,
			'typeOptions' => $typeOptions,
			'typeSettingsHtml' => $discount->getType()->getSettingsHtml($discount),
		]);
	}

	public function actionTypeSettings(): Response
	{
		$this->requirePostRequest();

		$discount = new Discount([
			'type' => $this->request->getBodyParam('type') ?: 'advanced',
		]);

		$view = Craft::$app->getView();
		$html = $discount->getType()->getSettingsHtml($discount);

		return $this->asJson([
			'html' => $html,
			'headHtml' => $view->getHeadHtml(),
			'bodyHtml' => $view->getBodyHtml(),
		]);
	}

	public function actionGenerateCoupons(): ?Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$count = (int) $this->request->getBodyParam('count', 0);
		$format = $this->request->getBodyParam('format') ?: CommerceCoupons::DEFAULT_COUPON_FORMAT;

		$existingCodes = $this->request->getBodyParam('existingCodes') ?: [];
		if (! is_array($existingCodes)) {
			$existingCodes = [];
		}
		$existingCodes = array_merge($existingCodes, Plugin::getInstance()->coupons->getAllCodes());

		/** @var CommercePlugin $commerce */
		$commerce = CommercePlugin::getInstance();
		$coupons = $commerce->getCoupons()->generateCouponCodes($count, $format, $existingCodes);

		return $this->asSuccess(data: [
			'coupons' => $coupons,
		]);
	}

	public function actionPanel(): Response
	{
		$this->requirePostRequest();

		$type = Plugin::getInstance()->discountTypes->getDiscountTypeByHandle(
			$this->request->getBodyParam('type') ?: 'advanced'
		);
		$discount = new Discount([
			'type' => $type::handle(),
		]);

		$view = Craft::$app->getView();
		$html = $view->renderTemplate('advanced-discounts/_panel', [
			'panel' => $discount->panels[0],
			'actionLabel' => $type::actionLabel(),
			'actionInstructions' => $type::actionInstructions(),
			'bundle' => $type::actionConditionClass() === BundleCondition::class,
		]);

		return $this->asJson([
			'html' => $html,
			'headHtml' => $view->getHeadHtml(),
			'bodyHtml' => $view->getBodyHtml(),
		]);
	}

	public function actionReorder(): ?Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$ids = Json::decode($this->request->getRequiredBodyParam('ids'));

		if (! Plugin::getInstance()->discounts->reorderDiscounts($ids)) {
			return $this->asFailure(Craft::t('advanced-discounts', "Couldn't reorder discounts."));
		}

		return $this->asSuccess(Craft::t('advanced-discounts', 'Discounts reordered.'));
	}

	public function actionDelete(): ?Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$id = (int) $this->request->getRequiredBodyParam('id');

		if (! Plugin::getInstance()->discounts->deleteDiscount($id)) {
			return $this->asFailure(Craft::t('advanced-discounts', 'Discount not found.'));
		}

		return $this->asSuccess(Craft::t('advanced-discounts', 'Discount deleted.'));
	}

	public function actionSave(): void
	{
		$this->requirePostRequest();

		$discount = new Discount();

		$discount->id = $this->request->getBodyParam('id');
		$discount->name = $this->request->getBodyParam('name');
		$discount->requireCouponCode = (bool) $this->request->getBodyParam('requireCouponCode');
		$discount->setCoupons($this->request->getBodyParam('coupons') ?? []);
		$discount->enabled = (bool) $this->request->getBodyParam('enabled');
		$discount->stopProcessing = (bool) $this->request->getBodyParam('stopProcessing');
		$discount->type = $this->request->getBodyParam('type') ?: 'advanced';
		$discount->setGlobalCartCondition($this->request->getBodyParam('globalCartCondition'));
		$discount->setPanels($this->request->getBodyParam('panels') ?? []);

		if (Plugin::getInstance()->discounts->saveDiscount($discount)) {
			$this->setSuccessFlash(Craft::t('advanced-discounts', 'Discount saved.'));
			$this->redirectToPostedUrl($discount);
		} else {
			$this->setFailFlash(Craft::t('advanced-discounts', "Couldn't save discount."));
			Craft::$app->getUrlManager()->setRouteParams([
				'discount' => $discount,
			]);
		}
	}
}
