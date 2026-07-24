<?php

namespace fostercommerce\advanceddiscounts;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\models\OrderNotice;
use craft\commerce\services\OrderAdjustments;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use fostercommerce\advanceddiscounts\adjusters\AfterTaxDiscountAdjuster;
use fostercommerce\advanceddiscounts\adjusters\DiscountAdjuster;
use fostercommerce\advanceddiscounts\enums\TaxBasis;
use fostercommerce\advanceddiscounts\models\Settings;
use fostercommerce\advanceddiscounts\services\AdvancedDiscountsService;
use fostercommerce\advanceddiscounts\services\Coupons;
use fostercommerce\advanceddiscounts\services\Discounts;
use fostercommerce\advanceddiscounts\services\DiscountTypes;
use fostercommerce\advanceddiscounts\variables\AdvancedDiscountsVariable;
use yii\base\Event;

/**
 * Advanced Discounts plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @property-read Discounts $discounts
 * @property-read DiscountTypes $discountTypes
 * @property-read Coupons $coupons
 */
class Plugin extends BasePlugin
{
	public bool $hasCpSection = true;

	public bool $hasCpSettings = true;

	public string $schemaVersion = '1.0.6';

	/**
	 * @return array<string, mixed>
	 */
	public static function config(): array
	{
		return [
			'components' => [
				'discounts' => Discounts::class,
				'discountTypes' => DiscountTypes::class,
				'coupons' => Coupons::class,
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

	/**
	 * @return array<string, mixed>|null
	 */
	public function getCpNavItem(): ?array
	{
		$navItem = parent::getCpNavItem();
		if ($navItem === null) {
			return null;
		}

		$navItem['subnav'] = [
			'discounts' => [
				'label' => Craft::t('advanced-discounts', 'Discounts'),
				'url' => 'advanced-discounts',
			],
			'excluded-variants' => [
				'label' => Craft::t('advanced-discounts', 'Excluded Variants'),
				'url' => 'advanced-discounts/excluded-variants',
			],
		];

		return $navItem;
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
			'taxBasisOptions' => TaxBasis::options(),
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

		// register adjusters
		Event::on(
			OrderAdjustments::class,
			OrderAdjustments::EVENT_REGISTER_DISCOUNT_ADJUSTERS,
			static function (RegisterComponentTypesEvent $event): void {
				$event->types[] = DiscountAdjuster::class;
			}
		);

		Event::on(
			OrderAdjustments::class,
			OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS,
			static function (RegisterComponentTypesEvent $event): void {
				$event->types[] = AfterTaxDiscountAdjuster::class;
			}
		);

		// Prevent Commerce from clearing our custom discount codes during order validation.
		// Commerce's validateCouponCode() nulls $order->couponCode if not found in its own
		// discount system, but only when recalculationMode is ALL or ADJUSTMENTS_ONLY.
		// We temporarily set mode to NONE before validation and restore it after, so the
		// code survives into recalculate() where our adjuster can read it.
		$savedModes = [];

		Event::on(
			Order::class,
			Order::EVENT_BEFORE_VALIDATE,
			function (Event $event) use (&$savedModes): void {
				/** @var Order $order */
				$order = $event->sender;
				if (! $order->couponCode) {
					return;
				}
				$discount = Plugin::getInstance()->discounts->getDiscountByCode($order->couponCode);
				if ($discount === null) {
					return;
				}

				if (! $discount->enabled) {
					$this->removeCouponCode($order, Craft::t('advanced-discounts', 'This coupon is no longer available.'));
					return;
				}

				if (! $discount->matchesCouponCode($order->couponCode)) {
					$this->removeCouponCode($order, Craft::t('advanced-discounts', 'This coupon has reached its usage limit.'));
					return;
				}

				$id = spl_object_id($order);
				$savedModes[$id] = $order->recalculationMode;
				$order->recalculationMode = Order::RECALCULATION_MODE_NONE;
			}
		);

		Event::on(
			Order::class,
			Order::EVENT_AFTER_VALIDATE,
			function (Event $event) use (&$savedModes): void {
				/** @var Order $order */
				$order = $event->sender;
				$id = spl_object_id($order);
				if (! isset($savedModes[$id])) {
					return;
				}
				$order->recalculationMode = $savedModes[$id];
				unset($savedModes[$id]);
				$order->recalculate();
			}
		);

		Event::on(
			Order::class,
			Order::EVENT_AFTER_COMPLETE_ORDER,
			static function (Event $event): void {
				/** @var Order $order */
				$order = $event->sender;

				/** @var OrderAdjustment[] $adjustments */
				$adjustments = $order->getAdjustments();

				$appliedDiscountIds = [];
				foreach ($adjustments as $adjustment) {
					$discountId = $adjustment->sourceSnapshot['advancedDiscountId'] ?? null;
					if ($discountId !== null) {
						$appliedDiscountIds[$discountId] = true;
					}
				}

				if ($appliedDiscountIds === []) {
					return;
				}

				$discountIds = array_keys($appliedDiscountIds);
				Plugin::getInstance()->discounts->incrementUses($discountIds);

				if ($order->couponCode) {
					Plugin::getInstance()->coupons->incrementUses($order->couponCode, $discountIds);
				}
			}
		);
	}

	private function removeCouponCode(Order $order, string $message): void
	{
		$order->couponCode = null;
		$order->addNotice(Craft::createObject([
			'class' => OrderNotice::class,
			'attributes' => [
				'type' => 'invalidCouponRemoved',
				'attribute' => 'couponCode',
				'message' => $message,
			],
		]));
	}

	private function registerCpRoutes(): void
	{
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			static function (RegisterUrlRulesEvent $registerUrlRulesEvent): void {
				$registerUrlRulesEvent->rules['advanced-discounts'] = 'advanced-discounts/manage/index';
				$registerUrlRulesEvent->rules['advanced-discounts/excluded-variants'] = 'advanced-discounts/manage/excluded-variants';
				$registerUrlRulesEvent->rules['advanced-discounts/new'] = 'advanced-discounts/manage/edit';
				$registerUrlRulesEvent->rules['advanced-discounts/panel'] = 'advanced-discounts/manage/panel';
				$registerUrlRulesEvent->rules['advanced-discounts/type-settings'] = 'advanced-discounts/manage/type-settings';
				$registerUrlRulesEvent->rules['advanced-discounts/generate-coupons'] = 'advanced-discounts/manage/generate-coupons';
				$registerUrlRulesEvent->rules['advanced-discounts/<id:\d+>'] = 'advanced-discounts/manage/edit';
			}
		);
	}
}
