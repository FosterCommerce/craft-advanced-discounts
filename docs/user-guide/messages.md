# Messages

How to tell a customer they are close to, or have reached, a discount threshold.

Each message carries its own show-when rules, added under **Create rules to determine when to show this message**, that decide when it appears. Leave them empty and the message follows its group instead, appearing whenever the group's [Cart Conditions](cart-conditions.md) match.

Show-when rules replace that fallback rather than narrowing it, so a message can appear for a tier the customer has not reached yet. The discount still has to be enabled, have its coupon requirement met, and pass its Global Conditions.

## Tokens

Tokens in the message text are replaced with live cart figures when the message is shown. Drag a token chip into the message, or click it to insert it at the cursor.

| Token | Value |
|---|---|
| `{discountAmount}` | The first action's discount value in this group, as a percentage or an amount. |
| `{amountRemaining}` | Requires an Order cart condition set to "at least" or "more than" a money total. Renders the amount left to reach it, counting only discountable items. |
| `{quantityRemaining}` | Requires an Order or Line Items cart condition set to "at least" or "more than" a quantity. Renders the items left to reach it, counting only discountable items. |
| `{buyQuantityRemaining}` | Items left to add before the next Buy X, Get Y reward is earned. |
| `{discountedQuantity}` | How many items the Buy X, Get Y reward gives each time it is earned. |

See the [tiered sale recipe](../recipes/tiered-sale.md) for messages that use these tokens.

## Getting messages onto the storefront

The plugin does not output messages anywhere on its own. A developer has to add them to your cart and checkout templates. See [displaying promotional messages](../dev-guide/displaying-messages.md).
