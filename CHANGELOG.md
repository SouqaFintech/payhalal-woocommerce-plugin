# Changelog

All notable changes to this project will be documented here.

---

## [1.0.5] - 2026-05-06

### Added

- Added WooCommerce Checkout Block support for compatibility with the latest WooCommerce checkout experience.
- Added WooCommerce HPOS (High-Performance Order Storage) compatibility declaration.
- Added GitHub-based plugin update support for easier future releases and maintenance.
- Added backward compatibility support for legacy PayHalal callback and request endpoints.

### Refactored

- Refactored the plugin into a modular WooCommerce gateway structure.
- Separated gateway, updater, blocks support, and redirect logic into dedicated classes/files.
- Improved plugin architecture for maintainability and future feature expansion.
- Improved callback and request handling structure following WooCommerce payment gateway standards.

### Improved

- Improved security and sanitization for callback and request data handling.
- Improved compatibility with the latest WooCommerce versions without requiring Classic Checkout.
- Improved recurring payment handling and recurring order creation using native WooCommerce order interfaces.
- Improved redirect handling using WordPress safe redirect methods.
- Improved order validation and payment status handling flow.

---

## [1.0.4] - 2025-10-21

### Added

- Added recurring option to post to `https://api-merchant.payhalal.my/seamless/Nomu/index.php`.

### Improved

- Improved recurring callback handling and properly created recurring orders using native WordPress and WooCommerce interfaces.

---

## [1.0.3] – 2025-04-25

### Added

- Introduced `get_payhalal_currency()` helper function to improve currency handling during hash validation.

### Fixed

- Enforced static `MYR` currency for PayHalal callbacks to prevent hash mismatch issues on multi-currency WooCommerce stores.
- Improved payment validation to ensure secure hash verification consistency.

### Updated

- Payment status handling updated based on the latest WooCommerce support guidance.

---

## [1.0.2] – Initial release

- First release of the PayHalal payment gateway plugin for WooCommerce.
