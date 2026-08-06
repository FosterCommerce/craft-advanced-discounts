# Advanced Discounts

A Craft CMS plugin that builds multi-tier Craft Commerce **discounts** from flexible cart rules.

## Overview

- Applies a discount when the customer's cart matches rules you set on date, line items, order totals, or the logged-in user.
- Stacks multiple tiers inside one discount, each with its own rules and reward (10% off at \$100, 15% at \$200, 20% at \$500).
- Discounts an item subtotal, specific line items, or shipping, by a flat amount or a percentage.
- Runs "Buy One Get One Free" style promotions where one product in the cart discounts another.
- Shows the customer promotional messages that fill in live figures, such as how much more they need to spend to reach the next tier.
- Gates a discount behind a coupon code, with bulk code generation and a per-code usage limit.
- Controls whether tax is calculated before or after discounts, for the whole store or per discount.

## Requirements

- Craft CMS `^5.0`
- Craft Commerce `^5.0`
- PHP `^8.2`

## Install

```sh
composer require fostercommerce/advanced-discounts
./craft plugin/install advanced-discounts
```

See [`docs/installation.md`](./docs/installation.md) for the full installation and configuration guide.

## Discounts

Discounts are created and ordered in **Advanced Discounts -> Discounts**. Order matters: discounts are processed top down, and any discount can stop processing once it matches. Each discount holds one optional set of Global Conditions, plus any number of Discount Panels, each pairing its own cart rules with the actions and messages they trigger.

See [`docs/user-guide/discounts.md`](./docs/user-guide/discounts.md).

## Conditions and actions

Conditions match on date range, line items (a specific purchasable, a quantity of it, or a relation to an entry or category), order totals, and the current user. When a panel's conditions match, its actions adjust the item subtotal, chosen line items, or shipping, by a flat amount or a percentage.

See [`docs/user-guide/cart-conditions.md`](./docs/user-guide/cart-conditions.md) and [`docs/user-guide/cart-actions.md`](./docs/user-guide/cart-actions.md).

## Promotional messages

Each panel can carry messages with their own display conditions, so you can tell a customer both that they have earned a discount and how far they are from the next one. Messages support tokens (`{discountAmount}`, `{amountRemaining}`, `{quantityRemaining}`) that resolve against the live cart.

See [`docs/user-guide/messages.md`](./docs/user-guide/messages.md).

## Documentation

[Read the full documentation](./docs/index.md), including worked [recipes](./docs/recipes/site-wide-coupon.md) for coupons, timed sales, BOGOF, and tiered discounts.

## License

Proprietary.

## Credits

Brought to you by [Foster Commerce](https://fostercommerce.com).
