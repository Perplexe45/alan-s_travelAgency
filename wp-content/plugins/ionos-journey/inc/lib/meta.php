<?php

namespace Ionos\Journey;

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Meta class
 * Gets meta information from plugin header and provides access it
 */
class Meta {
	private static $meta = [];

	/**
	 * Provides access to a single meta field
	 *
	 * @param $meta_name
	 *
	 * @return string
	 */
	public static function get_meta( $meta_name ) {
		if ( empty( self::$meta ) ) {
			$plugin_main_file_path = Options::get_plugin_dir_path() . Options::get_plugin_slug() . '.php';
			self::$meta            = get_plugin_data( $plugin_main_file_path );
		}

		if ( ! empty( self::$meta ) && array_key_exists( $meta_name, self::$meta ) ) {
			return self::$meta[ $meta_name ];
		}

		return '';
	}
}