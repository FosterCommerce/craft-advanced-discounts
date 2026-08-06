# Cart Actions

Actions define what happens when the [Cart Conditions](cart-conditions.md) in a Discount Panel are matched.

An **Advanced** discount offers three actions. A **Buy X, Get Y** discount offers one, covered at the bottom of this page.

Every action skips line items whose variants are not promotable. See [Excluded Variants](excluded-variants.md).

## Item Subtotal

Discounts the cart as a whole, by a flat amount or a percentage.

The figure it works from is the promotable item subtotal, which is the sum of the line items that can be discounted. It ignores shipping and tax. A cart with nothing promotable in it produces no discount at all.

This produces one adjustment against the order rather than against any particular line, so a per-line breakdown in your templates will not show it.

## Line Items

Discounts individual lines, by a flat amount or a percentage. Each matching line gets its own adjustment, so the discount is visible against the product it came off.

### Apply to

- **All line items**. Every promotable line in the cart.
- **Selected line items**. Only the products or variants you pick here.
- **Same line items as cart conditions**. The products already named in this panel's Cart Conditions.

The third option exists so you do not have to name the same products twice. Set up a condition on "Has Purchasable" or "Related To", and the action follows whatever that condition matched. Changing the condition later changes the action with it.

### Apply per

Only affects flat amounts. A percentage is always a percentage of the line's value, however this is set.

- **Per line item**. Takes the amount off the line once, no matter the quantity.
- **Per purchasable**. Takes the amount off each unit, so \$5 off a line of 3 removes \$15.

A flat amount never takes a line below zero. Ask for \$20 off a \$12 line and the customer gets \$12 off.

## Shipping

Discounts what the customer is charged for shipping, by a flat amount or a percentage of the total shipping cost.

Choose which shipping methods qualify: **Any**, or a specific set with **is one of** and **is not one of**. An order whose shipping is already free produces no discount.

## Using more than one action

A panel can hold several actions, and each one produces its own adjustment. When there is more than one, each adjustment is named after the panel and the action that made it, so a panel called "Summer Sale" produces `Summer Sale: Item Subtotal` and `Summer Sale: Shipping` on the order.

Combining **Item Subtotal** with **Line Items** discounts the same products twice, once as part of the cart total and again individually.

## Buy X, Get Y

Selecting the **Buy X, Get Y** discount type replaces the three actions above with a single one: the customer buys a quantity of one product and gets a quantity of another at a discount.

- **Apply repeatedly** decides whether the reward can be earned more than once in a cart. On, a customer buying six of a "buy 2" trigger earns the reward three times. Off, they earn it once, no matter how many they buy.
- When the trigger product and the discounted product are the same, the quantity the customer needs is both numbers added together. "Buy 2, get 1" of one variant needs 3 in the cart before the reward is earned.
- If several units qualify for the reward, the cheapest are the ones discounted.

The discount defaults to 100%, which is the usual "get one free" case. Lower it for a "half price" style offer.
