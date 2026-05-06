<?php
/**
 * Plugin Name: PayHalal for WooCommerce
 * Plugin URI: https://payhalal.my
 * Description: Payment Without Was-Was.
 * Version: 1.0.5
 * Author: Souqa Fintech Sdn Bhd
 * Author URI: https://payhalal.my
 * Text Domain: payhalal-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 * Update URI: https://github.com/SouqaFintech/payhalal-woocommerce-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PAYHALAL_WC_VERSION', '1.0.5' );
define( 'PAYHALAL_WC_FILE', __FILE__ );
define( 'PAYHALAL_WC_PATH', plugin_dir_path( __FILE__ ) );
define( 'PAYHALAL_WC_URL', plugin_dir_url( __FILE__ ) );
define( 'PAYHALAL_WC_BASENAME', plugin_basename( __FILE__ ) );

define( 'PAYHALAL_WC_GITHUB_REPO', 'SouqaFintech/payhalal-woocommerce-plugin' );

require_once PAYHALAL_WC_PATH . 'includes/class-payhalal-wc-plugin.php';

add_action( 'plugins_loaded', array( 'PayHalal_WC_Plugin', 'instance' ) );