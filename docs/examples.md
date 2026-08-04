# Examples

## Coupon to get 10% off all products
Goal: Create coupon that gives 10% off all products.

Go to Advanced Discounts -> Discounts and Create a new Discount

First, give your Discount a name, say "Site-wide coupon"

We only want the discount to be applied if the customer enters a coupon code. We are only publishing one code that anyone can use. So let's turn on "Require Coupon Code".

Click "Add a coupon" and enter the code we want to use in the Code field. Let's say it's going to be BIG10 and it has unlimited uses.

![alt text](images/demo-coupon-big10.png "Setting up the coupon code")

We don't want to allow any other discounts to be applied if the customer has entered this coupon code. So let's turn on "Stop Processing Further Discounts".  You can find this on the top right of the screen.

![alt text](images/demo-stop-processing-coupon.png "Stop processing switch")

We don't need to apply any restrictions to the sale so we can also ignore the Global Cart Conditions.

Next, select the type of Discount that will be applied. In this case it is "Advanced", that is the default.

Now we can set up the rules and actions.
First, give the Discount a name - this will be shown in the checkout when the coupon has been applied.

There are no special conditions to be applied, other than the coupon code being entered. So skip past the Cart Conditions and head right to Cart Actions.

We want to provide a 10% discount to all items in the customer's cart. So in Cart Actions, select "Line Items". 

We then have the option to choose which items get the discount. In our case it is all of them, so leave that set to "All line items".

We want to apply a discount of 10% of the item's price, so select "Discount a percentage" and enter 10. Then leave the last setting to "Per line item".

![alt text](images/demo-coupon-big10-actions.png "Setting the Cart Actions")

There is no need to show any other message in the cart and checkout. The customer will see that they have the coupon applied. We can skip the Messages section.

Here's what the finished set up should look like.

![alt text](images/demo-big10-coupon-setup.png "Set up for a 10% off coupon")

Once done, click on "Save Discount" to return to the list of configured Advanced Discounts.

Since we don't want to allow any other discounts to be applied, make sure that this discount is at the top of the list so it will be processed first.

![alt text](images/site-wide-coupon-at-top.png "Site wide coupon at top of list")

Now during checkout your customer can enter the code "BIG10" and they will receive 10% off the cost of items in their order.


## Discounting a few specific products for a week

Assume that you want to provide a discount for a group of products to encourage sales. No coupon code is required. Anybody purchasing those items will get them at a 5% discounted price and you want to run the promotion for a week starting on Monday. Using Advanced Discounts you are able to configure a discount to do just this.

Start by creating a new discount and give it a name. For the purposes of this demonstration we shall call it "Shear Bar Week"

You want the discount to run from August 1 to August 8 so set the Global Cart Conditions appropriately.

![alt text](images/demo-global-date-range.png "Setting the date range")

In the discount group, add a name, "5% off all Shear Bars"

Set up the Cart Conditions. In this case we want to define some products that will qualify for the discount. So select "Line Items", then click "OR" to show the options available and choose "Has Purchasable".

![alt text](images/demo-selecting-products.png "Choose which products qualify for the discount")

Now we can choose which products will get the discount. Click "Choose" and select a product you want to include.
You have the option to also specify a quantity, so if you only wanted to apply the discount if someone purchased 2 or more of that item you can set it here.

If you want to include more than one product, click "OR" and select another product.

*Scope for improvement here, allow the user to select more than 1 product in the same "has purchasable" condition*

Keep going until you have added all the products included in the discount.

![alt text](images/demo-has-purchasable-condition.png "Has purchasable conditions")


Moving on to the Cart Actions, select "Line Items", "Same line items as cart conditions", "Discount a percentage" and set it to 5.

To let the customer know they are getting the discount, add a Message.

![alt text](images/demo-basic-message.png "Basic message set up")



## Buy One, Get One Free
When a customer purchases a product we want to give them another product for free. In our case we want to give a 100% discount on a Left Hand mixer knife when purchasing a Right hand mixer knife.

Create a new discount, give it a name and set the Type to "Buy X, Get Y".

We want to allow customers to get further discounts, so we can leave "Stop Processing Further Discounts" set to "off".

In the panel give the group a name.

Next, choose the product that will trigger this discount.

Then choose the product which will be discounted.

Set the discount percentage, in our case 100% since we want it to be a free item.

We want to offer this discount for every RH Mixer knife. If the customer buys 4 then they get 4 Left hand mixer knives at the discounted price.

![alt text](images/demo-bogof-conditions.png "Setting the trigger and discounted products")

Add any messages you wish to show to the customer when they qualify for the discount.

![alt text](images/demo-bogof-messages.png "Messages and conditions to show to the customer in the checkout")

## Tiered sale
