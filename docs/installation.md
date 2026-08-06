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

From here you can adjust how tax is applied to an order.

- **After discounts**: tax is applied to the total after the discounts have been calculated. This is the default.
- **Before discounts**: tax is applied first and then discounts are applied to the total including tax.

It is also possible to override the Tax Basis setting on a per-discount basis.
