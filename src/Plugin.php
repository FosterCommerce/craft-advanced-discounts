<?php

namespace fostercommerce\coupons;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\UrlManager;
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

    public static function config(): array
    {
        return [
            'components' => ['coupons' => Coupons::class],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
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
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)

        if (! Craft::$app->getRequest()->getIsConsoleRequest()) {
            if (Craft::$app->getRequest()->getIsCpRequest()) {
                $this->registerCpRoutes();
            }
        }
    }

    private function registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $registerUrlRulesEvent): void {
                $registerUrlRulesEvent->rules['coupons'] = 'coupons/manage/index';
                $registerUrlRulesEvent->rules['coupons/new'] = 'coupons/manage/edit';
                $registerUrlRulesEvent->rules['coupons/<id:\d+>'] = 'coupons/manage/edit';
            }
        );
    }
}
