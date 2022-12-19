<?php

namespace Ionos\Performance;

// Do not allow direct access!
use Ionos\Performance\Config;
use Ionos\PluginDetection\PluginDetection;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Helper class
 */
class Helper {
	/**
	 * Get the url of the css folder
	 *
	 * @param  string $file  // css file name.
	 *
	 * @return string
	 */
	public static function get_css_url( $file = '' ) {
		return plugins_url( 'css/' . $file, __DIR__ );
	}

	/**
	 * Get the url to the js folder.
	 *
	 * @param  string $file  // js file name.
	 *
	 * @return string
	 */
	public static function get_js_url( $file = '' ) {
		return plugins_url( 'js/' . $file, __DIR__ );
	}

	/**
	 * Get parameter from config.ini
	 *
	 * @param  string $index
	 *
	 * @return string
	 */
	public static function get_option( $index ): string {
		$option = null;

		if ( is_array( $config = self::get_config_array() )
			 && isset( $config[ $index ] )
		) {
			$option = $config[ $index ];
		}

		return $option;
	}


	/**
	 * Get configuration file(s) path
	 *
	 * @param  string $file
	 *
	 * @return string
	 */
	public static function get_config_path( $file = '' ) {
		return plugin_dir_path( __DIR__ ) . 'config/' . $file;
	}

	public static function has_conflicting_caching_plugins() {
		$conflictingCachingPlugins = Config::get( 'features.conflictingCachingPlugins' );
		if ( $conflictingCachingPlugins && ! empty( $conflictingCachingPlugins ) ) {
			return PluginDetection::has_active( $conflictingCachingPlugins );
		}

		return false;
	}
}
