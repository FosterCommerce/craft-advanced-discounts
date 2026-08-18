# Excluded Variants

**Advanced Discounts -> Excluded Variants** lists every variant no discount can touch. It reads Commerce's **Promotable** switch on variants.

Columns are variant, SKU, and product. Disabled products and variants are omitted.

Excluded variants are skipped by every action, and their value is removed from the totals conditions test against. \$150 excluded plus \$60 ordinary counts as \$60 toward a "spend \$200" condition. Excluded units miss quantity conditions and Buy X, Get Y triggers too.

Selecting a product with non-promotable variants in an action flags them in the group.

See [Cart Actions](cart-actions.md) and [Cart Conditions](cart-conditions.md).
