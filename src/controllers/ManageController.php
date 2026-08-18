<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\controllers;

use Craft;
use craft\base\Element;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\services\Coupons as CommerceCoupons;
use craft\helpers\AdminTable;
use craft\helpers\Json;
use craft\i18n\Locale;
use craft\web\Controller;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;
use fostercommerce\advanceddiscounts\enums\TaxBasis;
use fostercommerce\advanceddiscounts\helpers\Purchasables;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\Plugin;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ManageController extends Controller
{
	public $defaultAction = 'index';

	protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

	public function beforeAction($action): bool
	{
		if (! parent::beforeAction($action)) {
			return false;
		}

		$this->requirePermission('commerce-managePromotions');

		return true;
	}

	public function actionIndex(): Response
	{
		Craft::$app->getView()->registerTranslations('advanced-discounts', [
			'index.column.type',
			'index.column.requireCouponCode',
			'index.column.coupons',
			'index.column.timesUsed',
			'index.column.stopsProcessing',
			'index.column.dateCreated',
			'index.empty',
			'index.flash.reordered',
			'index.flash.reorderFailed',
		]);

		$formatter = Craft::$app->getFormatter();
		$couponCounts = Plugin::getInstance()->coupons->getCouponCountsByDiscountId();
		$discounts = Plugin::getInstance()->discounts->getAllDiscounts();
		$tableData = array_map(static fn (Discount $discount): array => [
			'id' => $discount->id,
			'url' => "advanced-discounts/{$discount->id}",
			'title' => $discount->name,
			'status' => $discount->enabled,
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

	public function actionExcludedVariants(): Response
	{
		Craft::$app->getView()->registerTranslations('advanced-discounts', [
			'excludedVariants.column.variant',
			'excludedVariants.column.sku',
			'excludedVariants.column.product',
			'excludedVariants.empty',
		]);

		return $this->renderTemplate('advanced-discounts/excluded-variants');
	}

	public function actionExcludedVariantsData(): ?Response
	{
		$this->requireAcceptsJson();

		$page = $this->intParam('page', 1);
		$perPage = $this->intParam('per_page', 100);
		$search = $this->request->getParam('search');

		/** @var CommercePlugin $commerce */
		$commerce = CommercePlugin::getInstance();
		$store = $commerce->getStores()->getPrimaryStore();
		$variantIds = $store !== null && $store->id !== null
			? Purchasables::nonPromotablePurchasableIds($store->id)
			: [];

		$tableData = [];
		$total = 0;
		if ($variantIds !== []) {
			$query = Variant::find()
				->id($variantIds)
				->status(Element::STATUS_ENABLED)
				->productStatus(Element::STATUS_ENABLED);

			if ($search) {
				$query->search($search);
			}

			$total = (int) (clone $query)->count();

			foreach ($query->offset(($page - 1) * $perPage)->limit($perPage)->all() as $variant) {
				$product = $variant->getProduct();
				$tableData[] = [
					'id' => $variant->id,
					'title' => $variant->title,
					'url' => $product?->getCpEditUrl(),
					'sku' => $variant->sku,
					'product' => $product?->title ?? '',
				];
			}
		}

		return $this->asSuccess(data: [
			'pagination' => AdminTable::paginationLinks($page, $total, $perPage),
			'data' => $tableData,
		]);
	}

	public function actionEdit(?int $id = null): Response
	{
		Craft::$app->getView()->registerTranslations('advanced-discounts', [
			'edit.coupons.generateCount',
			'edit.coupons.generateFormat',
			'edit.coupons.generateFormatInstructions',
			'edit.coupons.generate',
		]);

		$discount = Craft::$app->getUrlManager()->getRouteParams()['discount']
			?? ($id !== null ? Plugin::getInstance()->discounts->getDiscountById($id) : new Discount());

		if ($discount === null) {
			throw new NotFoundHttpException(Craft::t('advanced-discounts', 'edit.error.notFound'));
		}

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
			'taxBasisOptions' => TaxBasis::options(),
			'typeSettingsHtml' => $discount->getType()->getSettingsHtml($discount),
		]);
	}

	public function actionTypeSettings(): Response
	{
		$this->requirePostRequest();

		$discount = new Discount([
			'type' => $this->stringParam('type', 'advanced'),
		]);

		$view = Craft::$app->getView();
		$html = $discount->getType()->getSettingsHtml($discount);
		$placeholdersHtml = $view->renderTemplate('advanced-discounts/_message-placeholders-table', [
			'placeholders' => $discount->getType()::messagePlaceholders(),
		]);

		return $this->asJson([
			'html' => $html,
			'placeholdersHtml' => $placeholdersHtml,
			'headHtml' => $view->getHeadHtml(),
			'bodyHtml' => $view->getBodyHtml(),
		]);
	}

	public function actionGenerateCoupons(): ?Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$count = $this->intParam('count', 0);
		$format = $this->stringParam('format', CommerceCoupons::DEFAULT_COUPON_FORMAT);

		$existingCodes = $this->request->getBodyParam('existingCodes');
		$existingCodes = array_merge(
			is_array($existingCodes) ? array_map('strval', $existingCodes) : [],
			Plugin::getInstance()->coupons->getAllCodes()
		);

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

		$type = Plugin::getInstance()->discountTypes->getDiscountTypeByHandle($this->stringParam('type', 'advanced'));
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

		$ids = Json::decode($this->stringParam('ids', '[]'));

		if (! is_array($ids) || ! Plugin::getInstance()->discounts->reorderDiscounts(array_map('intval', $ids))) {
			return $this->asFailure(Craft::t('advanced-discounts', 'index.flash.reorderFailed'));
		}

		return $this->asSuccess(Craft::t('advanced-discounts', 'index.flash.reordered'));
	}

	public function actionDelete(): ?Response
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();

		$id = $this->intParam('id', 0);

		if (! Plugin::getInstance()->discounts->deleteDiscount($id)) {
			return $this->asFailure(Craft::t('advanced-discounts', 'index.flash.notFound'));
		}

		return $this->asSuccess(Craft::t('advanced-discounts', 'index.flash.deleted'));
	}

	public function actionSave(): ?Response
	{
		$this->requirePostRequest();

		$id = $this->request->getBodyParam('id');
		$coupons = $this->request->getBodyParam('coupons');
		$globalCartCondition = $this->request->getBodyParam('globalCartCondition');
		$panels = $this->request->getBodyParam('panels');
		$taxBasis = $this->request->getBodyParam('taxBasis');

		$discount = new Discount();
		$discount->id = is_numeric($id) ? (int) $id : null;
		$discount->name = $this->stringParam('name', '');
		$discount->requireCouponCode = (bool) $this->request->getBodyParam('requireCouponCode');
		$discount->setCoupons(is_array($coupons) ? $coupons : []);
		$discount->enabled = (bool) $this->request->getBodyParam('enabled');
		$discount->stopProcessing = (bool) $this->request->getBodyParam('stopProcessing');
		$discount->type = $this->stringParam('type', 'advanced');
		$discount->taxBasis = is_string($taxBasis) ? TaxBasis::tryFrom($taxBasis) : null;
		$discount->setGlobalCartCondition(is_array($globalCartCondition) || is_string($globalCartCondition) ? $globalCartCondition : null);
		$discount->setPanels(is_array($panels) ? $panels : []);

		if (! Plugin::getInstance()->discounts->saveDiscount($discount)) {
			$this->setFailFlash(Craft::t('advanced-discounts', 'edit.flash.saveFailed'));
			Craft::$app->getUrlManager()->setRouteParams([
				'discount' => $discount,
			]);

			return null;
		}

		$this->setSuccessFlash(Craft::t('advanced-discounts', 'edit.flash.saved'));

		return $this->redirectToPostedUrl($discount);
	}

	private function intParam(string $name, int $default): int
	{
		$value = $this->request->getParam($name);

		return is_numeric($value) ? (int) $value : $default;
	}

	private function stringParam(string $name, string $default): string
	{
		$value = $this->request->getParam($name);

		return is_string($value) && $value !== '' ? $value : $default;
	}
}
