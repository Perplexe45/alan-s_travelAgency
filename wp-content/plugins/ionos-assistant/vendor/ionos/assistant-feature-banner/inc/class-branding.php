<?php

namespace Assistant\Banner;

use Ionos\Assistant\Config;
use Ionos\Assistant\Options;

class Branding {
	private static $info = array(
		'name'    => '',
		'logos'   => array(),
		'visuals' => array(),
	);

	public static function setup_branding_info() {
		$brand_params = Config::get( 'branding' );
		if ( ! is_array( $brand_params ) ) {
			return;
		}

		self::$info['name'] = isset( $brand_params['name'] ) ? $brand_params['name'] : '';

		foreach ( $brand_params as $key => $value ) {
			if ( strpos( $key, 'logo_' ) !== false ) {
				self::$info['logos'][ str_replace( 'logo_', '', $key ) ] = $value;
			}
			if ( strpos( $key, 'visual_' ) !== false ) {
				self::$info['visuals'][ $key ] = $value;
			}
		}
	}

	public static function get_logo( $variant = null ) {
		$logos  = self::$info['logos'];
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

	public static function get_visual( $id ) {
		$visuals = self::$info['visuals'];
		if ( is_array( $visuals ) && array_key_exists( 'visual_' . $id, $visuals ) ) {
			return $visuals[ 'visual_' . $id ];
		}
		return null;
	}

	public static function get_name() {
		return self::$info['name'];
	}
}
