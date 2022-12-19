<?php

namespace Assistant\Wizard;

use Assistant\Wizard\Controllers\Install;
use WP_REST_Server;
use WP_REST_Response;
use Plugin_Upgrader;
use Theme_Upgrader;
use Automatic_Upgrader_Skin;

class Rest_Api {

	const API_NAMESPACE     = 'assistant-wizard/v1';
	const TOKEN_OPTION_NAME = 'assistant_wizard_rest_api_token';
	const TOKEN_HEADER_NAME = 'X-Assistant-Wizard-Token';

	private static $install_data;

	public static function init() {
		add_action(
			'rest_api_init',
			function() {
				register_rest_route(
					self::API_NAMESPACE,
					'process-install-data',
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => function() {
							return self::process_install_data();
						},
						'permission_callback' => function( $request ) {
							$token = $request->get_header( self::TOKEN_HEADER_NAME );
							if ( empty( $token ) ) {
								return false;
							}

							return get_option( self::TOKEN_OPTION_NAME, false ) === $token;
						},
					)
				);
			}
		);
	}

	private static function process_install_data() {
		$result = Installer::install_next_component();
		if ( false === $result ) {
			return new WP_REST_Response( null, 422 );
		}

		if ( null === $result ) {
			return new WP_REST_Response( array( 'processing_completed' => true ), 200 );
		}

		return new WP_REST_Response( null, 200 );
	}
}
