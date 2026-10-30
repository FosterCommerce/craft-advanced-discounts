![Advanced Discounts](resources/img/header.png)

# Advanced Discounts

A Craft CMS plugin that builds multi-tier Craft Commerce **discounts** from flexible cart rules.

## Overview

- Applies a discount when the customer's cart matches rules you set on date, line items, order totals, or the customer on the order.
- Stacks multiple tiers inside one discount, each with its own rules and reward (10% off at \$100, 15% at \$200, 20% at \$500).
- Discounts an item subtotal, specific line items, or shipping, by a flat amount or a percentage.
- Runs "Buy One Get One Free" style promotions where one product in the cart discounts another.
- Shows the customer promotional messages that fill in live figures, such as how much more they need to spend to reach the next tier.
- Gates a discount behind a coupon code, with bulk code generation and a per-code usage limit.
- Controls whether tax is calculated before or after discounts, for the whole store or per discount.

## How it works

Create discounts under **Advanced Discounts -> Discounts**. Each time Commerce recalculates a cart, the plugin tests each enabled discount from the top of the list down, and adds an order adjustment for each one that matches. Prices stay as they are. Commerce's own discounts still apply, and both can discount the same cart.

## Usage

Promotional messages appear where a template asks for them:

```twig
{% for discountMessage in craft.advancedDiscounts.getMessages(cart) %}
  <p>{{ discountMessage }}</p>
{% endfor %}
```

This prints each message whose rules match the cart, with figures such as `{amountRemaining}` filled in.

## Requirements

- Craft CMS `^5.0`
- Craft Commerce `^5.0`
- PHP `^8.2`

## Install

```sh
composer require fostercommerce/advanced-discounts
./craft plugin/install advanced-discounts
```

## Documentation

- [Getting started](https://www.fostercommerce.com/craft-cms-plugins/advanced-discounts/docs/getting-started), from install to your first discount on a cart
- [Discounts](https://www.fostercommerce.com/craft-cms-plugins/advanced-discounts/docs/user-guide/discounts), structuring a discount into groups and tiers
- [Cart conditions](https://www.fostercommerce.com/craft-cms-plugins/advanced-discounts/docs/user-guide/cart-conditions), the rules that decide when a discount applies
- [Displaying promotional messages](https://www.fostercommerce.com/craft-cms-plugins/advanced-discounts/docs/dev-guide/displaying-messages), rendering messages in Twig
- [Troubleshooting](https://www.fostercommerce.com/craft-cms-plugins/advanced-discounts/docs/user-guide/troubleshooting), why a discount isn't applying

## License

Proprietary.

---

<a href="https://www.fostercommerce.com" target="_blank"><img src="./resources/img/foster-commerce.svg" alt="Foster Commerce" width="160" height="40"></a>
