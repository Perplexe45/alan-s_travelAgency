<?php

namespace Assistant\JetpackBackupFlow\Controllers;

class Confirm implements ViewController {
	public static function setup() {}

	public static function render() {
		load_template( ASSISTANT_JETPACK_BACKUP_FLOW_VIEWS_DIR . '/confirm.php', true );
	}

	public static function get_page_title() {
		return __( 'Confirm Jetpack installation', 'ionos-assistant' );
	}
}