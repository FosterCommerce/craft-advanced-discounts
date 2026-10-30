# Messages

How to tell a customer they are close to, or have reached, a discount threshold.

Each message has its own show-when rules, added under **Create rules to determine when to show this message**. With no show-when rules, the message follows its group instead, and appears whenever the group's [Cart Conditions](cart-conditions.md) match.

Show-when rules replace that fallback rather than narrowing it, so a message can appear for a tier the customer has not reached yet. The discount still has to be enabled, have its coupon requirement met, and pass its Global Cart Conditions.

To show a message only between two amounts, add two show-when rules, each its own **Order** condition: for example, **Item Subtotal** **is greater than or equals** \$10,000, and **Item Subtotal** **is less than** \$15,000. Separate conditions must all match. Two rules inside one **Order** condition need only one match.

## Messages and the stop processing switches

**Stop Processing Further Groups** does not hide messages. A group skipped by a higher tier still shows its messages when their rules match. In a tiered sale, give each tier's messages an upper limit, so a cart at the top tier does not also show the lower tiers' messages.

**Stop Processing Further Discounts** hides the messages of the discounts below it, but only when its own discount adds an adjustment to the order.

## Messages for each site

On a multi-site install, each message can have its own text for each site, and a blank message uses the primary site's message. Tokens work the same in every site's message. For how site text works, see [text for each site](discounts.md#text-for-each-site).

## Tokens

The plugin replaces tokens in the message text with live cart figures. Drag a token chip into the message, or click it to insert it at the cursor.

| Token | Value |
|---|---|
| `{discountAmount}` | The first action's discount value in this group, as a percentage or an amount. |
| `{amountRemaining}` | Requires an Order cart condition set to "at least" or "more than" a money total. Renders the amount left to reach it, counting only discountable items. |
| `{quantityRemaining}` | Requires an Order or Line Items cart condition set to "at least" or "more than" a quantity. Renders the items left to reach it, counting only discountable items. |
| `{buyQuantityRemaining}` | Buy X, Get Y only. Items left to add before the next reward is earned. |
| `{discountedQuantity}` | Buy X, Get Y only. How many items the reward gives each time it is earned. |

A token the group cannot work out renders as an empty string. Saving an **Advanced** discount with a Buy X, Get Y token fails validation.

For messages that use these tokens, see the [tiered sale recipe](../recipes/tiered-sale.md).

## Getting messages onto the storefront

Messages appear only where a template calls `craft.advancedDiscounts.getMessages()` or `getMessage()`. See [displaying promotional messages](../dev-guide/displaying-messages.md).
