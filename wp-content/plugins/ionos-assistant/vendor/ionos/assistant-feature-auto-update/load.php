<?php

namespace Assistant\AutoUpdate;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

define( 'FEATURE_AUTO_UPDATE_FILE', __FILE__ );
define( 'FEATURE_AUTO_UPDATE_DIR', __DIR__ );
define( 'FEATURE_AUTO_UPDATE_BASE', plugin_basename( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

function init() {
	Manager::init();
}

add_action( 'init', __NAMESPACE__ . '\init', 1 );
