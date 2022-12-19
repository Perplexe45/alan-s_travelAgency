<?php

namespace Assistant\Wizard;

use Ionos\Assistant\Options;

define( 'ASSISTANT_WIZARD_FILE', __FILE__ );
define( 'ASSISTANT_WIZARD_DIR', __DIR__ );
define( 'ASSISTANT_WIZARD_VIEWS_DIR', ASSISTANT_WIZARD_DIR . '/inc/views' );
define( 'ASSISTANT_WIZARD_BASE', plugin_basename( __FILE__ ) );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

function init() {
	Manager::init();
}
add_action( 'init', __NAMESPACE__ . '\init' );

function setup() {
	Manager::setup();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\setup' );
