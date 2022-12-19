<?php

namespace Assistant\JetpackBackupFlow;

define( 'ASSISTANT_JETPACK_BACKUP_FLOW_FILE', __FILE__ );
define( 'ASSISTANT_JETPACK_BACKUP_FLOW_DIR', __DIR__ );
define( 'ASSISTANT_JETPACK_BACKUP_FLOW_VIEWS_DIR', ASSISTANT_JETPACK_BACKUP_FLOW_DIR . '/inc/views' );
define( 'ASSISTANT_JETPACK_BACKUP_FLOW_BASE', plugin_basename( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

function init() {
	Manager::init();
}

add_action( 'init', __NAMESPACE__ . '\init' );
