<?php

namespace Ionos\Performance;

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Warning class
 */
class Warning {
	/**
	 * @var mixed|string
	 */
	private $slug;

	public function __construct( $slug = '' ) {
		$this->slug = $slug;

		\add_action( 'admin_enqueue_scripts', array( $this, 'add_script' ) );
	}

	/**
	 * add plugins deactivation warning script
	 */
	public function add_script() {
		// Load styles
		\wp_enqueue_style(
            'ionos-plugin-deactivate-warning',
			\plugins_url( 'css/plugin_warning.css', __FILE__ ),
            array(),
            \filemtime( \plugin_dir_path( __FILE__ ) . 'css/plugin_warning.css' )
        );
		// Load scripts
		\wp_enqueue_script(
            'ionos-plugin-deactivate-warning',
			\plugins_url( 'js/plugin_warning.js', __FILE__ ),
            array(),
            \filemtime( \plugin_dir_path( __FILE__ ) . 'js/plugin_warning.js' ),
            true
        );
		\wp_localize_script( 'ionos-plugin-deactivate-warning', 'plugin_deactivation_warning',
			array(
				'html'     => \file_get_contents( \plugin_dir_path( __FILE__ ) . 'html/plugin_warning.html' ),
			)
		);

		\wp_enqueue_script(
            'ionos-plugin-deactivate-warning-' . $this->slug,
			\plugins_url( 'js/plugin_warning_call.js', __FILE__ ),
            array(),
            \filemtime( \plugin_dir_path( __FILE__ ) . 'js/plugin_warning_call.js' ),
            true
        );
		\wp_localize_script( 'ionos-plugin-deactivate-warning-' . $this->slug, 'plugin_deactivation_warning_call',
			array(
				'headline' => \__( 'Warning', $this->slug),
				'body'     => \__( 'You are about to disable an IONOS plugin. This may cause dependency problems to other plugins. Do you really want to do that?', $this->slug),
				'primary'  => \__( 'Disable', $this->slug),
				'slug'     => $this->slug,
			)
		);
	}
}