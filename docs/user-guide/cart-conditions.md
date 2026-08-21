# Cart Conditions

Cart Conditions decide when a discount applies to the cart. The same sets are offered for a discount's Global Conditions and for the Cart Conditions inside each [discount group](discounts.md#discount-groups).

Every condition you add has to match. The exception is criteria joined with **+ OR** inside one Line Items or Order condition, where any one of them matching is enough.

## Date Range

Set a date range when this discount can be applied.

## Line Items

### Quantity

Which items, and how many of each, must be in the cart. The rule reads as a sentence: "at least 3 of Product Variant Grass Chute Liner", or "at least 1 related to Entry Fall Campaign".

Compare the quantity with **at least**, **at most**, **more than**, **fewer than**, **exactly**, or **other than**.

Then choose how the items are identified. **of** counts the line items for a specific product or variant. **related to** counts the line items whose product or variant is related to an Entry or Category. A relation on the product counts for every variant under it.

The quantity starts at 1, so a new rule means "this item is in the cart". Clear it to match any number of them instead, which ignores the comparison. Click **+ OR** to accept another product instead.

To require a particular combination of products rather than any one of them, use the **Buy X, Get Y** discount type.

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
