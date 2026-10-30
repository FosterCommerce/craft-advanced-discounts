# Displaying promotional messages

Fetch the messages a store admin has set up on a discount, with their tokens filled in from the cart. The plugin does not render messages; they appear only where a template calls one of these methods. For how messages are written, see [messages](../user-guide/messages.md).

```twig
{% set discountMessages = craft.advancedDiscounts.getMessages(cart) %}
```

Both methods take a Commerce order, either the cart or a completed order, and evaluate every discount against it when called.

| Call | Returns |
|---|---|
| `craft.advancedDiscounts.getMessages(order)` | An array of message strings, in display order. Empty when no message qualifies. |
| `craft.advancedDiscounts.getMessage(order)` | The first string from that array, or `null`. |

Strings come back with their [tokens](../user-guide/messages.md#tokens) already replaced. `{amountRemaining}` and `{discountAmount}` are formatted in the order's payment currency.

## Example

1. Get the cart.
2. Fetch its messages once, into a variable.
3. Render the list when it is not empty.

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

For a single message, such as a "you are close to the next tier" prompt:

```twig
{% set cart = craft.commerce.carts.cart %}
{% set discountMessage = craft.advancedDiscounts.getMessage(cart) %}

{% if discountMessage %}
  <p class="discount-message">{{ discountMessage }}</p>
{% endif %}
```

Each call evaluates every discount again. To show the messages in two places on one page, assign them to a variable once, as in the example.

## What qualifies a message

A message is in the array when all of the following hold.

1. Its discount is enabled.
2. Its discount's coupon requirement is met. A discount with **Require Coupon Code** off always passes. A discount with it on needs a matching code with uses left on `order.couponCode`.
3. The discount's **Global Cart Conditions** match.
4. Its group is enabled.
5. The message text is not empty.
6. Its own show-when rules match. A message without show-when rules follows its group's **Cart Conditions**.

Steps 3 and 6 test totals without non-promotable line items, the same as the discount itself. A cart made up mostly of non-promotable variants might not meet a threshold its on-screen total suggests.

## Ordering

`getMessages()` returns discounts in their order on **Advanced Discounts -> Discounts**, then groups top to bottom within each discount, then messages in the order they were added to the group. `getMessage()` returns the first of these: the topmost message of the topmost matching group of the highest discount.

To change which single message comes first, reorder the discounts on the index or the groups inside the discount. When more than one message could qualify at once, give them show-when rules that do not overlap.

The stop processing switches affect messages differently from adjustments. See [messages and the stop processing switches](../user-guide/messages.md#messages-and-the-stop-processing-switches).
