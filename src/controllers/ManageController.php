<?php

namespace fostercommerce\advanceddiscounts\controllers;

use Craft;
use craft\helpers\Json;
use craft\i18n\Locale;
use craft\web\Controller;
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
			'Code',
			'Created',
			'Updated',
			'No discounts yet.',
			'Discounts reordered.',
			"Couldn't reorder discounts.",
		]);

		$discounts = Plugin::getInstance()->discounts->getAllDiscounts();
		$tableData = array_map(function (Discount $discount): array {
			$row = $discount->toArray();
			$row['url'] = "advanced-discounts/{$discount->id}";
			$row['title'] = $discount->name;
			$row['dateCreated'] = Craft::$app->getFormatter()
				->asDate($row['dateCreated'], Locale::LENGTH_SHORT);
			$row['dateUpdated'] = Craft::$app->getFormatter()
				->asDate($row['dateUpdated'], Locale::LENGTH_SHORT);

			return $row;
		}, $discounts);

		return $this->renderTemplate('advanced-discounts/index', [
			'tableData' => $tableData,
		]);
	}

	public function actionEdit(?int $id = null): Response
	{
		$discount = Craft::$app->getUrlManager()->getRouteParams()['discount']
			?? ($id !== null ? Plugin::getInstance()->discounts->getDiscountById($id) : new Discount());

		return $this->renderTemplate('advanced-discounts/edit', [
			'discount' => $discount,
			'isNewDiscount' => $discount->id === null,
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
		$discount->code = $this->request->getBodyParam('code') ?: null;
		$discount->enabled = (bool) $this->request->getBodyParam('enabled');
		$discount->stopProcessing = (bool) $this->request->getBodyParam('stopProcessing');
		$discount->setCartCondition($this->request->getBodyParam('cartCondition'));
		$discount->setCartActionCondition($this->request->getBodyParam('cartActionCondition'));
		$discount->setMessageCondition($this->request->getBodyParam('messageCondition'));

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
