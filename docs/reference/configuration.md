# Configuration

Settings on **Settings -> Plugins -> Advanced Discounts**.

| Setting | Handle | Values | Default |
|---|---|---|---|
| **Tax Basis** | `taxBasis` | `afterDiscount`, `beforeDiscount` | `afterDiscount` |
| **Shipping Discounts** | `shippingDiscounts` | `shipping`, `discount` | `shipping` |

## Tax Basis

- **After discounts**: the discount comes off before Commerce calculates tax. An Item Subtotal discount does not lower tax under Commerce's tax engine. See [Item Subtotal](../user-guide/cart-actions.md#item-subtotal).
- **Before discounts**: Commerce calculates tax on the full price, then the discount comes off.

Each discount has its own **Tax Basis** field, set to **Use plugin default** until you change it.

Tax Basis applies per order, not per discount. If any enabled discount resolves to **Before discounts**, every Advanced Discounts discount on the order applies after tax. The check ignores conditions: an enabled discount whose coupon requirement is met counts, even when its conditions do not match. One discount set to **Before discounts** can change the tax on orders it does not discount. Commerce's own discounts are not affected.

See [example order summaries](#example-order-summaries).

## Shipping Discounts

How a **Shipping** [cart action](../user-guide/cart-actions.md#shipping) appears on the order.

- **Reduce the shipping cost**: the action adds a negative `shipping` adjustment. `order.totalShippingCost` is the reduced cost, and `order.totalDiscount` leaves it out.
- **Show as a discount**: the order keeps the full shipping cost, and the action adds a `discount` adjustment. `order.totalDiscount` includes it.

New installs default to **Reduce the shipping cost**. Installs from before 1.3.0 keep **Show as a discount** after updating.

With **Show as a discount** and **After discounts**, the shipping discount is an order-level `discount` adjustment. A tax integration that spreads order-level discounts across items lowers item tax by it, and a **Free order** can get negative tax.

The setting applies to every discount and every order. See [example order summaries](#example-order-summaries).

## Example order summaries

One cart under each combination of Tax Basis and Shipping Discounts:

- One item at \$97.00, with \$10.00 shipping.
- Sales tax of 6% on items, calculated by Avatax, which spreads order-level discounts across items. Shipping is not taxed. The tax amounts are what Avatax returned for this cart.
- A discount named "Spring sale": 10% off line items and free shipping.

Rows follow the order Commerce applies adjustments. Amounts come from `order.itemSubtotal`, `order.totalShippingCost`, `order.totalDiscount`, `order.totalTax` and `order.totalPrice`.

### After discounts, Reduce the shipping cost

| Row | Amount |
|---|---|
| Subtotal | \$97.00 |
| Shipping | \$0.00 |
| Discount | −\$9.70 |
| Tax | \$5.23 |
| **Total** | **\$92.53** |

Tax is on the discounted items. The order shows \$0.00 shipping, and its adjustments list the \$10.00 charge and the −\$10.00 shipping discount.

### After discounts, Show as a discount

| Row | Amount |
|---|---|
| Subtotal | \$97.00 |
| Shipping | \$10.00 |
| Discount | −\$19.70 |
| Tax | \$4.63 |
| **Total** | **\$91.93** |

The discount row includes the free shipping. Avatax also took the \$10.00 shipping discount off the items, which lowered tax by \$0.60 compared with **Reduce the shipping cost**. Commerce's own tax engine leaves item tax unchanged here.

### Before discounts, Reduce the shipping cost

| Row | Amount |
|---|---|
| Subtotal | \$97.00 |
| Shipping | \$0.00 |
| Tax | \$5.82 |
| Discount | −\$9.70 |
| **Total** | **\$93.12** |

Tax is 6% of the full item price, and the discount comes off after it.

### Before discounts, Show as a discount

| Row | Amount |
|---|---|
| Subtotal | \$97.00 |
| Shipping | \$10.00 |
| Tax | \$5.82 |
| Discount | −\$19.70 |
| **Total** | **\$93.12** |

Same tax and total as the one before. The order lists the full shipping cost, then one discount row covering the items and the shipping.
