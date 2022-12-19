<?php

namespace Assistant\Banner;

define( 'ASSISTANT_BANNER_FILE', __FILE__ );
define( 'ASSISTANT_BANNER_DIR', __DIR__ );
define( 'ASSISTANT_BANNER_BASE', plugin_basename( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

function init() {
	$manager = new Manager();
	$manager->init();
}

add_action( 'init', __NAMESPACE__ . '\init' );
