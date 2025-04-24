<?php
/*
 * Plugin Name: PayHalal for WooCommerce
 * Plugin URI: payhalal.my
 * Description: Payment Without Was-Was
 * Author:  Souqa Fintech Sdn Bhd
 * Author URI: https://payhalal.my
 * Version: 1.0.3
 */

add_action('plugins_loaded', 'payhalal_init_gateway_class');

function payhalal_init_gateway_class()
{
    add_filter('woocommerce_payment_gateways', 'payhalal_add_gateway');

    function payhalal_add_gateway($methods)
    {
        $methods[] = 'WC_Payhalal_Gateway';
        return $methods;
    }

    class WC_Payhalal_Gateway extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'payhalal';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'PayHalal Gateway';
            $this->method_description = 'Payment Without Was-Was';

            $this->supports = array('products');

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
            $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');
            $this->action_url = $this->testmode ? 'https://api-testing.payhalal.my/pay' : 'https://api.payhalal.my/pay';
            $this->product_description = 'WooCommerce';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_payhalalrequest', array($this, 'request_handler'));
            add_action('woocommerce_api_wc_payhalal_gateway', array($this, 'callback_handler'));
            add_action('woocommerce_api_payhalalstatus', array($this, 'check_status'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Payhalal Gateway',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'default' => 'Pay with PayHalal',
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'default' => '<img src="https://payhalal.my/images/pay-with-payhalal-wc.png" />',
                ),
                'testmode' => array(
                    'title' => 'Test mode',
                    'label' => 'Enable Test Mode',
                    'type' => 'checkbox',
                    'default' => 'yes',
                ),
                'test_publishable_key' => array(
                    'title' => 'Test App ID',
                    'type' => 'text'
                ),
                'test_private_key' => array(
                    'title' => 'Test Secret Key',
                    'type' => 'text',
                ),
                'publishable_key' => array(
                    'title' => 'Live App ID',
                    'type' => 'text'
                ),
                'private_key' => array(
                    'title' => 'Live Secret Key',
                    'type' => 'text'
                )
            );
        }

        public function process_payment($order_id)
        {
            return array(
                'result' => 'success',
                'redirect' => get_home_url() . '/wc-api/payhalalrequest/?order_id=' . $order_id
            );
        }

        public function request_handler()
        {
            $order_id = $_GET['order_id'];
            if ($order_id > 0) {
                $order = wc_get_order($order_id);
                if ($order != "") {
                    $data_out["app_id"] = $this->publishable_key;
                    $data_out["amount"] = $order->get_total();
                    $data_out["currency"] = $order->get_currency();
                    $data_out["product_description"] = $this->product_description;
                    $data_out["order_id"] = $order->get_order_number();
                    $data_out["customer_name"] = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
                    $data_out["customer_email"] = $order->get_billing_email();
                    $data_out["customer_phone"] = $order->get_billing_phone();
                    $data_out["hash"] = hash('sha256', $this->private_key . $data_out["amount"] . $data_out["currency"] . $data_out["product_description"] . $data_out["order_id"] . $data_out["customer_name"] . $data_out["customer_email"] . $data_out["customer_phone"]);
?>
                    <form id="payhalal" method="post" action="<?= $this->action_url; ?>">
                        <?php foreach ($data_out as $key => $value) { ?>
                            <input type="hidden" name="<?= $key; ?>" value="<?= $value; ?>">
                        <?php } ?>
                        <div style="display: grid; align-items: center; margin: auto;">
                            <button type="submit" style="margin: auto; text-align: center;">Please click here if you are not redirected within a few seconds</button>
                        </div>
                    </form>
                    <script type="text/javascript">
                        document.getElementById("payhalal").submit()
                    </script>
<?php
                } else {
                    wc_add_notice('Invalid Order ID', 'error');
                    wp_redirect(WC()->cart->get_cart_url());
                }
            } else {
                wc_add_notice('Invalid Order ID!', 'error');
                wp_redirect(WC()->cart->get_cart_url());
            }
            die();
        }

        public function check_status()
        {
            $order_id = $_GET["order_id"];
            $order = wc_get_order($order_id);
            $allowed_order_status = array("processing", "completed");
            if (in_array($order->status, $allowed_order_status)) {
                wp_redirect($this->get_return_url($order));
            } else {
                wc_add_notice('Transaction was not processed or complete.', 'error');
                wp_redirect(WC()->cart->get_cart_url());
            }
        }

        public function callback_handler()
        {
            $post_array = $_POST;

            if (count($post_array) > 0) {
                $order = wc_get_order($post_array['order_id']);
                $mode = $this->testmode;

                $key = $mode ? $this->get_option('test_private_key') : $this->get_option('private_key');
                $app = $mode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');

                $data_out["app_id"] = $app;
                $data_out["amount"] = $order->get_total();
                $data_out["currency"] = $this->get_payhalal_currency();
                $data_out["product_description"] = $this->product_description;
                $data_out["order_id"] = $post_array["order_id"];
                $data_out["customer_name"] = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
                $data_out["customer_email"] = $order->get_billing_email();
                $data_out["customer_phone"] = $order->get_billing_phone();
                $data_out["status"] = $post_array["status"];

                $dataout_hash = self::ph_sha256($data_out, $key);

                if ($dataout_hash === $post_array['hash'] && $post_array['amount'] == $order->get_total()) {
                    if ($post_array["status"] == "SUCCESS") {
                        WC()->cart->empty_cart();
                        $order->add_order_note(__('Payment Success. Transaction ID: ' . $post_array["transaction_id"]));
                        $order->add_order_note(__('Payment Method: ' . $post_array["channel"]));
                        $order->payment_complete();
                        wp_redirect($this->get_return_url($order));
                    } elseif ($post_array["status"] == "FAIL") {
                        wc_add_notice('Payment Failed. Please Try Again.', 'error');
                        $order->update_status('failed', 'Payment Failed.');
                        wp_redirect(WC()->cart->get_cart_url());
                    } elseif ($post_array["status"] == "PENDING") {
                        wc_add_notice('Payment is pending.', 'error');
                        $order->update_status('pending', 'Payment Pending.');
                        wp_redirect(WC()->cart->get_cart_url());
                    } elseif ($post_array["status"] == "TIMEOUT") {
                        wc_add_notice('Payment Timeout.', 'error');
                        $order->update_status('failed', 'Payment Timeout.');
                        wp_redirect(WC()->cart->get_cart_url());
                    } else {
                        wp_redirect(WC()->cart->get_cart_url());
                    }
                } else {
                    wc_add_notice('Hash validation failed.', 'error');
                    $order->update_status('failed', 'Hash Mismatch.');
                    wp_redirect(WC()->cart->get_cart_url());
                }
            } else {
                wc_add_notice('No data received.', 'error');
                wp_redirect(WC()->cart->get_cart_url());
            }
        }

        public function ph_sha256($data, $secret)
        {
            return hash('sha256', $secret . $data["amount"] . $data["currency"] . $data["product_description"] . $data["order_id"] . $data["customer_name"] . $data["customer_email"] . $data["customer_phone"] . $data["status"]);
        }

        private function get_payhalal_currency()
        {
            return 'MYR';
        }
    }
}
?>