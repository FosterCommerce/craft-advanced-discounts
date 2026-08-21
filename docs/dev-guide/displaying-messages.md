# Displaying promotional messages

Render the messages a store admin has configured on a discount. The plugin outputs nothing on its own, so nothing appears on the storefront until you add one of the calls below to a template. See [Messages](../user-guide/messages.md) for how they are authored.

## The contract

The plugin registers an `advancedDiscounts` Twig variable with two methods. Both take a Commerce order (the cart, or a completed order) and evaluate every discount against it at the moment they are called.

| Call | Returns |
|---|---|
| `craft.advancedDiscounts.getMessages(order)` | An array of resolved message strings, in display order. Empty when nothing qualifies. |
| `craft.advancedDiscounts.getMessage(order)` | The first string from that array, or `null`. |

Strings come back with their [tokens](../user-guide/messages.md#tokens) already replaced. `{amountRemaining}` and `{discountAmount}` are formatted in the order's payment currency.

## Minimal example

Both calls take an order, so pass the cart:

```twig
{% set cart = craft.commerce.carts.cart %}

{% set discountMessages = craft.advancedDiscounts.getMessages(cart) %}

{% if discountMessages %}
  <ul class="discount-messages">
    {% for discountMessage in discountMessages %}
      <li>{{ discountMessage }}</li>
    {% endfor %}
  </ul>
{% endif %}
```

For a single message, usually the "you are close to the next tier" prompt:

```twig
{% set cart = craft.commerce.carts.cart %}
{% set discountMessage = craft.advancedDiscounts.getMessage(cart) %}

{% if discountMessage %}
  <p class="discount-message">{{ discountMessage }}</p>
{% endif %}
```

Each call re-evaluates every discount. If you need the messages in two places on one page, assign them to a variable once, as above, rather than calling twice.

## What qualifies a message

A message string appears in the array when all of the following hold.

1. Its discount is enabled.
2. Its discount's coupon requirement is satisfied. A discount with **Require Coupon Code** off always passes; one with it on needs a matching, unexhausted code on `order.couponCode`.
3. The discount's **Global Cart Conditions** match.
4. Its group is enabled.
5. The message text is not empty.
6. Its own show-when rules match. A message with no show-when rules falls back to its group's **Cart Conditions**, so it appears whenever the group applies.

Non-promotable line items are subtracted from the order's totals before steps 3 and 6 are evaluated, the same as they are for the discount itself. A cart whose value is mostly non-promotable stock will not hit a threshold you would expect it to hit from the cart total on screen.

## Ordering

`getMessages()` returns discounts in the order they appear on **Advanced Discounts -> Discounts**, then groups top to bottom within each discount, then messages in the order they were added to the group. `getMessage()` returns index 0 of that array, so it resolves to the topmost message of the topmost matching group of the highest-priority discount.

To change which single message wins, reorder the discounts on the index page or the groups inside the discount. Give messages non-overlapping show-when rules when more than one could qualify at once.

Two behaviors explain most unexpected messages:

- **Stop Processing Further Discounts** suppresses the messages of later discounts only when the discount holding the switch actually produced an adjustment. A discount that matches its conditions but discounts nothing does not stop the ones below it.
- **Stop Processing Further Groups** does not apply to messages at all. It stops a lower tier's *adjustment*, but that tier's messages are still collected. A tiered sale can therefore return the qualifying message for a tier whose discount was skipped. Use show-when rules on those messages if that is not what you want.
