<?php

namespace Assistant\Wizard\Controllers;

use Ionos\Assistant\Config;
use Assistant\Wizard\Market_Helper;
use Assistant\Wizard\Wp_Org_Api;

class Abort_Plugin_Selection implements View_Controller {
	private static $plugin_infos;
	const ABORT_SCREEN_TRANSIENT_NAME = 'abort_screen_active';

	public static function render() {
		set_transient( self::ABORT_SCREEN_TRANSIENT_NAME, true, 3600 );

		$plugins = Config::get( 'features.wizard.plugins' );
		$plugins = Market_Helper::filter_assets_by_market( $plugins );

		$next_step          = Plugin_Advertising::validate_promoted_plugin() ? 'plugin-advertising' : 'install';
		self::$plugin_infos = Wp_Org_Api::get_plugin_infos( array_keys( $plugins ) );

		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/abort-plugin-selection.php',
			true,
			array(
				'counter_text' => __( 'Step 1 of 1', 'ionos-assistant' ),
				'heading_text' => __( 'Pick some plugins', 'ionos-assistant' ),
				'next_step'    => $next_step,
				'plugins'      => $plugins,
				'plugin_infos' => self::$plugin_infos,
			)
		);
	}

	public static function validate_request_params() {
		return true;
	}


	public static function get_page_title() {
		return __( 'Abort Plugin selection', 'ionos-assistant' );
	}

	public static function setup() {
		if ( ! Config::get( 'features.wizard.abortFlow.enabled' ) ) {
			$skip_url = add_query_arg( array(
				'assistant_wizard_completed' => '1',
			), get_admin_url() );
			wp_redirect( $skip_url );
			exit;
		}
	}

	public static function get_plugin_name( $slug ) {
		$paths = array(
			"features.wizard.plugins.$slug.name",
		);

		foreach ( $paths as $path ) {
			$name = Config::get( $path );
			if ( $name ) {
				return $name;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['name'] ) ) {
			return self::$plugin_infos[ $slug ]['name'];
		}

		return 'no name';
	}

	public static function get_plugin_description( $slug ) {
		$paths = array(
			"features.wizard.plugins.$slug.description",
		);

		foreach ( $paths as $path ) {
			$description = Config::get( $path );
			if ( $description ) {
				return $description;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['short_description'] ) ) {
			return self::$plugin_infos[ $slug ]['short_description'];
		}

		return 'no description';
	}

	public static function get_plugin_version( $slug ) {
		$paths = array(
			"features.wizard.plugins.$slug.version",
		);

		foreach ( $paths as $path ) {
			$version = Config::get( $path );
			if ( $version ) {
				return $version;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['version'] ) ) {
			return self::$plugin_infos[ $slug ]['version'];
		}

		return 'no version';
	}

	public static function get_plugin_author( $slug ) {
		$paths = array(
			"features.wizard.plugins.$slug.author",
		);

		foreach ( $paths as $path ) {
			$author = Config::get( $path );
			if ( $author ) {
				return $author;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['author'] ) ) {
			return self::$plugin_infos[ $slug ]['author'];
		}

		return 'no author';
	}

}
