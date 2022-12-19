<?php

namespace Assistant\Wizard\Controllers;

use Assistant\Wizard\Manager;

class Completed implements View_Controller {

	public static function render() {
		load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/completed.php', true );
	}

	public static function validate_request_params() {
		return true;
	}

	public static function get_page_title() {
		return __( 'Setup completed', 'ionos-assistant' );
	}

	public static function setup() {
		wp_redirect( admin_url() );
		exit;
	}
}
