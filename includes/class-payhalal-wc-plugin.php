<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class PayHalal_WC_Plugin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        $this->includes();
        $this->hooks();
    }

    private function includes() {
        require_once PAYHALAL_WC_PATH . 'includes/class-payhalal-wc-gateway.php';
        require_once PAYHALAL_WC_PATH . 'includes/class-payhalal-wc-blocks.php';
        require_once PAYHALAL_WC_PATH . 'includes/class-payhalal-wc-updater.php';
    }

    private function hooks() {
        add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
        add_filter( 'plugin_action_links_' . PAYHALAL_WC_BASENAME, array( $this, 'plugin_action_links' ) );

        add_action( 'woocommerce_blocks_loaded', array( $this, 'register_blocks_support' ) );

        new PayHalal_WC_Updater();
    }

    public function declare_hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                PAYHALAL_WC_FILE,
                true
            );
        }
    }

    public function register_gateway( $methods ) {
        $methods[] = 'WC_Payhalal_Gateway';
        return $methods;
    }

    public function register_blocks_support() {
        if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
            return;
        }

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( $payment_method_registry ) {
                $payment_method_registry->register( new PayHalal_WC_Blocks() );
            }
        );
    }

    public function plugin_action_links( $links ) {
        $settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payhalal' );

        array_unshift(
            $links,
            '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'payhalal-for-woocommerce' ) . '</a>'
        );

        return $links;
    }

    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'PayHalal for WooCommerce requires WooCommerce to be installed and active.', 'payhalal-for-woocommerce' );
        echo '</p></div>';
    }
}