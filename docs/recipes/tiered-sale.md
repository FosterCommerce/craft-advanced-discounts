# Recipe: tiered sale

Imagine you want to run a Sale through July and August that gives varying tiers of discount depending on how much the customer has in their cart. For example,

- If they spend $2500 they get 5% discount
- If they spend $7000 they get 7% discount
- If they spend $10000 they get a 10% discount
- If they spend $15000 they get a 12% discount

This is possible by setting up multiple groups within your discount. Each group can have its own conditions, actions, and messaging.

Start by creating a new discount and calling it "Summer Sale".

We want the sale to run through July and August, so set the Global Cart Condition accordingly.

![Setting the date range](../../resources/img/demo-multi-tier-global-cart-conditions.png)

Next, set the Type to Advanced.

Set up the Groups. Note that groups are processed first to last, so in order to create this multi-tier sale, we want to start with the top tier and work down to the minimum discount. If any of the group conditions match then we stop processing the remaining groups.

Our first group will be for the 12% discount. Set a title for the group to reflect the level of discount.

![Setting the first group title](../../resources/img/demo-multi-tier-first-group-title.png)

Set the Cart Conditions that will match this tier and trigger its Actions. If the customer's cart value is $15000 or more, then they match this group.

![Set up the cart conditions for the group](../../resources/img/demo-multi-tier-group-cart-conditions.png)

When they match, give the customer 12% off their cart value.

![Set up the cart actions for this group](../../resources/img/demo-multi-tier-group-actions.png)

We want to let the customer know that they have qualified for the 12% discount, so set up some messaging in the group.

![Show the customer a message indicating their discount](../../resources/img/demo-multi-tier-group-qualifying-message.png)

The qualifying message is straightforward enough, but what if we also want to show the customer a message showing how far they are from attaining the discount? No problem, let's create a second message in the same group.

This time, we will make use of the placeholder tokens to dynamically insert information into the message. See [Messages](../user-guide/messages.md#tokens) for the full token list.

![Using tokens for dynamic message content](../../resources/img/demo-multi-tier-dynamic-message.png)

Note that the message is showing that the customer has reached the 10% discount and how far they are from reaching 12%.
Let's set some rules for this message that will trigger its display. We want to show it if the customer has $10000 or more in their cart.

![Setting the rules for the dynamic message](../../resources/img/demo-multi-tier-dynamic-message-rules.png)

You can add as many messages as you like in the group.

We want to stop processing groups in this discount if this group is applied, so turn on "Stop processing further groups".

The entire group should now look like this.

![The completed first group](../../resources/img/demo-multi-tier-first-group.png)

We can now set up additional groups within this discount to apply the conditions, actions, and messages for the remaining tiers.

When done, the whole thing looks like this.

![Sale with multiple tiers of discount](../../resources/img/multi-tier-sale.png)
