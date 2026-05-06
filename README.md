# PayHalal for WooCommerce

Official WooCommerce payment gateway plugin for PayHalal.

Accept secure online payments via PayHalal directly from your WooCommerce store.

---

## Features

- WooCommerce payment gateway integration
- WooCommerce Checkout Block support
- HPOS (High-Performance Order Storage) compatibility
- Recurring payment support
- GitHub-based plugin update support
- Compatible with latest WooCommerce versions
- Native WooCommerce order handling
- Secure hash validation

---

## Requirements

- WordPress 6.0 or higher
- WooCommerce 7.0 or higher
- PHP 7.4 or higher

---

## Installation

For detailed installation instructions, kindly refer to our Wiki:

👉 https://github.com/SouqaFintech/payhalal-woocommerce-plugin/wiki

### Method 1 — Download ZIP

Download the latest release:

```bash
https://github.com/SouqaFintech/payhalal-woocommerce-plugin/releases
```

Upload the ZIP file via:

```txt
WordPress Admin → Plugins → Add New → Upload Plugin
```

---

### Method 2 — Git Clone

Run the following command inside:

```txt
/wp-content/plugins
```

```bash
git clone https://github.com/SouqaFintech/payhalal-woocommerce-plugin
```

---

## Activation

1. Activate the plugin from WordPress Admin → Plugins
2. Go to:

```txt
WooCommerce → Settings → Payments
```

3. Enable:

```txt
PayHalal Gateway
```

4. Insert your:
   - App ID
   - Secret Key

---

## Merchant Dashboard Configuration

After activating the plugin and creating your PayHalal merchant account:

1. Login to PayHalal Merchant Dashboard
2. Open:

   ```txt
   Developer Tools
   ```

3. Configure the following URLs:

### Recommended URLs

| Type         | URL                                                  |
| ------------ | ---------------------------------------------------- |
| Return URL   | `https://your-website.com/?wc-api=payhalal_callback` |
| Success URL  | `https://your-website.com/?wc-api=payhalal_callback` |
| Cancel URL   | `https://your-website.com/cart`                      |
| Callback URL | `https://your-website.com/?wc-api=payhalal_callback` |

Replace:

```txt
your-website.com
```

with your actual WooCommerce domain.

---

## WooCommerce Checkout Block Support

This plugin supports:

- Classic WooCommerce Checkout
- New WooCommerce Checkout Block

No additional configuration is required.

---

## Recurring Payments

### Requirements

- Your MID (Merchant ID) must be enabled for recurring transactions.
- Please contact the PayHalal onboarding team for activation.

### Setup

1. Create a WooCommerce product
2. Set product type to:

   ```txt
   Variable Product
   ```

3. Add an attribute named:

```txt
payment-cycle
```

4. Supported values:

```txt
MONTHLY
WEEKLY
DAILY
TEST
```

5. Save product configuration.

---

## Plugin Updates

This plugin supports GitHub-based updates.

To receive updates properly:

- Install plugin using release ZIP
- Keep plugin folder name unchanged
- Use official GitHub releases

---

## Troubleshooting

### Payment Gateway Not Showing

Please ensure:

- WooCommerce is installed
- Plugin is activated
- Gateway is enabled in WooCommerce settings

### Checkout Block Not Showing Gateway

Please ensure:

- WooCommerce Blocks is updated
- Checkout Block is used correctly
- Gateway is enabled

---

## Support

For technical assistance:

📧 tech_support@payhalal.my

---

## Disclaimer

Souqa Fintech Sdn Bhd is not responsible for any issues arising from improper configuration or third-party modifications of this plugin.

Please use this plugin responsibly and always test in sandbox mode before production deployment.
