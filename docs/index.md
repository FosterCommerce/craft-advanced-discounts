# Getting Started

## Installation

To install the plugin, search for “Advanced Discounts” in the Craft Plugin Store and follow these instructions.

Or install via your terminal.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to require the plugin, and Craft to install it:

        composer require fostercommerce/advanced-discounts && php craft install/plugin bestsellers

Once installed you can access the Advanced Discounts setting page by clicking "Advanced Discounts" in the Control Panel sidebar.


## Plugin Settings
Access the plugin settings by going to Control Panel -> Settings and clicking on Advanced Discounts.

From here you can adjust how tax is applied to an order.
"After discounts" - tax will be applied to the total after the discounts have been calculated.
"Before discounts" - tax will be applied first and then discounts applied to the total including tax.

It is also possible to override the Tax Basis setting on a per-discount basis.

## Next Steps

- [Discounts](discounts.md)
- [Cart Conditions](cart-conditions.md)
- [Cart Actions](cart-actions.md)
- [Messages](messages.md)
- [Excluding Products](excluded-products.md)
- [Examples](examples.md)

