# Reading discounts on the order

Show a customer which discount reduced what, rather than a single lump sum.

```twig
{% set advancedAdjustments = cart.adjustments|filter(
  adjustment => adjustment.sourceSnapshot.advancedDiscountId ?? false
) %}
```

The plugin does not change prices. Every discount it applies is a Commerce [order adjustment](https://craftcms.com/docs/commerce/5.x/extend/adjusters.html), with a snapshot describing the rule that produced it.

## Finding this plugin's adjustments

Commerce's own discounts also use the adjustment type `discount`, so filtering on type alone mixes the two together. Every adjustment from this plugin has `advancedDiscountId` in its source snapshot. Commerce's own discount adjustments do not set it.

With the [Shipping Discounts](../reference/configuration.md#shipping-discounts) setting on **Reduce the shipping cost**, the default for new installs, a **Shipping** action writes a negative `shipping` adjustment instead. It lowers `totalShippingCost`, and `totalDiscount` leaves it out.

`cart.adjustments` returns every adjustment on the order, including those attached to line items. `lineItem.adjustments` returns only that line item's.

If this plugin's `discount` adjustments add up to less than `order.totalDiscount`, another discount, such as one of Commerce's own, applies to the order. To see every adjustment, drop the filter.

## Example

1. Loop over the line items and show each line's own adjustments from the plugin.
2. Show the order-level adjustments from the plugin, the ones without a `lineItemId`. Filter on type `discount`. Under **Reduce the shipping cost**, `order.totalShippingCost` already includes the shipping discount.

```twig
{% for lineItem in cart.lineItems %}
  <p>{{ lineItem.description }}: {{ lineItem.subtotal|commerceCurrency(cart.currency) }}</p>

  {% for adjustment in lineItem.adjustments|filter(
    adjustment => adjustment.sourceSnapshot.advancedDiscountId ?? false
  ) %}
    <p>
      {{ adjustment.name }}: {{ adjustment.amount|commerceCurrency(cart.currency) }}
      ({{ adjustment.sourceSnapshot.discountValue }}{{ adjustment.sourceSnapshot.discountType == 'percentage' ? '%' }})
    </p>
  {% endfor %}
{% endfor %}

{% for adjustment in cart.adjustments|filter(
  adjustment => (adjustment.sourceSnapshot.advancedDiscountId ?? false)
    and adjustment.lineItemId is null
    and adjustment.type == 'discount'
) %}
  <p>{{ adjustment.name }}: {{ adjustment.amount|commerceCurrency(cart.currency) }}</p>
{% endfor %}
```

## The snapshot

| Key | Value |
|---|---|
| `advancedDiscountId` | ID of the discount that produced the adjustment. |
| `rule` | Which cart action ran: `order`, `shipping`, `lineItem`, or `bogo`. |
| `discountType` | `flatAmount` or `percentage`. |
| `discountValue` | The number set on the action. A `percentage` of `10` means 10%. |
| `requireCouponCode` | Whether the discount is gated behind a code. |
| `couponCode` | The code the customer entered, or `null` when the discount is not gated. |
| `discountedPurchasableIds` | The purchasables the adjustment covers. Absent on `shipping`. |
| `applyPer` | Only on `lineItem`: the action's [Apply per](../user-guide/cart-actions.md#apply-per), `lineItem` or `purchasable`. Ignored for `percentage`. |

Two more fields come from the adjustment itself rather than the snapshot:

- `amount` is negative. Add it to a running total rather than subtracting it.
- `name` is the group's **Discount Name**. A group saved before that field was required can be blank, and then `name` is the discount's own name. When a group holds more than one cart action, each adjustment name ends with its rule label, such as `Summer Sale: Item Subtotal` and `Summer Sale: Shipping`.

## Where each rule attaches

`order` and `shipping` adjustments belong to the order. `lineItem` and `bogo` adjustments belong to a line item and set `lineItemId`. They appear in both `cart.adjustments` and that line item's own `adjustments`.

A per-line breakdown reads `lineItem.adjustments`. A cart summary reads the order-level ones, as in the example.

## Showing the coupon that was used

`couponCode` is set only on adjustments from a discount with **Require Coupon Code** on. On an ungated sale it is `null`:

```twig
{% for adjustment in advancedAdjustments|filter(adjustment => adjustment.sourceSnapshot.couponCode) %}
  <p>Code {{ adjustment.sourceSnapshot.couponCode }}: {{ adjustment.name }}</p>
{% endfor %}
```

An order has one coupon code at most, so every gated adjustment on an order repeats the same value.

## Tax

With **Before discounts** [Tax Basis](../reference/configuration.md#tax-basis), the plugin adds its adjustments after Commerce calculates tax.
