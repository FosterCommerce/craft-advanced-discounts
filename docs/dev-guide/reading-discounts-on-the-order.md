# Reading discounts on the order

Show a customer which discount reduced what, rather than a single lump sum.

Advanced Discounts never rewrites prices. Every discount it applies is a Commerce order adjustment, so the order keeps its original subtotal, shipping cost, and tax lines, and the discounts sit alongside them. Each adjustment carries a snapshot describing the rule that produced it.

## Finding this plugin's adjustments

Commerce's own discounts also use the adjustment type `discount`, so filtering on type alone mixes the two systems together. Every adjustment from this plugin carries `advancedDiscountId` in its source snapshot, and nothing else does:

```twig
{% set advancedAdjustments = cart.adjustments|filter(
  adjustment => adjustment.sourceSnapshot.advancedDiscountId ?? false
) %}
```

`cart.adjustments` returns every adjustment on the order, including those attached to line items. `lineItem.adjustments` returns only that line item's.

If the filtered adjustments do not add up to the order's total discount, a Commerce discount is applying alongside this plugin's, and the filter is leaving it out. Drop the filter to see everything the order carries.

## The snapshot

| Key | Value |
|---|---|
| `advancedDiscountId` | ID of the discount that produced the adjustment. |
| `rule` | Which cart action ran: `order`, `shipping`, `lineItem`, or `bogo`. |
| `discountType` | `flatAmount` or `percentage`. |
| `discountValue` | The number configured on the action. A `percentage` of `10` means 10%. |
| `requireCouponCode` | Whether the discount is gated behind a code. |
| `couponCode` | The code the customer entered, or `null` when the discount is not gated. |
| `discountedPurchasableIds` | The purchasables the adjustment covers. Absent on `shipping`. |

Two more fields come from the adjustment itself rather than the snapshot:

- `amount` is negative. It is a reduction, so add it to a running total rather than subtracting it.
- `name` is the Discount Name from the group, falling back to the discount's own name. When a group holds more than one cart action, each adjustment is suffixed with its rule label, giving `Summer Sale: Item Subtotal` and `Summer Sale: Shipping`.

## Where each rule attaches

`order` and `shipping` adjustments belong to the order. `lineItem` and `bogo` adjustments belong to a line item and set `lineItemId`, so they appear in both `cart.adjustments` and that line item's own `adjustments`.

That split decides where you render them. A per-line breakdown reads `lineItem.adjustments`; a cart summary reads the order-level ones:

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
  adjustment => (adjustment.sourceSnapshot.advancedDiscountId ?? false) and adjustment.lineItemId is null
) %}
  <p>{{ adjustment.name }}: {{ adjustment.amount|commerceCurrency(cart.currency) }}</p>
{% endfor %}
```

## Showing the coupon that was used

`couponCode` is populated only on adjustments from a discount with **Require Coupon Code** switched on. On an ungated sale it is `null`, because no code was involved:

```twig
{% for adjustment in advancedAdjustments|filter(adjustment => adjustment.sourceSnapshot.couponCode) %}
  <p>Code {{ adjustment.sourceSnapshot.couponCode }}: {{ adjustment.name }}</p>
{% endfor %}
```

An order carries one coupon code at most, so every gated adjustment on a given order repeats the same value.

## Tax

Whether the discount lands before or after tax is the **Tax Basis** setting, not something you control from the template. It changes which adjuster runs, and therefore where the discount sits in the order's adjustment stack. See [Installation](../installation.md#configure).
