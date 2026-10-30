# Troubleshooting

What to check when a discount does not do what you set it up to do, and how the plugin works alongside Commerce's own discounts.

## Why isn't my discount applying?

Work down this list.

**The variant is not promotable.** The plugin skips variants with Commerce's **Promotable** switch off. The [excluded variants](excluded-variants.md) page lists every variant in that state.

**A discount above it stopped processing.** The plugin evaluates discounts top down. When a discount with **Stop Processing Further Discounts** on applies, the plugin skips every discount below it. Check the order on **Advanced Discounts -> Discounts**, and either drag this discount higher or turn that switch off on the one above.

**The discount or the group is off.** The plugin skips a disabled discount, and a disabled group inside an enabled discount. A disabled group shows a gray dot on its bar. See [group controls](discounts.md#group-controls).

**The coupon is not on the cart, or it is used up.** A discount with **Require Coupon Code** on does not apply until the customer enters one of its codes. A code whose uses equal its **Max Uses** stops working. See [coupon codes](coupons.md).

**The conditions do not match.** Global Cart Conditions gate the whole discount; each group's Cart Conditions gate that group. Check the Date Range: **To** is inclusive. See [cart conditions](cart-conditions.md).

**The totals are lower than the cart looks.** Conditions test totals without non-promotable variants, so a cart can show more on screen than it counts for. Quantity conditions and Buy X, Get Y triggers skip those units too. See [excluded variants](excluded-variants.md).

**The action names products that are not in the cart.** A Line Items action set to **Selected line items** only discounts the products picked there. **Same line items as cart conditions** follows the group's conditions instead. See [cart actions](cart-actions.md).

**The discount type is no longer registered.** If a discount was saved with a type from a module that has since been removed, the discount is missing from the index and does not apply to orders. The log names the missing handle. See [adding a discount type](../dev-guide/custom-discount-types.md).

## How this works alongside Commerce's own discounts

The plugin evaluates every order, and Commerce's own discounts still apply. A Commerce discount and an Advanced Discounts discount can come off the same line.

**Stop Processing Further Discounts only stops this plugin's discounts.** Commerce's own discounts still apply.

**Both write adjustments of type `discount`.** Only this plugin's adjustments have `advancedDiscountId` in their snapshot, which is how a template tells them apart. See [reading discounts on the order](../dev-guide/reading-discounts-on-the-order.md).

**Coupon codes are separate sets.** See [coupon codes](coupons.md#what-the-customer-sees).

**Tax Basis applies to this plugin's discounts.** It sets whether tax is worked out before or after they come off. See the [configuration reference](../reference/configuration.md#tax-basis).
