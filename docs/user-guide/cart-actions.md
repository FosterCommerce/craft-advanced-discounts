# Cart actions

Actions set what a discount group discounts when its [Cart Conditions](cart-conditions.md) match.

An **Advanced** discount offers three actions. A **Buy X, Get Y** discount offers one, covered under [Buy X, Get Y](#buy-x-get-y).

Every action skips line items whose variants are not promotable. See [excluded variants](excluded-variants.md).

A discount value can't be negative, and a percentage can't be more than 100.

When several discounts apply, each one takes at most what is left of the line, the item subtotal, or shipping, after the discounts before it. With **Before discounts** [Tax Basis](../reference/configuration.md#tax-basis), Commerce's own discounts apply first and count too. The plugin does not take a line or shipping below zero.

## Item Subtotal

Discounts the cart as a whole, by a flat amount or a percentage.

The discount is worked out from the item subtotal of promotable line items, leaving out shipping and tax. If no line item is promotable, Item Subtotal does not add an adjustment.

Item Subtotal adds one adjustment to the order, not to a line. Per-line breakdowns in your templates do not include this adjustment.

## Line Items

Discounts individual lines, by a flat amount or a percentage. Each matching line gets its own adjustment, shown against the product it came off.

### Apply to

- **All line items**. Every promotable line in the cart.
- **Selected line items**. Only the products or variants you pick here.
- **Same line items as cart conditions**. The variants matched by this group's **Quantity** rules, whether matched by product or by relation. Quantity rules in Global Cart Conditions do not count.

With **Same line items as cart conditions**, you name the products once. Changing the condition later changes what the action discounts.

### Apply per

Only affects flat amounts. A percentage always works off the line's value.

- **Per line item**. Takes the amount off the line once, whatever the quantity.
- **Per purchasable**. Takes the amount off each unit, so \$5 off a line of 3 takes off \$15.

A flat amount never takes a line below zero. With \$20 off a \$12 line, the customer gets \$12 off.

## Shipping

Discounts what the customer is charged for shipping, by a flat amount or a percentage of the total shipping cost.

Choose which shipping methods qualify: **Any**, or a specific set with **is one of** and **is not one of**. If the order's shipping is already free, the action does not add an adjustment.

## Using more than one action

A group can hold several actions, and each one adds its own adjustment. When there is more than one, each adjustment is named after the group and the action that made it. A group called "Summer Sale" adds `Summer Sale: Item Subtotal` and `Summer Sale: Shipping` to the order.

Combining **Item Subtotal** with **Line Items** discounts the same products twice, once as part of the cart total and again individually.

## Buy X, Get Y

The **Buy X, Get Y** discount type replaces the three actions with a single one. The customer buys a quantity of the products you choose and gets a quantity of the same or other products at a discount.

- **Apply repeatedly** sets whether the reward can be earned more than once in a cart. On, a customer buying six of a "buy 2" trigger earns the reward three times. Off, they earn it once, however many they buy.
- When the trigger product and the discounted product are the same, the customer needs both quantities added together. "Buy 2, get 1" of one variant needs 3 in the cart before the reward is earned.
- Both quantities must be at least 1.
- If several units qualify for the reward, the plugin discounts the cheapest.
- The plugin only discounts reward units already in the cart. It does not add them.

The discount defaults to 100%, for a "get one free" offer. Lower it for a "half price" style offer.
