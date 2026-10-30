# Excluded variants

**Advanced Discounts -> Excluded Variants** lists every variant the plugin skips because Commerce's **Promotable** switch is off. The page reads the primary store, and leaves out disabled products and variants.

Every action skips excluded variants, and their value does not count toward the totals conditions test. \$150 excluded plus \$60 promotable counts as \$60 toward a "spend \$200" condition. Excluded units do not count toward quantity conditions or Buy X, Get Y triggers either.

When an action names a product with non-promotable variants, the group shows a button such as "1 variant not promotable" that lists them.

See [cart actions](cart-actions.md) and [cart conditions](cart-conditions.md).
