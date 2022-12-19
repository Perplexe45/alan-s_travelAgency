<?php

namespace Assistant\Wizard\Controllers;

use Ionos\Assistant\Config;
use Assistant\Wizard\Manager;
use Assistant\Wizard\Request_Validator;
use Assistant\Wizard\Wp_Org_Api;

class Plugin_Advertising implements View_Controller {

	private static $is_valid_promoted_plugin;

	public static function render() {
		if ( ! self::$is_valid_promoted_plugin ) {
			// Todo: Load error template.
			exit;
		}

		$promoted_plugin = Config::get( 'features.wizard.promotedPlugin' );
		$promoted_plugin = $promoted_plugin[ array_key_first( $promoted_plugin ) ];
		$next_step = 'summary';
		if ( true === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) ) {
			$next_step = 'install';
		}

		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/plugin-advertising.php',
			true,
			array(
				'counter_text' => ' ',
				'heading_text' => '',
				'next_step'    => $next_step,
				'plugin'       => $promoted_plugin,
			)
		);
	}

	public static function validate_request_params() {
        if ( true === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) ) {
            return true;
        }

		return Request_Validator::validate( array( 'use_case', 'theme' ) );
	}

	public static function get_page_title() {
		return __( 'Jetpack', 'ionos-assistant' );
	}

	public static function validate_promoted_plugin() {
		$promoted_plugin = Config::get( 'features.wizard.promotedPlugin' );

		if ( empty( $promoted_plugin ) || ! is_array( $promoted_plugin ) ) {
			return false;
		}

		$slug            = array_key_first( $promoted_plugin );
		$promoted_plugin = $promoted_plugin[ $slug ];

		$required_array_keys = array(
			'headline',
			'name',
			'description',
			'image',
		);

		foreach ( $required_array_keys as $key ) {
			if ( ! array_key_exists( $key, $promoted_plugin ) ) {
				return false;
			}
		}

		if ( array_key_exists( 'download_url', $promoted_plugin ) ) {
			$response = wp_remote_head( $promoted_plugin[ $promoted_plugin['download_url'] ] );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			return true;
		}

		return ! empty( Wp_Org_Api::get_plugin_infos( array( $slug ) ) );
	}

	public static function setup() {
		self::$is_valid_promoted_plugin = self::validate_promoted_plugin();
		Manager::maybe_redirect_to_dashboard();
	}
}
