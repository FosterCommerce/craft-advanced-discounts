# Getting started

Build tiered, conditional, and buy-one-get-one discounts from cart rules.

This page walks you from `composer require` to a discount applied to a cart. By the end, you know how a discount, its groups, and their conditions and actions fit together.

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

**Tax Basis** and **Shipping Discounts** keep their defaults for this walkthrough. For what each controls, see the [configuration reference](./reference/configuration.md).

## 3. Create a discount

Go to **Advanced Discounts -> Discounts** and click **New discount**.

1. Enter "Spend \$100, save 10%" as the **Name**.
2. Leave **Type** on **Advanced**.
3. In the first group, enter "10% off" as the **Discount Name**.
4. Under **Cart Conditions**, click **Add a cart condition** and choose **Order**. Set its **Item Subtotal** rule to **is greater than or equals** `100`.
5. Under **Cart Actions**, click **Add a cart action** and choose **Line Items**. Choose **Discount a percentage** and enter `10`.
6. Click **Save discount**.

The discount now heads the list on **Advanced Discounts -> Discounts**.

## 4. Check the cart

Add \$100 or more of promotable products to a cart on your storefront. Each line now has a discount adjustment named "10% off". Below \$100, the cart has no adjustment from the plugin.

If the adjustment is missing, see [troubleshooting](./user-guide/troubleshooting.md).

## Where to go next

- [Discounts](./user-guide/discounts.md), structuring a discount into groups and tiers.
- [Cart conditions](./user-guide/cart-conditions.md), every rule a group can test.
- [Messages](./user-guide/messages.md), telling customers how close they are to a discount.
- [Recipes](./recipes/site-wide-coupon.md), complete examples, starting with a site-wide coupon.
