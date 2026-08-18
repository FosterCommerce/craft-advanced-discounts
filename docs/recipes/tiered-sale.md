# Recipe: tiered sale

Goal: a sale running through July and August where the discount grows with the cart value.

- Spend \$2,500, get 5% off
- Spend \$7,000, get 7% off
- Spend \$10,000, get 10% off
- Spend \$15,000, get 12% off

Each tier is one group inside a single discount, with its own conditions, actions, and messages.

Create a new discount and name it "Summer Sale".

Set a Global Cart Condition covering July and August.

![Setting the date range](../../resources/img/demo-multi-tier-global-cart-conditions.png)

Set Type to **Advanced**.

Groups are processed top to bottom, and each tier stops the ones below it, so build the highest tier first and work down.

Start with the 12% tier. Give the group a Discount Name that names the tier.

![Setting the first group title](../../resources/img/demo-multi-tier-first-group-title.png)

Set the Cart Conditions for the tier: an item subtotal of \$15,000 or more.

![Set up the cart conditions for the group](../../resources/img/demo-multi-tier-group-cart-conditions.png)

Add a Cart Action giving 12% off.

![Set up the cart actions for this group](../../resources/img/demo-multi-tier-group-actions.png)

Add a message telling the customer they have qualified for 12% off.

![Show the customer a message indicating their discount](../../resources/img/demo-multi-tier-group-qualifying-message.png)

Add a second message in the same group to show how far the customer is from the tier. Use the placeholder tokens to fill in the live figures. See [Messages](../user-guide/messages.md#tokens) for the full list.

![Using tokens for dynamic message content](../../resources/img/demo-multi-tier-dynamic-message.png)

Give that second message its own show-when rule so it only appears below the threshold: an item subtotal of \$10,000 or more.

![Setting the rules for the dynamic message](../../resources/img/demo-multi-tier-dynamic-message-rules.png)

A group takes as many messages as you need.

Turn on **Stop Processing Further Groups** so a cart that reaches this tier does not also pick up the lower ones.

![The completed first group](../../resources/img/demo-multi-tier-first-group.png)

Add a group per remaining tier, in descending order.

![Sale with multiple tiers of discount](../../resources/img/multi-tier-sale.png)

## Where to go next

- [Messages](../user-guide/messages.md), the full token list and how show-when rules work.
- [Displaying promotional messages](../dev-guide/displaying-messages.md), rendering the messages on the storefront.
