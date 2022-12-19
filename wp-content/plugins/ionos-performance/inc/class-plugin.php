<?php

namespace Ionos\Performance;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Plugin class
 *
 * @deprecated
 */
class Plugin {

	/**
	 * Adds the snippet for hdd-caching to the .htaccess
	 *
	 * @deprecated
	 * @return void
	 */
	public static function add_htaccess_snippet() {
		if ( Helper::has_conflicting_caching_plugins() ) {
			return;
		}

		$path           = dirname( WP_CONTENT_DIR ) . '/.htaccess';
		$htaccess_file  = file_get_contents( $path );
		$config_array   = json_decode( file_get_contents( plugin_dir_path( __DIR__ ) . 'config/ionos-performance.json' ), true );

		if ( isset( $config_array['htaccess'] ) && @file_exists( $path ) && self::is_htaccess_modified() === false ) {
			$htaccess = $config_array['htaccess'];
			$htaccess = \str_replace( '{{IONOS_PERFORMANCE_CACHE_DIR}}', IONOS_PERFORMANCE_CACHE_DIR, $htaccess );
			$htaccess = \str_replace( '{{IONOS_PERFOMRANCE_HTACCESS_VERSION}}', Htaccess::get_caching_version(), $htaccess );

			$htaccess_file = $htaccess . PHP_EOL . $htaccess_file;

			file_put_contents( $path, $htaccess_file );
		}
	}

	/**
	 * Updates .htaccess if necessary
	 *
	 * @deprecated
	 * @return bool
	 */
	public static function maybe_update_htaccess() {
		if (
			version_compare(
				trim( Htaccess::get_caching_version() ),
				trim( self::get_current_htaccess_version() ),
				'=='
			)
		) {
			return false;
		}

		self::remove_htaccess_snippet();
		self::add_htaccess_snippet();

		return true;
	}

	/**
	 * Checks if .htaccess is modified
	 *
	 * @deprecated
	 * @return bool
	 */
	public static function is_htaccess_modified() {
		$result = false;
		$path   = dirname( WP_CONTENT_DIR ) . '/.htaccess';
		if ( @file_exists( $path ) ) {
			preg_match_all( '/# START IONOS_Performance(.|\n)*?# END IONOS_Performance/', file_get_contents( $path ), $matches, PREG_SET_ORDER, 0 );
			$result = count( $matches ) > 0;
		}

		return $result;
	}

	/**
	 * @deprecated
	 * @return false|mixed|string
	 */
	private static function get_current_htaccess_version() {
		$result = '0.0.0';
		$path   = dirname( WP_CONTENT_DIR ) . '/.htaccess';
		if ( @file_exists( $path ) ) {
			preg_match( '/# IONOS_Performance Version: ([^\r\n]*)/', file_get_contents( $path ), $matches );

			if ( 0 < count( $matches ) ) {
				$result = next( $matches );
			}
		}

		return $result;
	}

	/**
	 * @deprecated
	 * @return void
	 */
	public static function remove_htaccess_snippet() {
		$path = dirname( WP_CONTENT_DIR ) . '/.htaccess';
		if ( @file_exists( $path ) ) {
			file_put_contents( $path, preg_replace( '/# START IONOS_Performance(.|\n)*?# END IONOS_Performance(\n*)/', '', file_get_contents( $path ) ) );
		}
	}
}
