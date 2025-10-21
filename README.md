# Wordpress WooCommerce Plugin for Payhalal

## Installation Instructions

For detailed installation instructions, kindly refer to our [Wiki](https://github.com/SouqaFintech/payhalal-woocommerce-plugin/wiki)

*NOTE:* You will need to have Woocommerce installed for this to work.

You can either Download the zip file from [here](https://github.com/SouqaFintech/payhalal-woocommerce-plugin/archive/refs/heads/main.zip) or run the following command in `/wp-content/plugins`:

```bash
git clone https://github.com/SouqaFintech/payhalal-woocommerce-plugin
```

After you have activated the plugin and created your Payhalal account, head to the Payhalal Merchant Dashboard and click on Developer tools. Add the following URLs:

**Note:** Please do not set your callback url to avoid any issues.

- Return URL: https://your-website/?wc-api=WC_Payhalal_Gateway
- Success URL: https://your-website/?wc-api=WC_Payhalal_Gateway
- Cancel URL: https://your-website/?wc-api=WC_Payhalal_Gateway
- Callback URL: Please leave the callback-url blank.

**Replace "your-website" with your shopping cart domain.**

<img width="495" alt="image" src="https://user-images.githubusercontent.com/34120495/221494394-0379444e-fe5f-4a2e-b2c0-327d87966369.png">

If you have any troubles with installation or have any questions, please contact <mark>tech_support@payhalal.my</mark>.

## Recurring option
### To enable recurring payments:
- Make sure your MID (Merchant ID) is approved for recurring transactions. You can confirm this with our Onboarding Team.
- Once enabled, create a new product in WordPress → Products → Add New Product.
- In the Product Data section:
  -  Add a new attribute named `payment_cycle` with following values: YEARLY | MONTHLY | WEEKLY.
  -  Fill in all other required product details.
  -  After publishing the product, customers will be able to choose their preferred payment cycle (Yearly, Monthly, or Weekly) when purchasing.

## Note

SouqaFintech SDN BHD **IS NOT RESPONSIBLE** for any problems that may arise from the use of this extension. Use this at your own risk. For any assistance, please email <mark>tech_support@payhalal.my</mark>.
