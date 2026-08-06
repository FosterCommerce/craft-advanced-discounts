# Advanced Discounts documentation

Build multi-tier Craft Commerce discounts with flexible rules and promotional messages, alongside Commerce's own discounts rather than in place of them.

## Where to go

**First time here?** Start with [Installation](./installation.md), then follow a [recipe](./recipes/site-wide-coupon.md) end to end.

**Running promotions day-to-day?** See the [user guide](./user-guide/):

- [Discounts](./user-guide/discounts.md), creating a discount, coupon codes, discount types, and Discount Panels.
- [Coupon Codes](./user-guide/coupons.md), gating a discount behind a code, in bulk or one at a time.
- [Cart Conditions](./user-guide/cart-conditions.md), the rules that decide when a discount applies.
- [Cart Actions](./user-guide/cart-actions.md), what happens once those rules match.
- [Messages](./user-guide/messages.md), promotional copy shown to the customer, with dynamic tokens.
- [Excluded Variants](./user-guide/excluded-variants.md), which variants no discount can touch, and why.

**Building on top of the plugin?** See the [developer guide](./dev-guide/):

- [Displaying promotional messages](./dev-guide/displaying-messages.md), the Twig API for rendering discount messages in your storefront.
- [Reading discounts on the order](./dev-guide/reading-discounts-on-the-order.md), breaking a total discount down into the rules that produced it.

**Want a worked example?** See the [recipes](./recipes/):

- [Site-wide coupon](./recipes/site-wide-coupon.md), 10% off everything behind a coupon code.
- [Timed product discount](./recipes/timed-product-discount.md), 5% off a group of products for one week.
- [Buy one, get one free](./recipes/buy-one-get-one-free.md), a Buy X, Get Y promotion.
- [Tiered sale](./recipes/tiered-sale.md), spend more, save more, with dynamic messaging.

**Setup details:** [Installation](./installation.md).
