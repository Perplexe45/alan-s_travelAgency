<?php

namespace Assistant\Descriptify;

define( 'ASSISTANT_DESCRIPTIFY_FILE', __FILE__ );
define( 'ASSISTANT_DESCRIPTIFY_DIR', __DIR__ );
define( 'ASSISTANT_DESCRIPTIFY_BASE', plugin_basename( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

function init() {
	Manager::init();
}

add_action( 'init', __NAMESPACE__ . '\init' );
