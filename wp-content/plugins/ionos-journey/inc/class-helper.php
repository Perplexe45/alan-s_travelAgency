<?php
namespace Ionos\Journey;

// Do not allow direct access!

use function defined;
use function plugins_url;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Helper class
 */
class Helper {

	const FALLBACK_LANG = 'en';

	/**
	 * Get the url of the css folder
	 *
	 * @param  string  $file  // css file name.
	 *
	 * @return string
	 */
	public static function get_css_url( $file = '' ) {
		return plugins_url( 'css/' . $file, __DIR__ );
	}

    public static function get_css_path($file) {
        return plugin_dir_path( __DIR__ ) . 'css/' . $file;
    }    

    /**
     * Get the url to the js folder.
     *
     * @param  string  $file  // js file name.
     *
     * @return string
     */
    public static function get_js_url( $file = '' ) {
        return plugins_url( 'js/' . $file, __DIR__ );
    }

    /**
     * Get the url to the js folder.
     *
     * @param  string  $file  // js file name.
     *
     * @return string
     */
    public static function get_json_path() {
        return plugin_dir_path( __DIR__ ) . 'config/config-template.json';
    }

    public static function get_js_path($file) {
        return plugin_dir_path( __DIR__ ) . 'js/' . $file;
    }

	public static function get_configuration($key){
		$language = strtolower( explode( '_', \get_locale() )[0] );
		return json_decode( Config::get( 'data.' . $language . '_' . $key ), true ) ??
			json_decode( Config::get( 'data.' . self::FALLBACK_LANG . '_' . $key ), true) ;
	}
}
