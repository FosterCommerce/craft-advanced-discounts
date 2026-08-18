# Cart Conditions

Cart Conditions decide when a discount applies to the cart. The same sets are offered for a discount's Global Conditions and for the Cart Conditions inside each [discount group](discounts.md#discount-groups).

Every condition you add has to match. The exception is criteria joined with **+ OR** inside one Line Items or Order condition, where any one of them matching is enough.

## Date Range

Set a date range when this discount can be applied.

## Line Items

### Has Purchasable

Which items, and how many of each, must be in the cart.

Leave **Quantity** blank to match any number of them, so long as the item is there. Click **+ OR** to accept another product instead.

To require a particular combination of products rather than any one of them, use the **Buy X, Get Y** discount type.

### Related To

Trigger this discount if any of the Line Items in the cart are related to the specified Entry or Category. A relation on the product counts, as well as one on the variant.

## Order

A condition based on the totals of the Order. Choose from the following:

- **Item subtotal**. The total value of line items before any adjustments are made.
- **Item total**. The total value of line items after any adjustments have been made.
- **Total**. Item subtotal plus shipping, discounts, and tax. Can be negative.
- **Total price**. The same figure, floored by the store's minimum total price strategy, such as never below \$0 or never below shipping cost.
- **Total quantity**. The total number of items in the cart.

## User

Conditions based on the customer on the order. They need an account and have to be logged in for these to apply.

See [Cart Actions](cart-actions.md) for what happens when these conditions match.
