# Changelog

All notable changes to this project will be documented here.

---

## [1.0.5] - 2025-10-30

- handle recurring callback, properly created order using wordpress interface
- 
## [1.0.4] - 2025-10-21

- Added recurring option to post to `https://api-merchant.payhalal.my/seamless/Nomu/index.php`;

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

