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
