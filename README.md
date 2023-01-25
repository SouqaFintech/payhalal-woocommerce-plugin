# Wordpress WooCommerce Plugin for Payhalal

## Note

SouqaFintech SDN BHD **IS NOT RESPONSIBLE** for any problems that may arise from the use of this extension. Use this at your own risk. For any assistance, please email <mark>tech_support@payhalal.my</mark>.

## Installation Instructions

*NOTE: You will need to have Woocommerce installed for this to work.*

You can either Download the zip file from [here](https://github.com/SouqaFintech/woocommerce-plugin) or run the following command in `/wp-content/plugins`:

```bash
git clone https://github.com/SouqaFintech/payhalal-woocommerce-plugin
```

After you have activated the plugin and created your Payhalal account, head to the Payhalal Merchant Dashboard and click on Developer tools. Add the following URLs:

- Return URL: https://your-website/shop/?wc-api=WC_Payhalal_Gateway
- Success URL: https://your-website/shop/?wc-api=WC_Payhalal_Gateway
- Cancel URL: https://your-website/shop/?wc-api=WC_Payhalal_Gateway

**Replace "your-website" with your shopping cart domain.**

If you have any troubles with installation or have any questions, please contact <mark>tech_support@payhalal.my</mark>.
