# Cart conditions

Cart Conditions decide when a discount applies to the cart. The same rules are offered for a discount's Global Cart Conditions and for the Cart Conditions inside each [discount group](discounts.md#discount-groups).

Every condition you add has to match. The exception is criteria joined with **+ OR** inside one Line Items or Order condition, where one match is enough.

## Date Range

Limits the discount to a date range. **From** is required and defaults to today. **To** is optional and inclusive.

## Line Items

### Quantity

Which items, and how many of each, must be in the cart. The rule reads as a sentence: "at least 3 of Product Variant Classic Tee, Medium", or "at least 1 related to Entry Fall Campaign".

Compare the quantity with **at least**, **at most**, **more than**, **fewer than**, **exactly**, or **other than**.

Then choose how the items are identified. **of** counts the line items for a specific product or variant. **related to** counts the line items whose product or variant is related to an entry or category. A relation on the product counts for every variant under it.

The quantity starts at 1, so a new rule means "this item is in the cart". To match any number of the item, clear the quantity, which ignores the comparison. To accept another product instead, click **+ OR**.

To require more than one product, add a separate **Line Items** condition for each. Separate conditions must all match.

## Order

Tests the order's totals. Choose from:

- **Item subtotal**. The total value of line items before adjustments.
- **Item total**. The total value of line items after adjustments.
- **Total**. Item subtotal plus shipping, discounts, and tax. Can be negative.
- **Total price**. The same figure, floored by the store's minimum total price strategy, such as never below \$0 or never below shipping cost.
- **Total quantity**. The total number of items in the cart.

Totals leave out non-promotable variants. See [excluded variants](excluded-variants.md).

## User

Tests the customer on the order, not the logged-in user. An order with no customer does not match.

For what happens when these conditions match, see [cart actions](cart-actions.md).
