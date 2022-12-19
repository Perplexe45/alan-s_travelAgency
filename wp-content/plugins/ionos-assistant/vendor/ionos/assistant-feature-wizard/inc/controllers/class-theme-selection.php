<?php

namespace Assistant\Wizard\Controllers;

use Assistant\Wizard\Manager;
use Ionos\Assistant\Config;
use Assistant\Wizard\Request_Validator;
use Assistant\Wizard\Use_Case;
use Assistant\Wizard\Wp_Org_Api;

class Theme_Selection implements View_Controller {

	public static function render() {
		$selected_use_case = isset( $_GET['use_case'] ) ? strtolower( $_GET['use_case'] ) : null;
		if ( empty( $selected_use_case ) ) {
			return;
		}

		$use_case = new Use_Case( Config::get( "features.wizard.usecases.$selected_use_case") );
		$themes = $use_case->get_themes();
		if ( empty( $themes ) || ! is_array( $themes ) ) {
			return;
		}

		$infos = Wp_Org_Api::get_theme_infos( array_keys( $themes ) );
		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/theme-selection.php',
			true,
			array(
				'counter_text' => __( 'Step 2 of 5', 'ionos-assistant' ),
				'heading_text' => __( 'Choose your theme', 'ionos-assistant' ),
				'themes'       => $infos,
				'next_step'    => Manager::STEP_SLUGS['theme_preview'],
			)
		);
	}

	public static function validate_request_params() {
		return Request_Validator::validate( array( 'use_case' ) );
	}

	public static function get_page_title() {
		return __( 'Theme selection', 'ionos-assistant' );
	}

	public static function setup() {
		// TODO: Implement setup() method.
	}
}
