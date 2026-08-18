# Troubleshooting

What to check when a discount does not do what you set it up to do, and how the plugin behaves alongside Commerce's own discounts.

## Why isn't my discount applying?

Work down this list. The first few causes account for most cases.

**The variant is not promotable.** Commerce's **Promotable** switch takes a variant out of every discount, this plugin's included. The [Excluded Variants](excluded-variants.md) page lists every variant in that state.

**A discount above it ended the run.** Discounts are processed top down, and one with **Stop Processing Further Discounts** on ends the run as soon as it applies. Check the order on **Advanced Discounts -> Discounts**, and either drag this discount higher or turn that switch off on the one above.

**The discount or the group is off.** A disabled discount is skipped, and so is a disabled group inside an enabled discount. A disabled group shows a gray dot on its bar. See [Group controls](discounts.md#group-controls).

**The coupon is not on the cart, or it is used up.** A discount with **Require Coupon Code** on does nothing until the customer enters one of its codes, and a code that has hit its **Max Uses** stops working. See [Coupon Codes](coupons.md).

**The conditions do not match.** Global Conditions gate the whole discount; each group's Cart Conditions gate that group. A Date Range that has already ended is the usual cause. See [Cart Conditions](cart-conditions.md).

**The totals are lower than the cart looks.** Excluded stock is removed from the figures conditions test against, so a cart can show more on screen than it counts for. Quantity conditions and Buy X, Get Y triggers skip those units too. See [Excluded Variants](excluded-variants.md).

**The action names products that are not in the cart.** A Line Items action set to **Selected line items** only discounts the products picked there. **Same line items as cart conditions** follows the group's conditions instead. See [Cart Actions](cart-actions.md).

**The discount type is no longer registered.** A discount saved with a type from a module that has since been removed drops off the index and applies to nothing. The log names the missing handle. See [Adding a discount type](../dev-guide/custom-discount-types.md).

## How this works alongside Commerce's own discounts

Both systems evaluate every order, and both can apply to the same cart. A Commerce discount and an Advanced Discounts discount can come off the same line.

**Stop Processing Further Discounts only stops this plugin's discounts.** Commerce's own discounts still apply after it.

**Both write adjustments of type `discount`.** Only this plugin's carry `advancedDiscountId`, which is how a template tells them apart. See [Reading discounts on the order](../dev-guide/reading-discounts-on-the-order.md).

**Coupon codes are separate sets.** A code this plugin does not recognize is left to Commerce, including Commerce's own handling of an invalid one. Using the same code in both systems applies both discounts to the order. See [Coupon Codes](coupons.md).

**Tax Basis applies to this plugin's discounts.** It decides whether tax is worked out before or after they come off. See [Installation](../installation.md#configure).
