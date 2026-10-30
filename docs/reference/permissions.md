# Permissions

The plugin does not register permissions of its own. It uses Commerce's promotion and discount permissions.

| Handle | Description |
|---|---|
| `commerce-managePromotions` | Access to **Advanced Discounts -> Discounts** and **Advanced Discounts -> Excluded Variants**. |
| `commerce-createDiscounts` | Create discounts. Without it, **New discount** is hidden. |
| `commerce-editDiscounts` | Edit and reorder discounts. |
| `commerce-deleteDiscounts` | Delete discounts. Without it, the delete action is hidden. |

Grant them under **Settings -> Users -> User Groups**, or per user under **Users -> [user] -> Permissions**.

Without `commerce-managePromotions`, the sidebar item is hidden, and a direct URL returns a 403. The plugin settings page requires an admin account.
