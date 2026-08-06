# Cart Conditions

Cart Conditions determine when a discount is applied to the customer's cart. Audience: store admins setting up discounts in the control panel.

The same condition sets are available for Global Conditions and for the Cart Conditions inside a [Discount Panel](discounts.md#discount-panels).

## Date Range

Set a date range when this discount can be applied.

## Line Items

### Has Purchasable

Configure which items, and how many of each, must be in the customer's cart in order to meet the Condition.

You may set this condition up to look for one of multiple different products by clicking "+ OR" and adding another criterion.

*Hint: if you require a particular combination of products to trigger the discount, then use the "Buy X, Get Y" type*

### Related To

Trigger this discount if any of the Line Items in the cart are related to either the specified Entry or Category.

## Order

A condition based on the totals of the Order. Choose from the following:

- **Item subtotal**. The total value of line items before any adjustments are made.
- **Item total**. The total value of line items after any adjustments have been made.
- **Total**. The grand total (item subtotal + shipping + any discounts + tax). Could theoretically be a negative value.
- **Total price**. The grand total (item subtotal + shipping + any discounts + tax) based on the Store's minimum total price strategy (i.e., never below \$0 or never below shipping cost).
- **Total quantity**. The total number of items in the cart.

## User

Conditions based on the current User. Customers need to have an account and be logged in for these to apply.

If multiple conditions are set within a single Group, then all must match for the Discount Actions to be applied.

See [Cart Actions](cart-actions.md) for what happens when these conditions match.
