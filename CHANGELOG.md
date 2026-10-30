# Release Notes for Advanced Discounts

## 1.2.0 - 2026-10-30

### Added
- Added per-site text for discount names, group names, and messages on multi-site installs.
- Added a site menu to the Discounts index and discount edit pages.

### Changed
- Discount values can no longer be negative, percentage discounts can no longer exceed 100, and Buy X, Get Y quantities must be at least 1.

### Fixed
- Fixed a bug where stacked discounts could take a line item, the item subtotal, or shipping below zero.
- Fixed an error that occurred when saving a discount again after one of its coupon codes failed validation.
- Fixed a bug where coupon code errors were not shown on the discount edit page.
- Fixed a bug where users without Commerce's “Create discounts”, “Edit discounts” or “Delete discounts” permission could create, edit or delete advanced discounts.
- Fixed an error that could occur on every cart when a Buy X, Get Y discount had a quantity below 1.
- Fixed a bug where a group error was listed once for each message it applied to.
- Fixed a bug where control panel labels and cart notices showed as translation keys on sites using an English locale other than US English.
- Fixed a bug where a coupon code's uses were not counted on PostgreSQL when the customer entered it in a different case.
- Fixed a bug where message amounts were labeled with the payment currency instead of the store currency.
- Fixed an error that occurred when generating more coupon codes than the format allows.
- Fixed a bug where the instructions for “Stop Processing Further Discounts”, “Tax Basis” and non-promotable variants described behavior the plugin does not have.

## 1.1.2 - 2026-09-18

### Fixed
- Fixed a bug where custom user fields were unavailable as rules in a User cart condition.

## 1.1.1 - 2026-09-10

### Fixed
- Fixed a bug where a coupon code on a completed order was removed when the order was edited in the control panel.

## 1.1.0 - 2026-08-21

### Added
- Added the ability to require a quantity of items related to an entry or category.

### Changed
- The “Has Purchasable” and “Related To” cart conditions are now a single “Quantity” condition, and saved conditions are converted on update.

### Removed
- Removed `HasPurchasableConditionRule` and `RelatedToConditionRule`. Use `LineItemMatchConditionRule` instead.

### Fixed
- Fixed a bug where Buy X, Get Y message tokens showed 0, showed nothing, or told customers to buy more after the reward was earned.
- Fixed a bug where a “Total Price” condition could be tested against an amount below the store's minimum total price.

## 1.0.0 - 2026-08-18

### Added
- Initial release.
