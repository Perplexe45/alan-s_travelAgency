<?php

namespace Assistant\Wizard\Controllers;

use Assistant\Wizard\Manager;
use Assistant\Wizard\Request_Validator;
use Assistant\Wizard\Wp_Org_Api;

class Theme_Preview implements View_Controller {

	public static function render() {
		$selected_use_case = $_GET[ Manager::STATE_INPUT_NAMES['use_case'] ];
		$selected_theme    = $_GET[ Manager::STATE_INPUT_NAMES['theme'] ];

		$infos = Wp_Org_Api::get_theme_infos( array( $selected_theme ) );
		if ( empty( $infos ) ) {
			return;
		}

		$theme_name = $infos[ $selected_theme ]['name'];

		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/theme-preview.php',
			true,
			array(
				'counter_text' => __( 'Step 3 of 5', 'ionos-assistant' ),
				'heading_text' => sprintf( /* translators: s=theme name */
					__( 'Theme: %s' ),
					$theme_name
				),
				'next_step'    => 'plugin-selection',
				'info'         => $infos[ $selected_theme ],
				'theme'        => $selected_theme,
				'use_case'     => $selected_use_case,
				'preview_link' => 'https://wp-themes.com/' . $selected_theme,
			)
		);
	}

	public static function validate_request_params() {
		return Request_Validator::validate( array( 'use_case', 'theme' ) );
	}

	public static function get_page_title() {
		return __( 'Theme preview', 'ionos-assistant' );
	}

	public static function setup() {
		// TODO: Implement setup() method.
	}
}
