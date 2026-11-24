![Advanced Discounts](resources/img/header.png)

# Advanced Discounts

Build tiered, conditional, and buy-one-get-one discounts from cart rules.

## Overview

- Build tiers into one discount, each with its own cart rules and reward: 10% off at \$100, 15% at \$200, 20% at \$500.
- Show customers messages in your cart template, with live figures such as how much more to spend for the next tier.
- Run "buy one, get one free" promotions, where buying one product discounts another.
- Require a coupon code, with bulk code generation and a usage limit per code.
- Limit a discount to matching products or variants.
- Start a new discount from a preset: free items, free shipping, or a free order.
- Choose whether Commerce calculates tax before or after discounts, and whether a shipping discount lowers the shipping cost or shows as its own discount line.

## How it works

Create discounts under **Advanced Discounts -> Discounts**. Each time Commerce recalculates a cart, the plugin tests each enabled discount from the top of the list down, and adds order adjustments for each one that matches. Prices stay as they are. Commerce's own discounts still apply, and both can discount the same cart.

## Usage

Promotional messages appear where a template calls `getMessages()`:

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
