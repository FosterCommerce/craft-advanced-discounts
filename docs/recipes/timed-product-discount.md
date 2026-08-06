# Recipe: discounting a few specific products for a week

Assume that you want to provide a discount for a group of products to encourage sales. No coupon code is required. Anybody purchasing those items will get them at a 5% discounted price, and you want to run the promotion for a week starting on Monday. Using Advanced Discounts, you are able to configure a discount to do just this.

Start by creating a new discount and give it a name. For the purposes of this demonstration, we shall call it "Shear Bar Week".

You want the discount to run from August 1 to August 8, so set the Global Cart Conditions appropriately.

![Setting the date range](../../resources/img/demo-global-date-range.png)

In the discount group, add a name, "5% off all Shear Bars".

Set up the Cart Conditions. In this case we want to define some products that will qualify for the discount. So select "Line Items", then click "OR" to show the options available and choose "Has Purchasable".

![Choose which products qualify for the discount](../../resources/img/demo-selecting-products.png)

Now we can choose which products will get the discount. Click "Choose" and select a product you want to include.
You have the option to also specify a quantity, so if you only wanted to apply the discount if someone purchased 2 or more of that item, you can set it here.

If you want to include more than one product, click "OR" and select another product.

Keep going until you have added all the products included in the discount.

![Has purchasable conditions](../../resources/img/demo-has-purchasable-condition.png)

Moving on to the Cart Actions, select "Line Items", "Same line items as cart conditions", "Discount a percentage", and set it to 5.

To let the customer know they are getting the discount, add a Message.

![Basic message set up](../../resources/img/demo-basic-message.png)
