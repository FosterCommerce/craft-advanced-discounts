	# Installation

A Craft Commerce plugin for building multi-tier discounts with flexible rules and promotional messages.

## Requirements

- Craft CMS `^5.0`
- Craft Commerce `^5.0`
- PHP `^8.2`

## Install

From the Plugin Store, search for **Advanced Discounts** in **Settings -> Plugins** and press **Install**.

With Composer:

```sh
composer require fostercommerce/advanced-discounts
./craft plugin/install advanced-discounts
```

With DDEV:

```sh
ddev composer require fostercommerce/advanced-discounts -w && ddev craft plugin/install advanced-discounts
```

Once installed you can access the Advanced Discounts settings page by clicking "Advanced Discounts" in the Control Panel sidebar.

## Configure

Access the plugin settings by going to **Settings -> Plugins -> Advanced Discounts**.

**Tax Basis** controls when tax is calculated relative to the discount. Default: **After discounts**.

- **After discounts**: the discount comes off first, and tax is calculated on the reduced figure. The customer pays less tax on a discounted order.
- **Before discounts**: tax is calculated on the original prices, and the discount is applied afterwards. The cart keeps its original shipping cost and tax lines, with the discount shown against them.

Pick **Before discounts** when the cart or invoice has to show what an item and its tax would have cost, then the saving. Pick **After discounts** when the customer should only ever be taxed on what they actually pay.

### Overriding it per discount

Each discount has its own **Tax Basis** field on its edit page, set to **Use plugin default** until you change it.

The setting resolves per order, not per discount. If any enabled discount resolves to **Before discounts**, the whole order uses **Before discounts**, including the tax on discounts that came from elsewhere. A discount only has to be enabled and pass its coupon requirement to count; its conditions are not evaluated. Setting one discount to **Before discounts** therefore changes the tax basis on orders where that discount never applies.
