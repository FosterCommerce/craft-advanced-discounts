# Events

Events the plugin fires, and what to do with them.

## DiscountTypes::EVENT_REGISTER_DISCOUNT_TYPES

**Fires:** when the plugin builds the list of available discount types, on every call to `DiscountTypes::getAllDiscountTypes()`.

**Payload:** `craft\events\RegisterComponentTypesEvent`

| Property | Type | Notes |
|---|---|---|
| `types` | `array<int, class-string>` | Pre-populated with `AdvancedDiscountType` and `BuyXGetYDiscountType`. Append your own class, which must implement `DiscountTypeInterface`. |

**Listen:**

```php
use craft\events\RegisterComponentTypesEvent;
use fostercommerce\advanceddiscounts\services\DiscountTypes;
use yii\base\Event;

Event::on(
    DiscountTypes::class,
    DiscountTypes::EVENT_REGISTER_DISCOUNT_TYPES,
    static function (RegisterComponentTypesEvent $event): void {
        $event->types[] = MyDiscountType::class;
    }
);
```

**Common use:** adding a discount type with its own action rules, such as a tiered freight promotion. See [custom discount types](../dev-guide/custom-discount-types.md) for the class you register here.
