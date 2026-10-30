# Advanced Discounts documentation

Build Craft Commerce discounts from cart rules, with tiers, coupon codes, and promotional messages.

## Where to go

**Getting started:** [a walkthrough](./getting-started.md) from install to your first discount on a cart.

**User guide:**

- [Discounts](./user-guide/discounts.md), creating a discount and structuring it into groups
- [Cart conditions](./user-guide/cart-conditions.md), the rules that decide when a discount applies
- [Cart actions](./user-guide/cart-actions.md), what a group discounts once its rules match
- [Coupon codes](./user-guide/coupons.md), gating a discount behind codes, added by hand or generated
- [Messages](./user-guide/messages.md), promotional copy for customers, with live figures
- [Excluded variants](./user-guide/excluded-variants.md), the variants every discount skips
- [Troubleshooting](./user-guide/troubleshooting.md), why a discount isn't applying, and how it works alongside Commerce's own discounts

**Dev guide:**

- [Displaying promotional messages](./dev-guide/displaying-messages.md), rendering discount messages in Twig
- [Reading discounts on the order](./dev-guide/reading-discounts-on-the-order.md), breaking a total discount down into the rules that produced it
- [Adding a discount type](./dev-guide/custom-discount-types.md), registering a type with its own action rules

**Reference:**

- [Configuration](./reference/configuration.md), the plugin settings and their defaults
- [Events](./reference/events.md), the events you can listen for
- [Permissions](./reference/permissions.md), the Commerce permissions each screen and action requires

**Recipes:**

- [Site-wide coupon](./recipes/site-wide-coupon.md), 10% off everything with a coupon code
- [Timed product discount](./recipes/timed-product-discount.md), 5% off a set of products for one week
- [Buy one, get one free](./recipes/buy-one-get-one-free.md), a Buy X, Get Y promotion
- [Tiered sale](./recipes/tiered-sale.md), a bigger discount for a bigger cart, with messages

[Roadmap](./roadmap.md): ideas under consideration.
