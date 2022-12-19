<?php

namespace Assistant\Wizard;

use Ionos\Assistant\Config;

class Custom_CSS {

	public static function init() {
		$config = self::get_custom_css_config();
		self::set_custom_css( $config );
	}

	public static function get_custom_css_config() {
		$css_params = Config::get( 'custom-css' );

		if ( ! is_array( $css_params ) || ! array_key_exists( 'css', $css_params ) ) {
			return '';
		}

		return $css_params['css'];
	}

	public static function set_custom_css( $custom_css ) {
		wp_add_inline_style(
			'ionos-assistant-wizard',
			$custom_css
		);
	}
}
