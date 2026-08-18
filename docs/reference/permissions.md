# Permissions

The plugin registers no permissions of its own. Every control panel screen and action is gated on Craft Commerce's promotions permission.

| Handle | Description |
|---|---|
| `commerce-managePromotions` | Grants access to **Advanced Discounts -> Discounts** and **Advanced Discounts -> Excluded Variants**, including creating, editing, reordering and deleting discounts, and generating coupon codes. |

Set it under **Settings -> Users -> User Groups**, or per user under **Users -> [user] -> Permissions**.

Without it the sidebar item is hidden, and a direct URL returns a 403.
