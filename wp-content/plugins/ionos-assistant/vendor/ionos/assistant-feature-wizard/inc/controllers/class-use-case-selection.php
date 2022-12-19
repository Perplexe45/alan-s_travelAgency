<?php

namespace Assistant\Wizard\Controllers;

use Ionos\Assistant\Config;
use Assistant\Wizard\Manager;

class Use_Case_Selection implements View_Controller {

	public static function render() {
		$use_cases = Config::get( 'features.wizard.usecases' );

		if ( empty( $use_cases ) || ! is_array( $use_cases ) ) {
			return;
		}

		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/use-case-selection.php',
			true,
			array(
				'counter_text' => __( 'Step 1 of 5', 'ionos-assistant' ),
				'heading_text' => __( 'What would you like to build?', 'ionos-assistant' ),
				'use_cases'    => $use_cases,
				'next_step'    => Manager::STEP_SLUGS['theme_selection'],
			)
		);
	}

	public static function validate_request_params() {
		return true;
	}

	public static function get_page_title() {
		return __( 'Use case selection', 'ionos-assistant' );
	}

	public static function setup() {
		// TODO: Implement setup() method.
	}
}
