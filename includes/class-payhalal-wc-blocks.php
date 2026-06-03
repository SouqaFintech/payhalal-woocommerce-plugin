<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class PayHalal_WC_Blocks extends AbstractPaymentMethodType {

    protected $name = 'payhalal';

    private $gateway;

    public function initialize() {
        $this->settings = get_option( 'woocommerce_payhalal_settings', array() );

        $gateways = WC()->payment_gateways()->payment_gateways();

        if ( isset( $gateways['payhalal'] ) ) {
            $this->gateway = $gateways['payhalal'];
        }
    }

    public function is_active() {
        return isset( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'payhalal-wc-blocks',
            PAYHALAL_WC_URL . 'assets/js/blocks.js',
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ),
            PAYHALAL_WC_VERSION,
            true
        );

        return array( 'payhalal-wc-blocks' );
    }

    public function get_payment_method_data() {
        return array(
            'title'       => $this->gateway ? $this->gateway->title : 'PayHalal',
            'description' => $this->gateway ? $this->gateway->description : 'Pay securely via PayHalal.',
            'supports'    => array(
                'features' => array( 'products' ),
            ),
        );
    }
}