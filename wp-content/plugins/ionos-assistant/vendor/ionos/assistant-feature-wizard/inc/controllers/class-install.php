<?php

namespace Assistant\Wizard\Controllers;

use Assistant\Wizard\Manager;
use Ionos\Assistant\Config;
use Assistant\Wizard\Rest_Api;
use Assistant\Wizard\Theme;
use Assistant\Wizard\Use_Case;

class Install implements View_Controller {

	public static $is_pre_install_view;
	private static $install_data;

	const INSTALL_COMPONENTS_OPTION_NAME = 'assistant_wizard_install_data';

	public static function render() {

		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/install.php',
			true,
			array(
				'counter_text'        => ' ',
				'heading_text'        => __( 'Installation in progress', 'ionos-assistant' ),
				'next_step'           => 'plugin-advertising',
				'install_data'        => self::$install_data,
				'is_pre_install_view' => self::$is_pre_install_view,
			)
		);
	}

	public static function validate_request_params() {
		return true;
	}

	public static function get_page_title() {
		return __( 'Plugin selection', 'ionos-assistant' );
	}

	public static function setup() {
		Manager::maybe_redirect_to_dashboard();

		Manager::prevent_redirect();

		self::$install_data = get_option( self::INSTALL_COMPONENTS_OPTION_NAME, false );
		if ( self::$install_data ) {
			self::process_install_data();
			return;
		}

		self::$is_pre_install_view = true;

		add_action(
			'admin_head',
			function() {
				echo '<meta http-equiv="refresh" content="5">';
			}
		);

		if ( true === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) ) {
			$plugins = array();

			if ( ! empty( $_GET[ Manager::STATE_INPUT_NAMES['plugins'] ] ) ) {
				$plugins = array_flip( $_GET[ Manager::STATE_INPUT_NAMES['plugins'] ] );
			}

			if ( isset( $_GET['install_promoted'] ) ) {
				if ( Plugin_Advertising::validate_promoted_plugin() ) {
					$plugins = array_merge( $plugins, Config::get( 'features.wizard.promotedPlugin' ) );
				}
			}

			self::$install_data = array(
				'total'   => count( $plugins ),
				'plugins' => $plugins,
			);

			delete_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME );
			update_option( self::INSTALL_COMPONENTS_OPTION_NAME, self::$install_data );
			return;
		}

		$selected_use_case = $_GET[ Manager::STATE_INPUT_NAMES['use_case'] ];
		$selected_theme    = $_GET[ Manager::STATE_INPUT_NAMES['theme'] ];

		$use_case_info = Config::get( 'features.wizard.usecases.' . $selected_use_case );
		$theme_info    = $use_case_info['themes'][ $selected_theme ];

		$use_case = new Use_Case( $use_case_info );
		$theme = new Theme( $theme_info );

		$required_plugins = array_merge(
			$use_case->get_required_plugins(),
			$theme->get_required_plugins()
		);

		$optional_plugins = array_merge(
			$use_case->get_recommended_plugins(),
			$theme->get_recommended_plugins()
		);

		if ( ! isset( $_GET['plugins'] ) ) {
			$_GET['plugins'] = [];
		}
		$optional_plugins = array_intersect_key( $optional_plugins, array_flip( $_GET['plugins'] ) );

		$plugins = array_merge( $required_plugins, $optional_plugins );

		if ( isset( $_GET['install_promoted'] ) ) {
			if ( Plugin_Advertising::validate_promoted_plugin() ) {
				$plugins = array_merge( $plugins, Config::get( 'features.wizard.promotedPlugin' ) );
			}
		}

		self::$install_data = array(
			'total'             => count( $plugins ) + 1,
			'theme'             => array( $selected_theme => $theme_info ),
			'plugins'           => $plugins,
			'use_case_settings' => isset( $use_case_info['settings'] ) ? $use_case_info['settings'] : array(),
		);

		update_option( self::INSTALL_COMPONENTS_OPTION_NAME, self::$install_data );
	}

	private static function process_install_data() {
		$token = bin2hex( random_bytes( 16 ) );
		update_option( Rest_Api::TOKEN_OPTION_NAME, $token );
		$url      = esc_url_raw( rest_url( Rest_Api::API_NAMESPACE . '/' . 'process-install-data' ) );
		$url      = preg_replace( '/(.*)localhost:(?:[0-9]+)(.*)/', '$1localhost:80$2', $url );
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					Rest_Api::TOKEN_HEADER_NAME => $token,
				),
				'timeout' => 300,
			)
		);

		if ( is_wp_error( $response ) ) {
			// Todo: What to do if there was an error, show maybe an error page?
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		delete_option( Rest_Api::TOKEN_OPTION_NAME );

		if ( isset( $body->processing_completed ) ) {
			Manager::redirect_to_step( Manager::STEP_SLUGS['completed'] );
		}

		add_action(
			'admin_head',
			function() {
				echo '<meta http-equiv="refresh" content="0">';
			}
		);
	}

	public static function get_text( $string ) {
		return "Only $string components have to be installed.";
	}
}
