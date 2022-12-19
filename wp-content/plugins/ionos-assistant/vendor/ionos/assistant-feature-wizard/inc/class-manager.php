<?php

namespace Assistant\Wizard;

use Ionos\Assistant\Config;
use Ionos\Assistant\Options;
use Assistant\Wizard\Controllers\Completed;
use Assistant\Wizard\Controllers\Install;
use Assistant\Wizard\Controllers\Abort_Plugin_Selection;
use Assistant\Wizard\Controllers\Plugin_Advertising;
use Assistant\Wizard\Controllers\Plugin_Selection;
use Assistant\Wizard\Controllers\Summary;
use Assistant\Wizard\Controllers\Theme_Preview;
use Assistant\Wizard\Controllers\Use_Case_Selection;
use Assistant\Wizard\Controllers\Welcome;
use Assistant\Wizard\Controllers\Theme_Selection;
use Ionos\LoginRedirect\LoginRedirect;

class Manager {

	private static $current_url;

	const STEP_SLUGS = array(
		'welcome'                => 'welcome',
		'use_case_selection'     => 'use-case-selection',
		'theme_selection'        => 'theme-selection',
		'theme_preview'          => 'theme-preview',
		'plugin_selection'       => 'plugin-selection',
		'plugin_advertising'     => 'plugin-advertising',
		'summary'                => 'summary',
		'install'                => 'install',
		'completed'              => 'completed',
		'abort_plugin_selection' => 'abort-plugin-selection',
	);

	const STATE_INPUT_NAMES = array(
		'use_case'         => 'use_case',
		'theme'            => 'theme',
		'plugins'          => 'plugins',
		'install_promoted' => 'install_promoted',
		'preview_link'     => 'preview_link',
	);

	const TRANSIENTS = array(
		'theme_infos'  => array(
			'name'     => 'assistant_wizard_theme_infos',
			'duration' => 3600,
		),
		'plugin_infos' => array(
			'name'     => 'assistant_wizard_plugin_infos',
			'duration' => 3600,
		),
	);


	const WIZARD_COMPLETED_OPTION_NAME = 'ionos_assistant_completed';

	private static $is_controller_error;

	private static $steps = array(
		self::STEP_SLUGS['welcome']                => Welcome::class,
		self::STEP_SLUGS['use_case_selection']     => Use_Case_Selection::class,
		self::STEP_SLUGS['theme_selection']        => Theme_Selection::class,
		self::STEP_SLUGS['theme_preview']          => Theme_Preview::class,
		self::STEP_SLUGS['plugin_selection']       => Plugin_Selection::class,
		self::STEP_SLUGS['plugin_advertising']     => Plugin_Advertising::class,
		self::STEP_SLUGS['summary']                => Summary::class,
		self::STEP_SLUGS['install']                => Install::class,
		self::STEP_SLUGS['completed']              => Completed::class,
		self::STEP_SLUGS['abort_plugin_selection'] => Abort_Plugin_Selection::class,
	);

	private static $current_controller;

	/**
	 * This setup-method is beeing called earlier than init. Once the tariff
	 * is beeing set, it is not meant to be changed.
	 *
	 * @return void
	 */
	public static function setup() {
		if ( true === (bool) get_option( self::WIZARD_COMPLETED_OPTION_NAME, false ) ) {
			return;
		}

		self::set_tariff();

		if ( self::has_tariff_feature( 'woocommerce' ) ) {
			add_filter(
				'ionos_library_service_url_before_placeholder_replacement',
				array( __CLASS__, 'set_mode_to_woocommerce' ),
				10,
				4
			);
		}
	}

	public static function set_mode_to_woocommerce( $url, $service, $tenant, $plugin ) {
		if ( 'config' !== $service || 'assistant' !== $plugin ) {
			return $url;
		}
		$url = str_replace( '{mode}', 'woocommerce', $url );

		return $url;
	}

	public static function init() {
		if ( defined( 'WP_CLI' ) && true === WP_CLI ) {
			return;
		}

		self::$current_url = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

		Options::set_tenant_and_plugin_name( 'ionos', 'assistant' );
		Rest_Api::init();

		if ( ! Config::get( 'features.wizard.enabled' ) ) {
			return;
		}

		if ( isset( $_GET['coupon'] ) ) {
			return;
		}

		LoginRedirect::register_redirect();

		add_action( 'admin_menu', array( __CLASS__, 'add_assistant_page' ) );
		if ( false === (bool) get_option( self::WIZARD_COMPLETED_OPTION_NAME, false ) ) {
			add_filter( 'ionos_login_redirect_to', array( __CLASS__, 'start_wizard' ), 200, 3 );
		}

		add_action(
			'admin_init',
			function() {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				if ( ! isset( $_GET['assistant_wizard_completed'] ) ) {
					return;
				}

				update_option( self::WIZARD_COMPLETED_OPTION_NAME, true );
			},
			5
		);

		if ( ! self::is_wizard_page() ) {
			return;
		}

		add_filter(
			'admin_title',
			function ( $admin_title ) {
				if ( ! self::is_wizard_page() || self::$is_controller_error ) {
					return $admin_title;
				}

				$title = array_filter(
					array(
						self::$current_controller::get_page_title(),
						__( 'IONOS Assistant', 'ionos-assistant' ),
					)
				);

				return implode( ' – ', $title );
			},
			10
		);

		add_action(
			'admin_init',
			function() {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				self::$is_controller_error = ! self::setup_controller();
			},
			5
		);

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_wizard_resources' ) );
		add_action( 'admin_enqueue_scripts', array( Custom_CSS::class, 'init' ), 12 );
	}

	/**
	 * The tariff is detected by the presence of a plugin.
	 * A tariff may have multiple tariff features.
	 * Defaults to standard.
	 *
	 * @return void
	 */
	private static function set_tariff() {
		$tariff = (string) get_option( 'ionos_tariff', '' );
		if ( '' !== $tariff ) {
			return;
		}

		if ( true === class_exists( 'WooCommerce', false ) ) {
			update_option( 'ionos_tariff', 'woocommerce' );
			self::add_tariff_feature( 'woocommerce' );
			delete_transient( 'ionos_assistant_config' );
			return;
		}

		update_option( 'ionos_tariff', 'standard' );
	}

	private static function get_tariff() {
		return (string) get_option( 'ionos_tariff', 'standard' );
	}

	private static function add_tariff_feature( $feature ) {
		$features = (array) get_option( 'ionos_tariff_features', array() );
		array_push( $features, $feature );
		$features = array_unique( $features );
		update_option( 'ionos_tariff_features', $features );
	}

	private static function has_tariff_feature( $feature ) {
		return in_array( $feature, (array) get_option( 'ionos_tariff_features', array() ), true );
	}

	private static function is_wizard_page() {
		return false !== strpos( self::$current_url, 'page=ionos-assistant' );
	}

	/**
	 * Determines if a valid step exists and the corresponding controller for that, validates the request params
	 * Returns true if no problem appeared
	 *
	 * @return bool
	 */
	private static function setup_controller() {
		$step = isset( $_GET['step'] ) ? $_GET['step'] : self::STEP_SLUGS['welcome'];
		if ( get_option( self::WIZARD_COMPLETED_OPTION_NAME, false ) ) {
			$step = self::STEP_SLUGS['completed'];
		}

		self::$current_controller = isset( self::$steps[ $step ] ) ? self::$steps[ $step ] : '';
		if ( empty( self::$current_controller ) ) {
			return false;
		}

		if ( ! self::$current_controller::validate_request_params() ) {
			return false;
		}

		self::$current_controller::setup();

		return true;
	}

	public static function start_wizard( $redirect_to, $origin_redirect_to, $user ) {
		if ( is_wp_error( $user ) ) {
			return $redirect_to;
		}

		if ( ! $user->has_cap( 'manage_options' ) ) {
			return $redirect_to;
		}

		$params = self::get_params_from_url( $origin_redirect_to );
		if ( isset( $params['coupon'] ) ) {
			return $redirect_to;
		}

		return get_admin_url( null, 'admin.php?page=ionos-assistant' );
	}

	public static function add_assistant_page() {
		add_menu_page(
			'Ionos Assistant',
			'Ionos Assistant',
			'administrator',
			'ionos-assistant',
			array(
				__CLASS__,
				'show_assistant_page',
			)
		);
		remove_menu_page( 'ionos-assistant' );
	}

	public static function redirect_to_step( $step ) {
		wp_redirect( get_admin_url( null, "admin.php?page=ionos-assistant&step=$step" ) );
		exit;
	}

	public static function show_assistant_page() {
		if ( ! self::is_wizard_page() || self::$is_controller_error ) {
			return;
		}

		self::$current_controller::render();
	}

	public static function prevent_redirect() {
		add_filter(
			'wp_redirect',
			function( $location ) {
				if ( false !== strpos( $location, 'page=ionos-assistant&step=completed' ) ) {
					return $location;
				}
				return self::$current_url;
			},
			PHP_INT_MAX
		);
	}

	public static function enqueue_wizard_resources( $hook_suffix ) {
		if ( 'toplevel_page_ionos-assistant' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'ionos-assistant-wizard',
			plugins_url( 'css/wizard.css', __DIR__ ),
			array(),
			filemtime( ASSISTANT_WIZARD_DIR . '/css/wizard.css' )
		);
	}

	private static function get_params_from_url( $url ) {
		$url_query_string = wp_parse_url( $url, PHP_URL_QUERY );
		if ( ! is_string( $url_query_string ) ) {
			return null;
		}

		$params = null;
		wp_parse_str( $url_query_string, $params );

		return $params;
	}

	public static function maybe_redirect_to_dashboard() {
		$has_plugins = ! empty( $_GET['plugins'] );
		if ( true === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) && ! $has_plugins ) {
			$skip_url = add_query_arg(
				array(
					'assistant_wizard_completed' => '1',
				),
				get_admin_url()
			);
			wp_redirect( $skip_url );
			exit;
		}
	}
}
