# Messages

Messages are your opportunity to tell the customer if they are close to or if they have reached the threshold to get the discount.

Use Messages to display notifications to the customer. Each Message has its own set of Conditions that will control when the message is displayed.

If configured within a Group, the Group [Cart Conditions](cart-conditions.md) must be matched before any Message Conditions are matched.

## Tokens

Messages can contain a number of special tokens that will display dynamic values. Build your message by adding these tokens in the message text. They will be replaced with appropriate values when the message is displayed.

| Token | Value |
|---|---|
| `{discountAmount}` | The amount of money being discounted. |
| `{amountRemaining}` | The amount remaining to trigger the discount. |
| `{quantityRemaining}` | The number of items remaining to trigger the discount. |
| `{buyQuantityRemaining}` | The number of "buy" items left before the next Buy X, Get Y reward is earned. |
| `{discountedQuantity}` | The number of units currently discounted for the Buy X, Get Y reward. |

See the [tiered sale recipe](../recipes/tiered-sale.md) for messages that use these tokens.

## Getting messages onto the storefront

The plugin does not output messages anywhere on its own. A developer has to add them to your cart and checkout templates. See [displaying promotional messages](../dev-guide/displaying-messages.md).
