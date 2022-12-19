<?php

namespace Assistant\LoginRedesign;

use Ionos\Assistant\Config;
use Ionos\Assistant\Options;

class Branding {

	/**
	 * @var string
	 */
	private static $brand;

	/**
	 * @var string[]
	 */
	private static $logos = array();

	/**
	 * @var string[]
	 */
	private static $visuals = array();

	/**
	 * @var string[]
	 */
	private static $colors = array();

	public static function init() {
		$brand_params = Config::get( 'branding' );

		if ( ! is_array( $brand_params ) ) {
			return;
		}

		self::$brand = isset( $brand_params['name'] ) ? $brand_params['name'] : '';

		foreach ( $brand_params as $key => $value ) {
			if ( strpos( $key, 'color_' ) !== false ) {
				self::$colors[ str_replace( 'color_', '', $key ) ] = $value;
			}
			if ( strpos( $key, 'logo_' ) !== false ) {
				self::$logos[ str_replace( 'logo_', '', $key ) ] = $value;
			}
			if ( strpos( $key, 'visual_' ) !== false ) {
				self::$visuals[ $key ] = $value;
			}
		}
	}

	public static function get_brand() {
		return self::$brand;
	}

	/**
	 * @param string $variant
	 *
	 * @return string
	 */
	public static function get_logo( $variant = null ) {
		$logos  = self::$logos;
		$id     = $variant ? $variant : 'default';
		$market = Options::get_market();

		if ( is_array( $logos ) ) {
			if ( array_key_exists( $id . '_' . $market, $logos ) ) {
				return $logos[ $id . '_' . $market ];
			} elseif ( array_key_exists( $id, $logos ) ) {
				return $logos[ $id ];
			}
		}

		return null;
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public static function get_visual( $id ) {
		$visuals = self::$visuals;

		if ( is_array( $visuals ) && array_key_exists( 'visual_' . $id, $visuals ) ) {
			return $visuals[ 'visual_' . $id ];
		}

		return null;
	}

	/**
	 * Returns the CSS snippet defining all elements with brand colors
	 * (a CSS template is used with placeholders and default values)
	 *
	 * @return string
	 */
	public static function get_color_styles() {
		$inline_styles = '';

		$backgrounds = array();
		$colors      = self::$colors;

		$styles_template  = ASSISTANT_LOGIN_REDESIGN_DIR . '/css/branding-template.css';

		// Parse CSS template sheet
		if ( is_file( $styles_template ) && is_readable( $styles_template ) ) {
			$inline_styles = file_get_contents( $styles_template );
		}

		// Render inline styles
		if ( $inline_styles ) {

			// Render simple styles
			if ( is_array( $colors ) ) {
				foreach ( $colors as $color_id => $color_value ) {
					$inline_styles = str_replace( '"{' . $color_id . '}"', $color_value, $inline_styles );
				}
			}
		}

		return $inline_styles;
	}
}
