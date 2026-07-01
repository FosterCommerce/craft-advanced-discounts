<?php

namespace fostercommerce\advanceddiscounts;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\commerce\elements\Order;
use craft\commerce\services\OrderAdjustments;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use fostercommerce\advanceddiscounts\adjusters\DiscountAdjuster;
use fostercommerce\advanceddiscounts\models\Settings;
use fostercommerce\advanceddiscounts\services\AdvancedDiscountsService;
use fostercommerce\advanceddiscounts\services\Discounts;
use fostercommerce\advanceddiscounts\variables\AdvancedDiscountsVariable;
use yii\base\Event;

/**
 * Advanced Discounts plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @property-read Discounts $coupons
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
				'coupons' => Discounts::class,
			],
		];
	}

	public function init(): void
	{
		parent::init();

		// Defer most setup tasks until Craft is fully initialized
		Craft::$app->onInit(function () {
			\craft\commerce\Plugin::getInstance()?->set('discounts', [
				'class' => AdvancedDiscountsService::class,
			]);
			$this->attachEventHandlers();
		});
	}

	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->view->renderTemplate('advanced-discounts/_settings.twig', [
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

		// register Twig variable
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_DEFINE_BEHAVIORS,
			static function (Event $event): void {
				/** @var CraftVariable $variable */
				$variable = $event->sender;
				$variable->set('advancedDiscounts', AdvancedDiscountsVariable::class);
			}
		);

		// register adjuster
		Event::on(
			OrderAdjustments::class,
			OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS,
			static function (RegisterComponentTypesEvent $event): void {
				$event->types[] = DiscountAdjuster::class;
			}
		);

		// Commerce's validateCouponCode() nulls $order->couponCode if it can't find the
		// code in its own discount table. We save the code before validation and restore
		// it afterwards if Commerce cleared it, without touching recalculationMode so that
		// Commerce's normal recalculation flow runs uninterrupted.
		$savedCodes = [];

		Event::on(
			Order::class,
			Order::EVENT_BEFORE_VALIDATE,
			function (Event $event) use (&$savedCodes): void {
				/** @var Order $order */
				$order = $event->sender;
				if (! $order->couponCode) {
					return;
				}
				$coupon = Plugin::getInstance()->coupons->getCouponByCode($order->couponCode);
				if ($coupon === null || ! $coupon->enabled) {
					return;
				}
				$savedCodes[spl_object_id($order)] = $order->couponCode;
			}
		);

		Event::on(
			Order::class,
			Order::EVENT_AFTER_VALIDATE,
			function (Event $event) use (&$savedCodes): void {
				/** @var Order $order */
				$order = $event->sender;
				$id = spl_object_id($order);
				if (! isset($savedCodes[$id])) {
					return;
				}
				$savedCode = $savedCodes[$id];
				unset($savedCodes[$id]);

				if (! $order->couponCode) {
					$order->couponCode = $savedCode;
				}

				$order->clearErrors('couponCode');
			}
		);
	}

	private function registerCpRoutes(): void
	{
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			static function (RegisterUrlRulesEvent $registerUrlRulesEvent): void {
				$registerUrlRulesEvent->rules['advanced-discounts'] = 'advanced-discounts/manage/index';
				$registerUrlRulesEvent->rules['advanced-discounts/new'] = 'advanced-discounts/manage/edit';
				$registerUrlRulesEvent->rules['advanced-discounts/<id:\d+>'] = 'advanced-discounts/manage/edit';
			}
		);
	}
}
