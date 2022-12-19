<?php

namespace Assistant\Wizard;

class Wp_Org_Api {

	const INFO_REQUEST_TYPE_THEME  = 'theme';
	const INFO_REQUEST_TYPE_PLUGIN = 'plugin';

	/**
	 * Get the theme information for multiple themes at once
	 *
	 * @param $slugs
	 *
	 * @return array
	 */
	private static function get_infos( $slugs, $type ) {
		if ( ! is_array( $slugs ) || empty( $slugs ) ) {
			return array();
		}

		$transient_name = Manager::TRANSIENTS[ "{$type}_infos" ]['name'];
		$infos          = get_transient( $transient_name );
		if ( empty( $infos ) || ! is_array( $infos ) ) {
			$infos = array();
		}

		$missing_slugs = array_diff( $slugs, array_keys( $infos ) );
		$missing_infos = self::remote_info_request( $missing_slugs, $type );
		$infos         = array_merge( $infos, $missing_infos );
		set_transient( $transient_name, $infos, 60 );
		$infos = array_intersect_key( $infos, array_flip( $slugs ) );
		ksort( $infos );

		return null !== $infos ? $infos : array();
	}

	public static function get_theme_infos( $slugs ) {
		return self::get_infos( $slugs, self::INFO_REQUEST_TYPE_THEME );
	}

	public static function get_plugin_infos( $slugs ) {
		return self::get_infos( $slugs, self::INFO_REQUEST_TYPE_PLUGIN );
	}

	private static function remote_info_request( $slugs, $type ) {
		// Todo: Use plugins_api call
		$locale = get_user_locale();
		$url = "https://api.wordpress.org/{$type}s/info/1.2/?action={$type}_information&request[fields]=short_description,icons&request[locale]=$locale";

		if ( empty( $slugs ) || ! is_array( $slugs ) ) {
			return array();
		}

		foreach ( $slugs as $slug ) {
			$url .= "&request[slugs][]=$slug";
		}

		$response = wp_remote_get( $url );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return array();
		}

		$infos = json_decode( $body, true );

		return null !== $infos ? $infos : array();
	}
}
