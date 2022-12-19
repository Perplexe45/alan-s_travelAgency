<?php

namespace Ionos\Journey;

require_once 'meta.php';

/**
 * Options class
 * Manages/retrieves global WP options and options set during call
 */
class Options {

	/**
	 * @var string
	 */
	static $tenant_name;

	/**
	 * @var string
	 */
	static $plugin_name;

	/**
	 * Set tenant and plugin name
	 *
	 * @param string $tenant_name
	 * @param string $plugin_name
	 */
	public static function set_tenant_and_plugin_name( string $tenant_name, string $plugin_name ) {
		self::$tenant_name = $tenant_name;
		self::$plugin_name = $plugin_name;
	}

	/**
	 * Return complete slug of plugin
	 * {tenant_name}-{plugin_name}
	 *
	 * @return string
	 */
	public static function get_plugin_slug() {
		return self::get_tenant_name() . '-' . self::get_plugin_name();
	}

	/**
	 * @return string
	 */
	public static function get_tenant_name() {
		return self::$tenant_name;
	}

	/**
	 * @return string
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * @return string
	 */
	public static function get_plugin_dir_path() {
	    if ( strpos( __DIR__, WPMU_PLUGIN_DIR ) !== false ) {
	        return trailingslashit( WPMU_PLUGIN_DIR . '/' . self::get_plugin_slug() );
        }

        return trailingslashit( WP_PLUGIN_DIR . '/' . self::get_plugin_slug() );
    }

	/**
	 * Return the installation mode provided during the installation
	 * (available as WP option)
	 *
	 * @return string
	 */
	public static function get_installation_mode() {
		return strtolower( \get_option( self::get_tenant_name() . '_install_mode', 'standard' ) );
	}

	/**
	 * Return the contract's market value provided by the installation
	 *
	 * @return string
	 */
	public static function get_market() {

		$default_market    = 'US';
		$supported_markets = array( 'DE', 'CA', 'GB', 'UK', 'US', 'ES', 'MX', 'FR', 'IT' );

		$market = ( string ) strtoupper( \get_option( self::get_tenant_name() . '_market', $default_market ) );

		if ( ! $market || ! in_array( $market, $supported_markets ) ) {
			$market = $default_market;
		}

		return $market;
	}
}

