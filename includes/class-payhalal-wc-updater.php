<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PayHalal_WC_Updater {

    private $plugin_file;
    private $plugin_slug;
    private $github_repo;
    private $github_api;

    public function __construct() {
        $this->plugin_file = PAYHALAL_WC_BASENAME;
        $this->plugin_slug = dirname( PAYHALAL_WC_BASENAME );
        $this->github_repo = PAYHALAL_WC_GITHUB_REPO;
        $this->github_api  = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
    }

    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->get_latest_release();

        if ( ! $release || empty( $release['tag_name'] ) ) {
            return $transient;
        }

        $latest_version = ltrim( $release['tag_name'], 'v' );

        if ( version_compare( PAYHALAL_WC_VERSION, $latest_version, '<' ) ) {
            $transient->response[ $this->plugin_file ] = (object) array(
                'slug'        => $this->plugin_slug,
                'plugin'      => $this->plugin_file,
                'new_version' => $latest_version,
                'url'         => $release['html_url'],
                'package'     => $release['zipball_url'],
            );
        }

        return $transient;
    }

    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return $result;
        }

        if ( empty( $args->slug ) || $args->slug !== $this->plugin_slug ) {
            return $result;
        }

        $release = $this->get_latest_release();

        if ( ! $release ) {
            return $result;
        }

        return (object) array(
            'name'          => 'PayHalal for WooCommerce',
            'slug'          => $this->plugin_slug,
            'version'       => ltrim( $release['tag_name'], 'v' ),
            'author'        => 'Souqa Fintech Sdn Bhd',
            'homepage'      => 'https://payhalal.my',
            'download_link' => $release['zipball_url'],
            'sections'      => array(
                'description' => 'PayHalal payment gateway for WooCommerce.',
                'changelog'   => ! empty( $release['body'] )
                    ? nl2br( esc_html( $release['body'] ) )
                    : 'No changelog provided.',
            ),
        );
    }

    private function get_latest_release() {
        $response = wp_remote_get(
            $this->github_api,
            array(
                'timeout' => 15,
                'headers' => array(
                    'Accept' => 'application/vnd.github+json',
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }
}