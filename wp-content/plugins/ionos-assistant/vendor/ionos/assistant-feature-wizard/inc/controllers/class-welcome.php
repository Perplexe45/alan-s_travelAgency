<?php

namespace Assistant\Wizard\Controllers;

class Welcome implements View_Controller {

	public static function render() {
		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/welcome.php',
			true,
			array(
				'counter_text' => ' ',
				'heading_text' => __( 'Welcome to WordPress by IONOS', 'ionos-assistant' ),
				'next_step'    => 'use-case-selection',
			)
		);
	}


	public static function validate_request_params() {
		return true;
	}

	public static function get_page_title() {
		return '';
	}

	public static function setup() {
		// Reset state if welcome step is displayed.
		delete_transient( 'abort_screen_active' );
		delete_option( Install::INSTALL_COMPONENTS_OPTION_NAME );
	}
}
