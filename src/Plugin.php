<?php

namespace fostercommerce\coupons;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\commerce\elements\Order;
use craft\commerce\services\OrderAdjustments;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use fostercommerce\coupons\adjusters\CouponAdjuster;
use fostercommerce\coupons\models\Settings;
use fostercommerce\coupons\services\Coupons;
use yii\base\Event;

/**
 * coupons plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @property-read Coupons $coupons
 */
class Plugin extends BasePlugin
{
	public bool $hasCpSection = true;

	public bool $hasCpSettings = true;

	public string $schemaVersion = '1.0.0';

	/**
	 * @return array<string, mixed>
	 */
	public static function config(): array
	{
		return [
			'components' => [
				'coupons' => Coupons::class,
			],
		];
	}

	public function init(): void
	{
		parent::init();

		// Defer most setup tasks until Craft is fully initialized
		Craft::$app->onInit(function () {
			$this->attachEventHandlers();
			// ...
		});
	}

	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->view->renderTemplate('coupons/_settings.twig', [
			'plugin' => $this,
			'settings' => $this->getSettings(),
		]);
	}

	private function attachEventHandlers(): void
	{
		if (! Craft::$app->getRequest()->getIsConsoleRequest()) {
			if (Craft::$app->getRequest()->getIsCpRequest()) {
				$this->registerCpRoutes();
			}
		}

		// register adjuster
		Event::on(
			OrderAdjustments::class,
			OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS,
			static function (RegisterComponentTypesEvent $event): void {
				$event->types[] = CouponAdjuster::class;
			}
		);

		// Prevent Commerce from clearing our custom coupon codes during order validation.
		// Commerce's validateCouponCode() nulls $order->couponCode if not found in its own
		// discount system, but only when recalculationMode is ALL or ADJUSTMENTS_ONLY.
		// We temporarily set mode to NONE before validation and restore it after, so the
		// code survives into recalculate() where our adjuster can read it.
		$savedModes = [];

		Event::on(
			Order::class,
			'beforeValidate',
			function (Event $event) use (&$savedModes): void {
				/** @var Order $order */
				$order = $event->sender;
				if (! $order->couponCode) {
					return;
				}
				$coupon = Plugin::getInstance()->coupons->getCouponByCode($order->couponCode);
				if ($coupon === null || ! $coupon->enabled) {
					return;
				}
				$id = spl_object_id($order);
				$savedModes[$id] = $order->recalculationMode;
				$order->recalculationMode = Order::RECALCULATION_MODE_NONE;
			}
		);

		Event::on(
			Order::class,
			'afterValidate',
			function (Event $event) use (&$savedModes): void {
				/** @var Order $order */
				$order = $event->sender;
				$id = spl_object_id($order);
				if (! isset($savedModes[$id])) {
					return;
				}
				$order->recalculationMode = $savedModes[$id];
				unset($savedModes[$id]);
			}
		);
	}

	private function registerCpRoutes(): void
	{
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			static function (RegisterUrlRulesEvent $registerUrlRulesEvent): void {
				$registerUrlRulesEvent->rules['coupons'] = 'coupons/manage/index';
				$registerUrlRulesEvent->rules['coupons/new'] = 'coupons/manage/edit';
				$registerUrlRulesEvent->rules['coupons/<id:\d+>'] = 'coupons/manage/edit';
			}
		);
	}
}
