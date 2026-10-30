# Configuration

Settings on **Settings -> Plugins -> Advanced Discounts**.

| Setting | Handle | Values | Default |
|---|---|---|---|
| **Tax Basis** | `taxBasis` | `afterDiscount`, `beforeDiscount` | `afterDiscount` |

## Tax Basis

- **After discounts**: the discount comes off first, and Commerce calculates tax on the reduced price.
- **Before discounts**: Commerce calculates tax on the full price, then the discount comes off.

Each discount has its own **Tax Basis** field, set to **Use plugin default** until you change it.

Tax Basis applies per order, not per discount. If any enabled discount resolves to **Before discounts**, every Advanced Discounts discount on the order applies after tax. A discount counts once it is enabled and its coupon requirement is met, before its conditions are tested. One discount set to **Before discounts** can change the tax on orders it does not discount. Commerce's own discounts are not affected.
