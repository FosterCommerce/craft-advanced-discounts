# Getting started

Build Craft Commerce discounts from cart rules, with tiers, coupon codes, and promotional messages.

This walks you from `composer require` to a discount applied to a cart. By the end, you know how a discount, its groups, and their conditions and actions fit together.

## Requirements

- Craft CMS `^5.0`
- Craft Commerce `^5.0`
- PHP `^8.2`

## 1. Install

```sh
composer require fostercommerce/advanced-discounts
./craft plugin/install advanced-discounts
```

With DDEV:

```sh
ddev composer require fostercommerce/advanced-discounts -w && ddev craft plugin/install advanced-discounts
```

The control panel sidebar now shows an **Advanced Discounts** item.

## 2. Configure

Go to **Settings -> Plugins -> Advanced Discounts**.

**Tax Basis** sets whether tax is calculated before or after the discount comes off. Default: **After discounts**. For what each option does and how a discount overrides it, see the [configuration reference](./reference/configuration.md).

## 3. Create a discount

Go to **Advanced Discounts -> Discounts** and click **New discount**.

1. Enter "Spend \$100, save 10%" as the **Name**.
2. Leave **Type** on **Advanced**.
3. In the first group, under **Cart Conditions**, click **Add a cart condition** and choose **Order**. Add an **Item Subtotal** rule set to **is greater than or equals** `100`.
4. Under **Cart Actions**, click **Add a cart action** and choose **Item Subtotal**. Choose **Discount a percentage** and enter `10`.
5. Click **Save discount**.

The discount now heads the list on **Advanced Discounts -> Discounts**.

## 4. Check the cart

Add \$100 or more of promotable products to a cart on your storefront. The cart total now includes a discount adjustment named "Spend \$100, save 10%". Below \$100, the cart has no adjustment from the plugin.

If the adjustment is missing, see [troubleshooting](./user-guide/troubleshooting.md).

## Where to go next

- [Discounts](./user-guide/discounts.md), structuring a discount into groups and tiers.
- [Cart conditions](./user-guide/cart-conditions.md), every rule a group can test.
- [Messages](./user-guide/messages.md), telling customers how close they are to a discount.
- [Recipes](./recipes/site-wide-coupon.md), complete examples, starting with a site-wide coupon.
