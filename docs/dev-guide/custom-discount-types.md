# Adding a discount type

Build a discount type of your own, alongside the **Advanced** and **Buy X, Get Y** types the plugin ships. A discount type decides which action rules a group offers and how those rules turn into order adjustments.

## The contract

Extend `fostercommerce\advanceddiscounts\base\DiscountType`, which implements `DiscountTypeInterface` and supplies the group rendering, adjustment building, and message resolution. Five static methods are yours to define:

| Method | Returns |
|---|---|
| `handle()` | Stored on the discount row. Must be stable, because existing discounts reference it. |
| `displayName()` | Label in the **Type** dropdown. |
| `actionConditionClass()` | The `ElementCondition` subclass listing the action rules a group offers. |
| `actionLabel()` | Field label above the action condition builder. |
| `actionInstructions()` | Instructions under that label. |

Override `getAdjustments(Order $order, Discount $discount): array` when your rules need adjustment logic the base class does not already cover. The base implementation walks the discount's groups, skips disabled ones, tests each group's cart condition, and builds adjustments for the `OrderCartActionRule`, `ShippingMethodCartActionRule`, `LineItemCartActionRule` and `BogoCartActionRule` rules it finds.

## Minimal example

A type offering only a shipping action:

```php
<?php

declare(strict_types=1);

namespace mymodule\discounttypes;

use Craft;
use fostercommerce\advanceddiscounts\base\DiscountType;

class FreightDiscountType extends DiscountType
{
    public static function handle(): string
    {
        return 'freight';
    }

    public static function displayName(): string
    {
        return Craft::t('my-module', 'Freight');
    }

    public static function actionConditionClass(): string
    {
        return FreightActionCondition::class;
    }

    public static function actionLabel(): string
    {
        return Craft::t('my-module', 'Freight Actions');
    }

    public static function actionInstructions(): string
    {
        return Craft::t('my-module', 'Applied to shipping when the rules above match.');
    }
}
```

The condition class listing the rules the group offers:

```php
<?php

declare(strict_types=1);

namespace mymodule\discounttypes;

use craft\elements\conditions\ElementCondition;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodCartActionRule;

class FreightActionCondition extends ElementCondition
{
    /**
     * @return array<int, class-string>
     */
    protected function selectableConditionRules(): array
    {
        return [
            ShippingMethodCartActionRule::class,
        ];
    }
}
```

## Register it

Listen for `EVENT_REGISTER_DISCOUNT_TYPES` in your module's `init()`:

```php
use craft\events\RegisterComponentTypesEvent;
use fostercommerce\advanceddiscounts\services\DiscountTypes;
use yii\base\Event;

Event::on(
    DiscountTypes::class,
    DiscountTypes::EVENT_REGISTER_DISCOUNT_TYPES,
    static function (RegisterComponentTypesEvent $event): void {
        $event->types[] = FreightDiscountType::class;
    }
);
```

The type appears in the **Type** dropdown on the discount edit screen. See [events](../reference/events.md) for the event payload.

## Message placeholders

`DiscountType::MESSAGE_PLACEHOLDERS` lists every token a message can use, and `messagePlaceholders()` filters it to the ones your type resolves. The base implementation exposes the Buy X, Get Y tokens only when `actionConditionClass()` is `BundleCondition`. Override `messagePlaceholders()` if your type resolves a different set, or the edit screen will offer tokens that render as empty strings.

## Removing a type

A discount whose stored `type` handle no longer resolves is skipped when discounts are read. It drops off the Discounts index, applies to no order, and logs a warning naming the handle. The row stays in the database, so registering the type again brings the discount back.

Migrate existing rows to another handle before you stop registering a type, or the discount stops applying with only that log entry to explain it.
