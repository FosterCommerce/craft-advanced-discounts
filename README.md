# Advanced Discounts

Bring more power to your discounts and sales.
Easily set up Buy X, Get Y type discounts.

## Requirements

This plugin requires;
- Craft CMS 5.0 or later
- Commerce 5.0 or later

## Setup

### Installation

To install the plugin, search for “Advanced Discounts” in the Craft Plugin Store follow these instructions.

Or install via your terminal.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to require the plugin, and Craft to install it:

        composer require fostercommerce/advanced-discounts && php craft install/plugin bestsellers



## Getting Started
Access the Advanced Discounts setting page by clicking "Advanced Discounts" in the Control Panel sidebar.


## Plugin Settings
Access the plugin settings from the Control Panel->Settings and clicking on Advanced Discounts

From here you can adjust how tax is applied to an order.
"After discounts" - tax will be applied to the total after the discounts have been calculated.
"Before discounts" - tax will be applied first and then discounts applied to the total including tax.

It is also possible to override the Tax Basis setting on a per-discount basis.

## Discounts

This is the place for creating and managing all of your discounts. The list is searchable and sortable.

![alt text](docs/images/discounts-index.png "The Discounts index")

You can configure different types of discount and build out rules for when they will be applied and what happens when those rules are met.

Each Discount has its own sets of Conditions and you can create multiple sets of Conditions with each set triggering different Actions. For example, you may wish to create a multi-tiered Sale where the customer gets a different amount off their order depending on how much they spend - 10% off if they spend \$100, 15% off if they spend \$200, and 20% off when they spend \$500. You can create all of these within the same Discount.

Each tier can also have its own Messages that will be displayed to the customer when the matching criteria are met. Each Message has its very own conditions, allowing you to define multiple Messages per tier. For example, you could show the customer that they have qualified for 10% off and another to show far they are from reaching the 15% discount.

![alt text](docs/images/multi-tier-sale.png "Example of a multi-tiered sale discount")

## Creating a Discount
To create a new discount click the "New Discount" button on the to right of the Discounts list page.

The order in which the Discounts are arranged matters. Conditions are processed from the top down and you have the opportunity to stop processing once a Discount is matched, or indeed to continue and apply further discounts should you wish.

### Discount Name
Give your discount a name, this will be shown in the cart when the customer has the discount applied.

### Coupon Codes
If you want to only apply the discount when the customer enters a coupon code, switch that on and add the code or codes that you wish to use.

![alt text](docs/images/coupon-code-creation.png "Coupon code creation")

If you need to create a number of random codes for the same discount then click the "Generate" button

![alt text](docs/images/generate-coupon-codes.png "Generating coupon codes")

Enter the number of coupons you would like and the format for the codes. Any "#" characters will be replaced by random letters and numbers. When you are ready, click "Generate" and the codes will be created.

![alt text](docs/images/generated-coupon-codes.png "List of generated coupon codes")

Next to each code you are able to see the number of times that the code has been used. Whenever a code is used this number is incremented. You can set a maximum number of times that each code can be used too. If set then the code will no longer work once the maximum uses is reached.


### Discount Type

#### Advanced
This is the core for most Discounts and the one you will 

#### Buy X, Get Y
Should you need to create a "Buy One Get One Free" type promotion then this is what you need.

Set which product or combination of products will trigger the promotion then set which products get discounted when they are added to the cart.

### Conditions
Conditions determine when your discount will be applied to the customer's cart. 

There are two types of conditions, "Global Conditions" and "Cart Conditions". A discount can have multiple sets of "Cart Conditions" which can each trigger different "Cart Actions" but only one set of "Global Conditions". 

#### Global Conditions
Global Conditions allow you to set rules that will apply over and above any individual cart rules. For example, you may wish to only have the discount active during the first week of August.

Choose from the following Condition Sets:

##### Date Range
Criteria based on the date.

##### Line Items
Criteria based on the Order Line Items.

##### Order
Criteria based on the Order totals.

##### User
Criteria based on the User.

Global Conditions are optional and multiple Sets can be defined if you wish. So you can be sure that your discount will only ever be applied when you want it to be.

### Cart Conditions
Cart Conditions are applied only if the Global Conditions for the discount have been met.

You can create multiple groups of Cart Conditions with each group having its own Cart Actions. These are displayed in Discount Panels.


## Discount Panels
Each group of Conditions, Actions, and Messages are shown together in a Discount Panel. 

![alt text](docs/images/discount-panel-empty.png "Discount Panel")

Set the Discount Name field to display it in the cart when the customer cart matches the conditions for this panel.

Cart Conditions allow you to set the rules that apply the discount defined in this panel. Avaialble options are the same as with the Global Conditions.

Cart Actions define what happens when the Conditions in this panel are matched.

Messages are your opportunity to tell the customer if they are close to or if they have reached the threshold to get the discount.

If you want to prevent any further groups within the same discount from being processed, then switch on "Stop Processing Further Groups".

The panels are collapsible to reduce visual clutter should you need to build out a particularly complex discount.

![alt text](docs/images/discount-panel-collapsed.png "Collapsed Discount Panel")

### Cart Conditions
You can set up multiple Cart Conditions within a single Group. Each Condition in the Group must be matched to obtain the Discount.

---
# Cart Conditions


### Date Range
Set a date range when this discount can be applied. 

### Line Items
#### Has Purchasable
Configure which items, and how many of each, must be in the customer's cart in order to meet the Condition.

You may set this condition up to look for one of multiple different products by clicking "+ OR" and adding another criteria.

*Hint: if you require a particular combination of products to trigger the discount, then use the "Buy X, Get Y" type*

#### Related To
Trigger this discount if any of the Line Items in the cart are related to either the specified Entry or Category

### Order
A condition based on the totals of the Order. Choose from 

- **Item subtotal**. The total value of line items before any adjustments are made.
- **Item total**. The total value of line items after any adjustments have been made.
- **Total**. The grand total (item subtotal + shipping + any discounts + tax). Could theoretically be a negative value.
- **Total price**. The grand total (item subtotal + shipping + any discounts + tax) based on the Store's minimum total price strategy. i.e. never below \$0 or never below shipping cost.
- **Total quantity**. The total number of items in the cart.

### User
Conditions based on the current User. Customers need to have an account and be logged in for these to apply.

If multiple conditions are set within a single Group then all must match for the Discount Actions to be applied.

## Cart Actions
When the necessary Conditions are met, this section defines what will happen.

**Item Subtotal** Adjust the total value of the items in the cart either by a flat amount or percentage of their value.

**Line Items** Adjust the total value of specific line items in the cart. Again, either by a flat amount or a percentage.

**Shipping** Adjust the amount being charged for shipping based on the shipping method. Again, either by a flat amount or a percentage.

Multiple Cart Actions can be added for each Group.

## Promotional Messages
Use Messages to display notifications to the customer.
Each Message has its own set of Conditions that will control when the message is displayed. 

If configured within a Group, the Group Cart Conditions must be matched before any Message Conditions are matched.

Messages can contain a number of special tokens that will display dynamic values. Build your message by adding these tokens in the message text. They will be replaced with appropriate values when the message is displayed.

**{discountAmount}** - the amount of money being discounted
**{amountRemaining}** - the amount remaining to trigger the discount
**{quantityRemaining}** - the number of items remaining to trigger the discount
**{buyQuantityRemaining}** - 
**{discountedQuantity}** - 

## Excluded Products
