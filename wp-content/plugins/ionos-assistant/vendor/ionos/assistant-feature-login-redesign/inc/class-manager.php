<?php

namespace Assistant\LoginRedesign;

use Ionos\Assistant\Config;
use Ionos\Assistant\Options;

class Manager {

	public static function init() {
		Options::set_tenant_and_plugin_name( 'ionos', 'assistant' );

		if ( ! Config::get( 'features.loginRedesign.enabled' ) ) {
			return;
		}

		Branding::init();

		// Add the tweaks to adjust visual details
		add_filter( 'login_body_class', array( __CLASS__, 'add_body_class' ) );
		add_filter(
			'login_link_separator',
			function() {
				// remove login separator
				return '';
			}
		);

		// Add the alternative login scripts
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'login_enqueue_scripts', array( Custom_CSS::class, 'init' ), 12 );

		// Modify / Add some HTML components for the styles and animations to work
		add_action(
			'login_header',
			function () {
				self::load_template_part( 'login-header' );
			}
		);
	}

	/**
	 * Add the special CSS classes to the login page
	 *
	 * @param  array $classes
	 * @return array
	 */
	public static function add_body_class( $classes ) {
		$classes[] = 'assistant-page';
		return $classes;
	}

	/**
	 * Add the alternative login scripts
	 */
	public static function enqueue_scripts() {
		global $interim_login;

		if ( $interim_login ) {
			return;
		}

		// Add Assistant CSS and fonts
		self::enqueue_assistant_styles();
	}

	/**
	 * Register the CSS and fonts for the new Assistant design
	 * (used in the Assistant & Login)
	 */
	public static function enqueue_assistant_styles() {

		// Add the Assistant CSS in the Assistant pages & where the Assistant adds features
		if ( self::is_login_page() ) {
			wp_enqueue_style(
				'assistant-feature-login-redesign-assistant',
				plugins_url( 'css/assistant.css', ASSISTANT_LOGIN_REDESIGN_FILE ),
				array( 'buttons' ),
				filemtime( ASSISTANT_LOGIN_REDESIGN_DIR . '/css/assistant.css' )
			);

			wp_add_inline_style( 'assistant-feature-login-redesign-assistant', Branding::get_color_styles() );
		}
	}

	/**
	 * Extends the login form HTML code with template parts
	 *
	 * @param string $filename
	 */
	public static function load_template_part( string $filename ) {
		global $interim_login;

		$template = ASSISTANT_LOGIN_REDESIGN_DIR . "/templates/$filename.php";
		if ( $interim_login || ! $filename || ! file_exists( $template ) ) {
			return;
		}

		load_template( $template );
	}

	/**
	 * Check if we are on a wp-login page
	 *
	 * @return boolean
	 */
	public static function is_login_page() {
		return false !== stripos( wp_login_url(), $_SERVER['SCRIPT_NAME'] );
	}
}
