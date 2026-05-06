<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Payhalal_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'payhalal';
        $this->icon               = '';
        $this->has_fields         = false;
        $this->method_title       = 'PayHalal Gateway';
        $this->method_description = 'Payment Without Was-Was';

        $this->supports = array( 'products' );

        $this->init_form_fields();
        $this->init_settings();

        $this->title               = $this->get_option( 'title' );
        $this->description         = $this->get_option( 'description' );
        $this->enabled             = $this->get_option( 'enabled' );
        $this->testmode            = 'yes' === $this->get_option( 'testmode' );
        $this->debug_mode          = 'yes' === $this->get_option( 'debug_mode' );
        $this->recurring_pro_rate  = 'yes' === $this->get_option( 'recurring_pro_rate' );

        $this->private_key = $this->testmode
            ? $this->get_option( 'test_private_key' )
            : $this->get_option( 'private_key' );

        $this->app_id = $this->testmode
            ? $this->get_option( 'test_publishable_key' )
            : $this->get_option( 'publishable_key' );

        $this->action_url = $this->testmode
            ? 'https://api-testing.payhalal.my/pay'
            : 'https://api.payhalal.my/pay';

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        add_action( 'woocommerce_api_payhalal_request', array( $this, 'request_handler' ) );
        add_action( 'woocommerce_api_payhalal_callback', array( $this, 'callback_handler' ) );
        add_action( 'woocommerce_api_payhalal_status', array( $this, 'check_status' ) );

        // Backward compatibility with old endpoint names.
        add_action( 'woocommerce_api_payhalalrequest', array( $this, 'request_handler' ) );
        add_action( 'woocommerce_api_wc_payhalal_gateway', array( $this, 'callback_handler' ) );
        add_action( 'woocommerce_api_payhalalstatus', array( $this, 'check_status' ) );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'label'   => 'Enable PayHalal Gateway',
                'type'    => 'checkbox',
                'default' => 'no',
            ),
            'title' => array(
                'title'   => 'Title',
                'type'    => 'text',
                'default' => 'Pay with PayHalal',
            ),
            'description' => array(
                'title'   => 'Description',
                'type'    => 'textarea',
                'default' => '<img src="https://payhalal.my/images/pay-with-payhalal-wc.png" alt="Pay with PayHalal" />',
            ),
            'testmode' => array(
                'title'   => 'Test Mode',
                'label'   => 'Enable Test Mode',
                'type'    => 'checkbox',
                'default' => 'yes',
            ),
            'test_publishable_key' => array(
                'title' => 'Test App ID',
                'type'  => 'text',
            ),
            'test_private_key' => array(
                'title' => 'Test Secret Key',
                'type'  => 'password',
            ),
            'publishable_key' => array(
                'title' => 'Live App ID',
                'type'  => 'text',
            ),
            'private_key' => array(
                'title' => 'Live Secret Key',
                'type'  => 'password',
            ),
            'debug_mode' => array(
                'title'   => 'Debug Mode',
                'label'   => 'Enable Debug Mode',
                'type'    => 'checkbox',
                'default' => 'no',
            ),
            'recurring_pro_rate' => array(
                'title'   => 'Recurring Pro Rate',
                'label'   => 'Enable recurring pro rate',
                'type'    => 'checkbox',
                'default' => 'no',
            ),
        );
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wc_add_notice( 'Invalid order.', 'error' );

            return array(
                'result'   => 'failure',
                'redirect' => wc_get_cart_url(),
            );
        }

        return array(
            'result'   => 'success',
            'redirect' => add_query_arg(
                array( 'order_id' => $order_id ),
                home_url( '/wc-api/payhalal_request' )
            ),
        );
    }

    public function request_handler() {
        $order_id = absint( $_GET['order_id'] ?? 0 );

        if ( ! $order_id ) {
            wc_add_notice( 'Invalid Order ID.', 'error' );
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wc_add_notice( 'Order not found.', 'error' );
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        $product_description = 'WooCommerce';
        $payment_cycle       = null;
        $product_id          = null;

        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();

            if ( $product && $product->is_type( 'variation' ) ) {
                $variation_attributes = $product->get_attributes();

                if ( isset( $variation_attributes['payment-cycle'] ) ) {
                    $payment_cycle = $variation_attributes['payment-cycle'];
                    $product_id    = $product->get_parent_id();

                    $check_channel = $this->check_channel( $this->app_id, $this->private_key );

                    if ( ! $check_channel ) {
                        wc_add_notice( 'Recurring not enabled for your MID.', 'error' );
                        wp_safe_redirect( wc_get_cart_url() );
                        exit;
                    }
                }
            }
        }

        $data = array(
            'app_id'              => $this->app_id,
            'amount'              => $order->get_total(),
            'currency'            => $order->get_currency(),
            'product_description' => $product_description,
            'order_id'            => $order->get_id(),
            'customer_name'       => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
            'customer_email'      => $order->get_billing_email(),
            'customer_phone'      => $order->get_billing_phone(),
        );

        if ( $payment_cycle ) {
            $data['payment_cycle']       = $payment_cycle;
            $data['product_description'] = 'WooCommerce|' . $product_id;
            $data['pro_rate']            = $this->recurring_pro_rate ? 1 : 0;

            $this->action_url = 'https://api-merchant.payhalal.my/seamless/Nomu/index.php';
        }

        $data['hash'] = $this->generate_request_hash( $data );

        wc_get_template(
            'payhalal-redirect.php',
            array(
                'data'       => $data,
                'action_url' => $this->action_url,
            ),
            '',
            PAYHALAL_WC_PATH . 'templates/'
        );

        exit;
    }

    public function callback_handler() {
        $post = wp_unslash( $_POST );

        if ( empty( $post ) ) {
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        $order_id = absint( $post['order_id'] ?? 0 );
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        $required_fields = array(
            'app_id',
            'amount',
            'currency',
            'product_description',
            'order_id',
            'customer_name',
            'customer_email',
            'customer_phone',
            'status',
            'hash',
        );

        foreach ( $required_fields as $field ) {
            if ( ! isset( $post[ $field ] ) ) {
                $order->update_status( 'failed', 'PayHalal callback missing field: ' . $field );
                wp_safe_redirect( wc_get_cart_url() );
                exit;
            }
        }

        $hash_data = array(
            'amount'              => sanitize_text_field( $post['amount'] ),
            'currency'            => sanitize_text_field( $post['currency'] ),
            'product_description' => sanitize_text_field( $post['product_description'] ),
            'order_id'            => sanitize_text_field( $post['order_id'] ),
            'customer_name'       => sanitize_text_field( $post['customer_name'] ),
            'customer_email'      => sanitize_email( $post['customer_email'] ),
            'customer_phone'      => sanitize_text_field( $post['customer_phone'] ),
            'status'              => sanitize_text_field( $post['status'] ),
        );

        $expected_hash = $this->generate_callback_hash( $hash_data );
        $received_hash = sanitize_text_field( $post['hash'] );

        if ( $this->debug_mode ) {
            $order->add_order_note( 'PayHalal Debug Expected Hash: ' . $expected_hash );
            $order->add_order_note( 'PayHalal Debug Received Hash: ' . $received_hash );
        }

        if ( ! hash_equals( $expected_hash, $received_hash ) ) {
            $order->update_status( 'failed', 'PayHalal hash mismatch.' );
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        $status = sanitize_text_field( $post['status'] );

        if ( 'SUCCESS' === $status ) {
            $source = sanitize_text_field( $post['source'] ?? '' );

            if ( 'RECURRING_NOMU' === $source ) {
                $this->create_recurring_order( $post );
                exit;
            }

            if ( ! $order->is_paid() ) {
                $transaction_id = sanitize_text_field( $post['transaction_id'] ?? '' );
                $channel        = sanitize_text_field( $post['channel'] ?? '' );

                if ( $transaction_id ) {
                    $order->add_order_note( 'Payment Success. Transaction ID: ' . $transaction_id );
                }

                if ( $channel ) {
                    $order->add_order_note( 'Payment Method: ' . $channel );
                }

                $order->payment_complete( $transaction_id );
            }

            if ( WC()->cart ) {
                WC()->cart->empty_cart();
            }

            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }

        if ( 'FAIL' === $status ) {
            $order->update_status( 'failed', 'Payment Failed.' );
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        if ( 'PENDING' === $status ) {
            $order->update_status( 'pending', 'Payment Pending.' );
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        if ( 'TIMEOUT' === $status ) {
            $order->update_status( 'failed', 'Payment Timeout.' );
            wp_safe_redirect( wc_get_cart_url() );
            exit;
        }

        wp_safe_redirect( wc_get_cart_url() );
        exit;
    }

    public function check_status() {
        $order_id = absint( $_GET['order_id'] ?? 0 );
        $order    = wc_get_order( $order_id );

        if ( $order && in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }

        wc_add_notice( 'Transaction was not processed or completed.', 'error' );
        wp_safe_redirect( wc_get_cart_url() );
        exit;
    }

    private function generate_request_hash( $data ) {
        return hash(
            'sha256',
            $this->private_key .
            $data['amount'] .
            $data['currency'] .
            $data['product_description'] .
            $data['order_id'] .
            $data['customer_name'] .
            $data['customer_email'] .
            $data['customer_phone']
        );
    }

    private function generate_callback_hash( $data ) {
        return hash(
            'sha256',
            $this->private_key .
            $data['amount'] .
            $data['currency'] .
            $data['product_description'] .
            $data['order_id'] .
            $data['customer_name'] .
            $data['customer_email'] .
            $data['customer_phone'] .
            $data['status']
        );
    }

    private function create_recurring_order( $post ) {
        $product_id = absint( $post['product_id'] ?? 0 );
        $product    = wc_get_product( $product_id );

        if ( ! $product ) {
            echo esc_html__( 'Invalid recurring product.', 'payhalal-for-woocommerce' );
            exit;
        }

        $order = wc_create_order();

        $billing_data = array(
            'first_name' => sanitize_text_field( $post['customer_name'] ?? '' ),
            'email'      => sanitize_email( $post['customer_email'] ?? '' ),
            'phone'      => sanitize_text_field( $post['customer_phone'] ?? '' ),
        );

        $order->set_address( $billing_data, 'billing' );
        $order->add_product( $product, 1 );

        $transaction_id = sanitize_text_field( $post['transaction_id'] ?? '' );
        $channel        = sanitize_text_field( $post['channel'] ?? '' );

        if ( $transaction_id ) {
            $order->add_order_note( 'Payment Success. Transaction ID: ' . $transaction_id );
        }

        if ( $channel ) {
            $order->add_order_note( 'Payment Method: ' . $channel );
        }

        $order->calculate_totals();
        $order->payment_complete( $transaction_id );
        $order->save();

        echo esc_html( 'New order created: #' . $order->get_id() );
        exit;
    }

    private function check_channel( $app_key, $app_secret ) {
        $response = wp_remote_request(
            'https://payhalal.my/nomupay/check-enabled.php',
            array(
                'method'  => 'GET',
                'timeout' => 30,
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(
                    array(
                        'app_key'    => $app_key,
                        'app_secret' => $app_secret,
                    )
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        return isset( $body['code'] ) && '200' === (string) $body['code'];
    }
}