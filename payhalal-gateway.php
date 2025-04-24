<?php

/**
 * @Plugin Name: PayHalal for WooCommerce
 * @Plugin URI: https://payhalal.my
 * @Description: Payment Without Was-Was
 * @Author: Souqa Fintech Sdn Bhd
 * @Author URI: https://payhalal.my
 * @Version: 1.0.3
 */

if (!defined('ABSPATH')) {
    exit;
}

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
        private $logger;
        private $log_context;

        public function __construct()
        {
            $this->id = 'payhalal';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'PayHalal Gateway';
            $this->method_description = 'Payment Without Was-Was';

            $this->supports = ['products'];

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

            $this->logger = wc_get_logger();
            $this->log_context = 'payhalal';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_api_payhalalrequest', [$this, 'request_handler']);
            add_action('woocommerce_api_wc_payhalal_gateway', [$this, 'callback_handler']);
            add_action('woocommerce_api_payhalalstatus', [$this, 'check_status']);
        }

        public function log($message, $level = 'info')
        {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $this->logger->log($level, $message, ['source' => $this->log_context]);
            }
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Payhalal Gateway',
                    'type' => 'checkbox',
                    'default' => 'no'
                ],
                'title' => [
                    'title' => 'Title',
                    'type' => 'text',
                    'default' => 'Pay with PayHalal',
                    'desc_tip' => true,
                ],
                'description' => [
                    'title' => 'Description',
                    'type' => 'textarea',
                    'default' => '<img src="https://payhalal.my/images/pay-with-payhalal-wc.png" />',
                ],
                'testmode' => [
                    'title' => 'Test mode',
                    'label' => 'Enable Test Mode',
                    'type' => 'checkbox',
                    'default' => 'yes',
                    'desc_tip' => true,
                ],
                'test_publishable_key' => ['title' => 'Test App ID', 'type' => 'text'],
                'test_private_key' => ['title' => 'Test Secret Key', 'type' => 'text'],
                'publishable_key' => ['title' => 'Live App ID', 'type' => 'text'],
                'private_key' => ['title' => 'Live Secret Key', 'type' => 'text']
            ];
        }

        public function process_payment($order_id)
        {
            return [
                'result' => 'success',
                'redirect' => get_home_url() . '/wc-api/payhalalrequest/?order_id=' . $order_id
            ];
        }

        public function request_handler()
        {
            $order_id = $_GET['order_id'] ?? 0;

            if ($order_id > 0 && ($order = wc_get_order($order_id))) {
                $data_out = [
                    "app_id" => $this->publishable_key,
                    "amount" => WC()->cart->total,
                    "currency" => $order->get_currency(),
                    "product_description" => $this->product_description,
                    "order_id" => $order->get_order_number(),
                    "customer_name" => $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
                    "customer_email" => $order->get_billing_email(),
                    "customer_phone" => $order->get_billing_phone()
                ];
                $data_out["hash"] = hash('sha256', $this->private_key . implode('', $data_out));

                $this->log('Initiating payment request: ' . json_encode($data_out));

                echo '<form id="payhalal" method="post" action="' . esc_url($this->action_url) . '">';
                foreach ($data_out as $key => $value) {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
                echo '<div style="display: grid; align-items: center; margin: auto;"><button type="submit">Click here if not redirected</button></div>';
                echo '</form><script>document.getElementById("payhalal").submit();</script>';
            } else {
                wc_add_notice('Invalid Order ID', 'error');
                wp_redirect(WC()->cart->get_cart_url());
                exit;
            }

            exit;
        }

        public function check_status()
        {
            $order_id = $_GET["order_id"] ?? 0;
            $order = wc_get_order($order_id);

            if ($order && in_array($order->status, ['processing', 'completed'])) {
                wp_redirect($this->get_return_url($order));
            } else {
                wc_add_notice('Transaction was not processed or complete.', 'error');
                wp_redirect(WC()->cart->get_cart_url());
            }

            exit;
        }

        public function callback_handler()
        {
            $post_array = $_POST;

            $this->log('Received callback: ' . json_encode($post_array));

            if (!empty($post_array['order_id']) && ($order = wc_get_order($post_array['order_id']))) {
                $key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
                $app = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');

                $data_out = [
                    "app_id" => $app,
                    "amount" => $order->total,
                    "currency" => $order->get_currency(),
                    "product_description" => $this->product_description,
                    "order_id" => $post_array["order_id"],
                    "customer_name" => $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
                    "customer_email" => $order->get_billing_email(),
                    "customer_phone" => $order->get_billing_phone(),
                    "status" => $post_array["status"]
                ];

                $generated_hash = $this->ph_sha256($data_out, $key);
                $this->log("Generated hash: $generated_hash | Received: {$post_array['hash']}");

                if ($generated_hash === $post_array['hash'] && $post_array['amount'] == $order->total) {
                    if ($post_array["status"] === "SUCCESS") {
                        WC()->cart->empty_cart();
                        $order->add_order_note('Payment Success. Transaction: ' . $post_array["transaction_id"]);
                        $order->add_order_note('Payment channel: ' . $post_array["channel"]);
                        $order->payment_complete();
                        $this->log('Payment successful for Order ID: ' . $post_array["order_id"]);
                        wp_redirect($this->get_return_url($order));
                    } else {
                        $status_message = [
                            "FAIL" => "Payment Failed.",
                            "PENDING" => "Payment Pending.",
                            "TIMEOUT" => "Payment Timeout."
                        ];
                        $message = $status_message[$post_array["status"]] ?? 'Unknown status.';
                        $order->update_status('failed', $message);
                        wc_add_notice($message, 'error');
                        wp_redirect(WC()->cart->get_cart_url());
                    }
                } else {
                    $this->log("Hash mismatch or invalid amount. Order ID: {$post_array['order_id']}", 'error');
                    wc_add_notice('Invalid hash or payment error.', 'error');
                    $order->update_status('failed', 'Hash mismatch or amount incorrect.');
                    wp_redirect(WC()->cart->get_cart_url());
                }
            } else {
                wc_add_notice('No valid callback data received.', 'error');
                $this->log('Callback handler received no data or invalid order_id.', 'error');
                wp_redirect(WC()->cart->get_cart_url());
            }

            exit;
        }

        public function ph_sha256($data, $secret)
        {
            return hash('sha256', $secret . $data["amount"] . $data["currency"] . $data["product_description"] . $data["order_id"] . $data["customer_name"] . $data["customer_email"] . $data["customer_phone"] . $data["status"]);
        }
    }
}
