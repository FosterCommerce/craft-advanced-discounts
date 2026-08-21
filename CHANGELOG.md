# Changelog

## Unreleased

> {warning} This release rewrites saved cart conditions. Back up your database before updating.

### Added
- Added a quantity to relation matching, so a condition can require a number of items related to an Entry or Category.

### Changed
- Merged the “Has Purchasable” and “Related To” cart conditions into a single “Quantity” condition that starts at 1 and reads “at least 3 of Product Variant”.

### Fixed
- Fixed a bug where Buy X, Get Y message tokens showed 0, showed nothing, or told customers to buy more after the reward was earned.
- Fixed a bug where a “Total Price” condition could be tested against an amount below the store's minimum total price.

## 1.0.0 - 2026-08-18

### Added
- Initial release.
