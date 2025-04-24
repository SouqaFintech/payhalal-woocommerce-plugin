# Changelog

All notable changes to this project will be documented here.

---

## [1.0.3] – 2025-04-25

### Added

- Introduced `get_payhalal_currency()` helper function to improve currency handling during hash validation.

### Fixed

- Enforced static `MYR` currency for PayHalal callbacks to prevent hash mismatch issues on multi-currency WooCommerce stores.
- Improved payment validation to ensure secure hash verification consistency.

### Updated

- Payment status handling updated based on the latest [WooCommerce support guidance](https://wordpress.org/support/topic/wc_add_notice-not-working-anymore/).

---

## [1.0.2] – Initial release

- First release of the PayHalal payment gateway plugin for WooCommerce.
