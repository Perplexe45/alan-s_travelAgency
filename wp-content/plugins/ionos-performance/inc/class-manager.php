<?php

namespace Ionos\Performance;

// Do not allow direct access!
use WP_REST_Server;
use Ionos\Performance\Options;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Manager class
 */
class Manager {

	/**
	 * Method settings
	 *
	 * @since  2.0.9
	 * @var    integer
	 */
	const METHOD_DB = 0;
	const METHOD_APC = 1;
	const METHOD_HDD = 2;
	const METHOD_MMC = 3;
	/**
	 * Minify settings
	 *
	 * @since  2.0.9
	 * @var    integer
	 */
	const MINIFY_DISABLED = 0;
	const MINIFY_HTML_ONLY = 1;
	const MINIFY_HTML_JS = 2;
	/**
	 * REST endpoints
	 *
	 * @var    string
	 */
	const REST_NAMESPACE = 'ionos-performance/v1';
	const REST_ROUTE_FLUSH = 'flush';
	/**
	 * Plugin options
	 *
	 * @since  2.0
	 * @var    array
	 */
	private static $options;
	/**
	 * Caching method
	 *
	 * @since  2.0
	 * @var    object
	 */
	private static $method;
	/**
	 * Whether we are on an Nginx server or not.
	 *
	 * @since 2.2.5
	 * @var   boolean
	 */
	private static $is_nginx;

	const CLEAR_CACHE_CRON_NAME = 'ionos_performance_clear_cache';

	/**
	 * Constructor
	 *
	 * @return  void
	 * @since   1.0.0
	 * @change  2.2.2
	 */
	public function __construct() {
		/* Set defaults */
		self::$options = self::_get_options();
		self::$method  = new Caching();

		/* Publish hooks */
		add_action( 'init', array( __CLASS__, 'register_publish_hooks' ), 99 );

		/* Flush Hooks */
		add_action( 'init', array( __CLASS__, 'register_flush_cache_hooks' ), 10, 0 );

		add_action( 'ionos_performance_remove_post_cache', array( __CLASS__, 'remove_page_cache_by_post_id' ) );

		/* Register scripts */
		add_action( 'init', array( __CLASS__, 'register_scripts' ) );

		/* Register styles */
		add_action( 'init', array( __CLASS__, 'register_styles' ) );

		add_action( 'update_option_ionos-performance', function( $old_value, $value ) {
			if ( ! get_option( 'ionos_performance_show_guided_component_activation' ) ) {
				return;
			}

			if ( isset( $value['caching_enabled'] ) && $value['caching_enabled'] ) {
				delete_option( 'ionos_performance_show_guided_component_activation' );
				delete_option( 'ionos_performance_show_activation_admin_notice' );
			}
		}, 10, 2 );

		if ( self::get_option( 'caching_enabled' ) && ! Helper::has_conflicting_caching_plugins() ) {
			/* Flush icon */
			add_action( 'admin_bar_menu', array( __CLASS__, 'add_flush_icon' ), 90 );

			/* Flush icon script */
			add_action( 'admin_bar_menu', array( __CLASS__, 'add_flush_icon_script' ), 90 );

			/* Flush REST endpoint */
			add_action( 'rest_api_init', array( __CLASS__, 'add_flush_rest_endpoint' ) );
		}

		add_action( 'init', array( __CLASS__, 'process_flush_request' ) );

		/* Flush (post) cache if comment is made from frontend or backend */
		add_action( 'pre_comment_approved', array( __CLASS__, 'pre_comment' ), 99, 2 );

		/* Hooks */
		add_action( 'plugins_loaded', array( __CLASS__, 'instance' ) );
		register_activation_hook( IONOS_PERFORMANCE_FILE, array( __CLASS__, 'on_activation' ) );
		register_deactivation_hook( IONOS_PERFORMANCE_FILE, array( __CLASS__, 'on_deactivation' ) );
		register_uninstall_hook( IONOS_PERFORMANCE_FILE, array( __CLASS__, 'on_uninstall' ) );

		/* Add Cron for clearing the HDD Cache */
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_cache_expiration' ) );

		$timestamp = wp_next_scheduled( self::CLEAR_CACHE_CRON_NAME );
		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'ionos_performance_cache_expire', self::CLEAR_CACHE_CRON_NAME );
		}

		add_action( self::CLEAR_CACHE_CRON_NAME, array( __CLASS__, 'run_hdd_cache_cron' ) );

		if ( is_admin() ) {
			/* Backend */
			add_action( 'wpmu_new_blog', array( __CLASS__, 'install_later' ) );

			add_action( 'delete_blog', array( __CLASS__, 'uninstall_later' ) );

			add_action( 'admin_init', array( __CLASS__, 'register_textdomain' ) );

			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_admin_resources' ) );

			add_action( 'admin_head', array( __CLASS__, 'admin_dashboard_styles' ) );

			add_action( 'doing_dark_mode', array( __CLASS__, 'admin_dashboard_dark_mode_styles' ) );

			add_action( 'transition_comment_status', array( __CLASS__, 'touch_comment' ), 10, 3 );

			add_action( 'edit_comment', array( __CLASS__, 'edit_comment' ) );

			add_filter( 'dashboard_glance_items', array( __CLASS__, 'add_dashboard_count' ) );
		} else {
			/* Frontend */
			add_action( 'template_redirect', array( __CLASS__, 'manage_cache' ), 0 );
			add_action( 'do_robots', array( __CLASS__, 'robots_txt' ) );
		}

		\add_action( 'admin_init', array( __CLASS__, 'add_permalink_warning' ) );

		add_action( 'admin_notices', function() {
			global $current_screen;
			if ( 'ionos_page_ionos_performance' !== $current_screen->base ) {
				return;
			}
			if ( get_option( 'ionos_performance_show_guided_component_activation' ) ) {
				?>
				<div class="notice notice-info">
					<p><?php _e( 'The IONOS Performance Plugin is now available for free to all customers of our WordPress plans.', 'ionos-performance' ); ?></p>
					<p><?php _e( 'The plugin shortens the loading time of your website by caching pages that would otherwise have to be dynamically generated by WordPress on every page load. A website’s loading time is a crucial factor for your visitors and successful search engine rankings.', 'ionos-performance' ); ?></p>
					<p><?php _e( 'If you want to try caching, select “Enable caching feature” on this page and click “Save changes”.', 'ionos-performance' ); ?></p>
					<p><?php _e( 'Verify that it works correctly by logging out and viewing your site as a non-logged-in visitor. Caching is only active for non-logged-in users.', 'ionos-performance' ); ?></p>
					<p><?php _e( 'For example, if you notice problems with contact forms, you can disable caching at any time on this page.', 'ionos-performance' ); ?></p>
				</div>
				<?php
			}
		} );
	}

	/**
	 * Get options
	 *
	 * @return  array  Array of option values
	 * @since   2.0
	 * @change  2.3.0
	 */
	private static function _get_options() {
		return wp_parse_args(
			get_option( 'ionos-performance' ),
			array(
				'caching_enabled'  => 1,
				'only_guests'      => 1,
				'compress_html'    => self::MINIFY_DISABLED,
				'cache_expires'    => 12,
				'without_ids'      => '',
				'without_agents'   => '',
				'use_apc'          => self::METHOD_DB,
				'reset_on_post'    => 1,
				'reset_on_comment' => 1,
			)
		);
	}

	/**
	 * Pseudo constructor
	 *
	 * @since   2.0.5
	 * @change  2.0.5
	 */
	public static function instance() {
		new self();
	}

	/**
	 * Deactivation hook
	 *
	 * @since   2.1.0
	 * @change  2.1.0
	 */
	public static function on_deactivation() {
		/* Remove hdd cache cron when hdd is selected */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			$timestamp = wp_next_scheduled( self::CLEAR_CACHE_CRON_NAME );
			if ( false !== $timestamp ) {
				wp_unschedule_event( $timestamp, self::CLEAR_CACHE_CRON_NAME );
			}
		}

		self::flush_total_cache( true );
	}

	/**
	 * Flush total cache
	 *
	 * @param  bool $clear_all_methods  Flush all caching methods (default: FALSE).
	 *
	 * @since   0.1
	 * @change  2.0
	 * @change  2.4.0 Do not flush cache for post revisions.
	 */
	public static function flush_total_cache( $clear_all_methods = false ) {
		// We do not need to flush the cache for saved post revisions.
		if ( did_action( 'save_post_revision' ) ) {
			return;
		}

		call_user_func(
			array(
				self::$method,
				'clear_cache',
			)
		);

		/* Transient */
		delete_transient( 'ionos_performance_cache_size' );
	}

	/**
	 * Activation hook
	 *
	 * @since   1.0
	 * @change  2.1.0
	 */
	public static function on_activation() {
		/* Multisite & Network */
		if ( is_multisite() && ! empty( $_GET['networkwide'] ) ) {
			/* Blog IDs */
			$ids = self::_get_blog_ids();

			/* Loop over blogs */
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::_install_backend();
			}

			/* Switch back */
			restore_current_blog();

		} else {
			self::_install_backend();
		}
	}

	/**
	 * Get IDs of installed blogs
	 *
	 * @return  array  Blog IDs
	 * @since   1.0
	 * @change  1.0
	 */
	private static function _get_blog_ids() {
		/* Global */
		global $wpdb;

		return $wpdb->get_col( "SELECT blog_id FROM `$wpdb->blogs`" );
	}

	/**
	 * Actual installation of the options
	 *
	 * @since   1.0
	 * @change  2.0
	 */
	private static function _install_backend() {
		add_option(
			'ionos-performance',
			array()
		);

		/* Flush */
		self::flush_total_cache( true );
	}

	/**
	 * Plugin installation on new MU blog.
	 *
	 * @param  integer $id  Blog ID.
	 *
	 * @since   1.0
	 * @change  1.0
	 */
	public static function install_later( $id ) {
		/* No network plugin */
		if ( ! is_plugin_active_for_network( IONOS_PERFORMANCE_BASE ) ) {
			return;
		}

		/* Switch to blog */
		switch_to_blog( $id );

		/* Install */
		self::_install_backend();

		/* Switch back */
		restore_current_blog();
	}

	/**
	 * Uninstalling of the plugin per MU blog.
	 *
	 * @since   1.0
	 * @change  2.1.0
	 */
	public static function on_uninstall() {
		/* Global */
		global $wpdb;

		/* Multisite & Network */
		if ( is_multisite() && ! empty( $_GET['networkwide'] ) ) {
			/* Alter Blog */
			$old = $wpdb->blogid;

			/* Blog IDs */
			$ids = self::_get_blog_ids();

			/* Loop */
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::_uninstall_backend();
			}

			/* Switch back */
			switch_to_blog( $old );
		} else {
			self::_uninstall_backend();
		}
	}

	/**
	 * Actual uninstalling of the plugin
	 *
	 * @since   1.0
	 * @change  1.0
	 */
	private static function _uninstall_backend() {
		/* Options */
		$options = array(
			'ionos-performance',
			'ionos_performance_show_activation_admin_notice',
			'ionos_performance_show_guided_component_activation',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		/* Transient */
		Options::set_tenant_and_plugin_name( 'ionos', 'performance' );
		Options::clean_up();

		/* Flush cache */
		self::flush_total_cache( true );
	}

	/**
	 * Uninstalling of the plugin for MU and network.
	 *
	 * @param  integer $id  Blog ID.
	 *
	 * @since   1.0
	 * @change  1.0
	 */
	public static function uninstall_later( $id ) {
		/* No network plugin */
		if ( ! is_plugin_active_for_network( IONOS_PERFORMANCE_BASE ) ) {
			return;
		}

		/* Switch to blog */
		switch_to_blog( $id );

		/* Install */
		self::_uninstall_backend();

		/* Switch back */
		restore_current_blog();
	}

	/**
	 * Register the styles
	 *
	 * @since 2.4.0
	 */
	public static function register_styles() {
		/* Register dashboard CSS */
		wp_register_style(
			'ionos-performance-dashboard',
			plugins_url( 'css/dashboard.min.css', IONOS_PERFORMANCE_FILE ),
			array(),
			filemtime( plugin_dir_path( IONOS_PERFORMANCE_FILE ) . 'css/dashboard.min.css' )
		);

		/* Register admin bar flush CSS */
		wp_register_style(
			'ionos-performance-admin-bar-flush',
			plugins_url( 'css/admin-bar-flush.min.css', IONOS_PERFORMANCE_FILE ),
			array(),
			filemtime( plugin_dir_path( IONOS_PERFORMANCE_FILE ) . 'css/admin-bar-flush.min.css' )
		);
	}

	/**
	 * Register the scripts
	 *
	 * @since 2.4.0
	 */
	public static function register_scripts() {
		/* Register admin bar flush script */
		wp_register_script(
			'ionos-performance-admin-bar-flush',
			plugins_url( 'js/admin-bar-flush.min.js', IONOS_PERFORMANCE_FILE ),
			array(),
			filemtime( plugin_dir_path( IONOS_PERFORMANCE_FILE ) . 'js/admin-bar-flush.min.js' ),
			true
		);
	}

	/**
	 * Register the language file
	 *
	 * @since   2.1.3
	 * @change  2.3.2
	 */
	public static function register_textdomain() {
		load_plugin_textdomain( 'ionos-performance' );
	}

	/**
	 * Get an option
	 *
	 * @param $option
	 *
	 * @return mixed|null
	 */
	public static function get_option( $option ) {
		$options = self::_get_options();
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return null;
	}

	/**
	 * Modify robots.txt
	 *
	 * @since 1.0
	 * @since 2.1.9
	 * @since 2.4   Removed $data parameter and return value.
	 */
	public static function robots_txt() {
		/* HDD only */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			echo 'Disallow: */cache/ionos-performance/';
		}
	}

	/**
	 * HDD Cache expiration cron action.
	 *
	 * @since 2.4
	 */
	public static function run_hdd_cache_cron() {
		Caching::clear_cache();
	}

	/**
	 * Add cache expiration cron schedule.
	 *
	 * @param  array $schedules  Array of previously added non-default schedules.
	 *
	 * @return array Array of non-default schedules with our tasks added.
	 *
	 * @since 2.4
	 */
	public static function add_cron_cache_expiration( $schedules ) {
		$schedules['ionos_performance_cache_expire'] = array(
			'interval' => self::$options['cache_expires'] * 3600,
			'display'  => esc_html__( 'Ionos-Performance expire', 'ionos-performance' ),
		);

		return $schedules;
	}

	/**
	 * Add the action links
	 *
	 * @param  array $data  Initial array with action links.
	 *
	 * @return  array        Merged array with action links.
	 * @since   1.0
	 * @change  1.0
	 */
	public static function action_links( $data ) {
		/* Permissions? */
		if ( ! current_user_can( 'manage_options' ) ) {
			return $data;
		}

		return array_merge(
			$data,
			array(
				sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'page' => 'ionos-performance',
						),
						admin_url( 'options-general.php' )
					),
					esc_html__( 'Settings', 'ionos-performance' )
				),
			)
		);
	}

	/**
	 * Add cache properties to dashboard
	 *
	 * @param  array $items  Initial array with dashboard items.
	 *
	 * @return  array         Merged array with dashboard items.
	 * @since   2.0.0
	 * @change  2.2.2
	 */
	public static function add_dashboard_count( $items = array() ) {
		/* Skip */
		if ( ! current_user_can( 'manage_options' ) ) {
			return $items;
		}

		/* Cache size */
		$size = self::get_cache_size();

		/* Caching method */
		$method = call_user_func(
			array(
				self::$method,
				'stringify_method',
			)
		);

		/* Output of the cache size */
		$cachesize = ( 0 === $size )
			? esc_html__( 'Empty Cache', 'ionos-performance' )
			:
			/* translators: %s: cache size */
			sprintf( esc_html__( '%s Cache', 'ionos-performance' ), size_format( $size ) );

		/* Right now item */
		$items[] = sprintf(
			'<a href="%s" title="%s: %s" class="ionos-performance-glance">
            <svg class="ionos-performance-icon ionos-performance-icon--%s" aria-hidden="true" role="img">
                <use href="%s#ionos-performance-icon-%s" xlink:href="%s#ionos-performance-icon-%s">
            </svg> %s</a>',
			add_query_arg(
				array(
					'page' => 'ionos-performance',
				),
				admin_url( 'options-general.php' )
			),
			esc_attr( strtolower( $method ) ),
			esc_html__( 'Caching method', 'ionos-performance' ),
			esc_attr( $method ),
			plugins_url( 'images/symbols.svg', IONOS_PERFORMANCE_FILE ),
			esc_attr( strtolower( $method ) ),
			plugins_url( 'images/symbols.svg', IONOS_PERFORMANCE_FILE ),
			esc_attr( strtolower( $method ) ),
			$cachesize
		);

		return $items;
	}

	/**
	 * Get the cache size
	 *
	 * @return  integer    Cache size in bytes.
	 * @since   2.0.6
	 * @change  2.0.6
	 */
	public static function get_cache_size() {
		$size = get_transient( 'ionos_performance_cache_size' );
		if ( ! $size ) {
			/* Read */
			$size = (int) call_user_func(
				array(
					self::$method,
					'get_stats',
				)
			);

			/* Save */
			set_transient(
				'ionos_performance_cache_size',
				$size,
				60 * 15
			);
		}

		return $size;
	}

	/**
	 * Add flush icon to admin bar menu
	 *
	 * @param  object $wp_admin_bar  Object of menu items.
	 *
	 * @since   1.2
	 * @change  2.2.2
	 * @change  2.4.0 Adjust icon for flush request via AJAX
	 *
	 * @hook    mixed   ionos_performanceuser_can_flush_cache
	 */
	public static function add_flush_icon( $wp_admin_bar ) {
		/* Quit */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'ionos_performanceuser_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		/* Enqueue style */
		wp_enqueue_style( 'ionos-performance-admin-bar-flush' );

		/* Display the admin icon anytime */
		echo '<style>#wp-admin-bar-ionos-performance{display:list-item !important} #wp-admin-bar-ionos-performance .ab-icon{margin:0 !important} #wp-admin-bar-ionos-performance .ab-icon:before{top:2px;margin:0;} #wp-admin-bar-ionos-performance .ab-label{margin:0 5px}</style>';

		/* Print area for aria live updates */
		echo '<span class="ab-aria-live-area screen-reader-text" aria-live="polite"></span>';
		/* Check if the flush action was used without AJAX */
		$dashicon_class = 'dashicons-trash';
		if ( isset( $_GET['_ionos-performance'] ) && 'flushed' === $_GET['_ionos-performance'] ) {
			$dashicon_class = self::get_dashicon_success_class();
		}

		/* Add menu item */
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'ionos-performance',
				'href'   => wp_nonce_url( add_query_arg( '_ionos-performance', 'flush' ), '_ionos_performance_flush_nonce' ), // esc_url in /wp-includes/class-wp-admin-bar.php#L438.
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon dashicons ' . $dashicon_class . '" aria-hidden="true"></span>' .
							'<span class="ab-label">' .
							__(
								'Flush site cache',
								'ionos-performance'
							) .
							'</span>',
				'meta'   => array(
					'title' => esc_html__( 'Flush the ionos-performance cache', 'ionos-performance' ),
				),
			)
		);
	}

	/**
	 * Returns the dashicon class for the success state in admin bar flush button
	 *
	 * @return string
	 * @since  2.4.0
	 */
	public static function get_dashicon_success_class() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.2', '<' ) ) {
			return 'dashicons-yes';
		}

		return 'dashicons-yes-alt';
	}

	/**
	 * Add a script to query the REST endpoint and animate the flush icon in admin bar menu
	 *
	 * @param  object $wp_admin_bar  Object of menu items.
	 *
	 * @since   2.4.0
	 *
	 * @hook    mixed   ionos_performanceuser_can_flush_cache ?
	 */
	public static function add_flush_icon_script( $wp_admin_bar ) {
		/* Quit */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'ionos_performanceuser_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		/* Enqueue script */
		wp_enqueue_script( 'ionos-performance-admin-bar-flush' );

		/* Localize script */
		wp_localize_script(
			'ionos-performance-admin-bar-flush',
			'ionos_performanceadmin_bar_flush_ajax_object',
			array(
				'url'              => esc_url_raw( rest_url( self::REST_NAMESPACE . '/' . self::REST_ROUTE_FLUSH ) ),
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'flushing'         => __( 'Flushing cache', 'ionos-performance' ),
				'flushed'          => __( 'Cache flushed successfully', 'ionos-performance' ),
				'dashicon_success' => self::get_dashicon_success_class(),
			)
		);
	}

	/**
	 * Registers an REST endpoint for the flush operation
	 *
	 * @change 2.4.0
	 */
	public static function add_flush_rest_endpoint() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE_FLUSH,
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array(
					__CLASS__,
					'flush_cache',
				),
				'permission_callback' => array(
					__CLASS__,
					'user_can_manage_options',
				),
			)
		);
	}

	/**
	 * Check if user can manage options
	 *
	 * @return  bool
	 * @since   2.4.0
	 */
	public static function user_can_manage_options() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Process plugin's meta actions
	 *
	 * @param  array $data  Metadata of the plugin.
	 *
	 * @since   0.5
	 * @change  2.2.2
	 * @change  2.4.0  Extract cache flushing to own method and always redirect to referer with new value for `_ionos-performance` param.
	 *
	 * @hook    mixed  ionos_performanceuser_can_flush_cache
	 */
	public static function process_flush_request( $data ) {
		/* Skip if not a flush request */
		if ( empty( $_GET['_ionos-performance'] ) || 'flush' !== $_GET['_ionos-performance'] ) {
			return;
		}

		/* Check nonce */
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_ionos_performance_flush_nonce' ) ) {
			return;
		}

		/* Skip if not necessary */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'ionos_performanceuser_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		/* Load on demand */
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		/* Flush cache */
		self::flush_cache();

		wp_safe_redirect(
			add_query_arg(
				'_ionos-performance',
				'flushed',
				wp_get_referer()
			)
		);

		exit();
	}

	/**
	 * Flush cache
	 *
	 * @since 2.4.0
	 */
	public static function flush_cache() {
		/* Flush cache */
		if ( is_multisite() && is_network_admin() ) {
			/* Old blog */
			$old = $GLOBALS['wpdb']->blogid;

			/* Blog IDs */
			$ids = self::_get_blog_ids();

			/* Loop over blogs */
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::flush_total_cache();
			}

			/* Switch back to old blog */
			switch_to_blog( $old );

			/* Notice */
			if ( is_admin() ) {
				add_action( 'network_admin_notices', array( __CLASS__, 'flush_notice' ) );
			}
		} else {
			self::flush_total_cache();

			/* Notice */
			if ( is_admin() ) {
				add_action( 'admin_notices', array( __CLASS__, 'flush_notice' ) );
			}
		}

		/* Reschedule HDD Cache Cron */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			$timestamp = wp_next_scheduled( self::CLEAR_CACHE_CRON_NAME );
			if ( false !== $timestamp ) {
				wp_reschedule_event( $timestamp, 'ionos_performancecache_expire', self::CLEAR_CACHE_CRON_NAME );
				wp_unschedule_event( $timestamp, self::CLEAR_CACHE_CRON_NAME );
			}
		}

		if ( ! is_admin() ) {
			wp_safe_redirect(
				remove_query_arg(
					'_ionos-performance',
					wp_get_referer()
				)
			);

			exit();
		}
	}

	/**
	 * Notice after successful flushing of the cache
	 *
	 * @since   1.2
	 * @change  2.2.2
	 *
	 * @hook    mixed  ionos_performanceuser_can_flush_cache
	 */
	public static function flush_notice() {
		/* No admin */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'ionos_performanceuser_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return false;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html__( 'IONOS Performance cache is flushed.', 'ionos-performance' )
		);
	}

	/**
	 * Remove page from cache or flush on comment edit
	 *
	 * @param  integer $id  Comment ID.
	 *
	 * @since   0.1.0
	 * @change  2.1.2
	 */
	public static function edit_comment( $id ) {
		if ( self::$options['reset_on_comment'] ) {
			self::flush_total_cache();
		} else {
			self::remove_page_cache_by_post_id(
				get_comment( $id )->comment_post_ID
			);
		}
	}

	/**
	 * Removes a page (id) from cache
	 *
	 * @param  integer $post_id  Post ID.
	 *
	 * @since   2.0.3
	 * @change  2.1.3
	 */
	public static function remove_page_cache_by_post_id( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return;
		}

		self::remove_page_cache_by_url( get_permalink( $post_id ) );
	}

	/**
	 * Removes a page url from cache
	 *
	 * @param  string $url  Page URL.
	 *
	 * @since   0.1
	 * @change  2.1.3
	 */
	public static function remove_page_cache_by_url( $url ) {
		$url = (string) $url;
		if ( ! $url ) {
			return;
		}

		call_user_func(
			array(
				self::$method,
				'delete_item',
			),
			self::_cache_hash( $url ),
			$url
		);
	}

	/**
	 * Get hash value for caching
	 *
	 * @param  string $url  URL to hash [optional].
	 *
	 * @return  string       IONOS Performance hash value.
	 * @since   0.1
	 * @change  2.0
	 * @change  2.4.0 Fix issue with port in URL.
	 */
	private static function _cache_hash( $url = '' ) {
		$prefix = is_ssl() ? 'https-' : '';

		if ( empty( $url ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$url = wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] );
		}

		$url_parts = wp_parse_url( $url );
		if ( ! isset( $url_parts['host'] ) ) {
			$url_parts['host'] = '';
		}
		$hash_key  = $prefix . $url_parts['host'] . $url_parts['path'];

		return md5( $hash_key ) . '.ionos-performance';
	}

	/**
	 * Remove page from cache or flush on new comment
	 *
	 * @param  mixed $approved  Comment status.
	 * @param  array $comment   Array of properties.
	 *
	 * @return  mixed            Comment status.
	 * @since   0.1.0
	 * @change  2.1.2
	 */
	public static function pre_comment( $approved, $comment ) {
		/* Approved comment? */
		if ( 1 === $approved ) {
			if ( self::$options['reset_on_comment'] ) {
				self::flush_total_cache();
			} else {
				self::remove_page_cache_by_post_id( $comment['comment_post_ID'] );
			}
		}

		return $approved;
	}

	/**
	 * Remove page from cache or flush on comment edit
	 *
	 * @param  string $new_status  New status.
	 * @param  string $old_status  Old status.
	 * @param  object $comment     The comment.
	 *
	 * @since   0.1
	 * @change  2.1.2
	 */
	public static function touch_comment( $new_status, $old_status, $comment ) {
		if ( $new_status !== $old_status ) {
			if ( self::$options['reset_on_comment'] ) {
				self::flush_total_cache();
			} else {
				self::remove_page_cache_by_post_id( $comment->comment_post_ID );
			}
		}
	}

	/**
	 * Generate publish hook for custom post types
	 *
	 * @return  void
	 * @since   2.0.3
	 *
	 * @since   2.1.7  Make the function public
	 */
	public static function register_publish_hooks() {
		/* Available post types */
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		/* Empty data? */
		if ( empty( $post_types ) ) {
			return;
		}

		/* Loop the post types */
		foreach ( $post_types as $post_type ) {
			add_action( 'publish_' . $post_type, array( __CLASS__, 'publish_post_types' ), 10, 2 );
			add_action( 'publish_future_' . $post_type, array( __CLASS__, 'flush_total_cache' ) );
		}
	}

	/**
	 * Removes the post type cache on post updates
	 *
	 * @param  integer $post_id  Post ID.
	 * @param  object  $post     Post object.
	 *
	 * @since   2.0.3
	 * @change  2.3.0
	 */
	public static function publish_post_types( $post_id, $post ) {
		/* No post_id? */
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		/* Post status check */
		if ( ! in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
			return;
		}

		/* Check user role */
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		/* Remove cache OR flush */
		if ( 1 !== self::$options['reset_on_post'] ) {
			self::remove_page_cache_by_post_id( $post_id );
		} else {
			self::flush_total_cache();
		}
	}

	/**
	 * Register all hooks to flush the total cache
	 *
	 * @since   2.4.0
	 */
	public static function register_flush_cache_hooks() {
		/* Define all default flush cache hooks */
		$flush_cache_hooks = array(
			'ionos_performanceflush_cache' => 10,
			'_core_updated_successfully'   => 10,
			'switch_theme'                 => 10,
			'before_delete_post'           => 10,
			'wp_trash_post'                => 10,
			'create_term'                  => 10,
			'delete_term'                  => 10,
			'edit_terms'                   => 10,
			'user_register'                => 10,
			'edit_user_profile_update'     => 10,
			'delete_user'                  => 10,
		);

		$flush_cache_hooks = apply_filters( 'ionos_performanceflush_cache_hooks', $flush_cache_hooks );

		/* Loop all hooks and register actions */
		foreach ( $flush_cache_hooks as $hook => $priority ) {
			add_action( $hook, array( __CLASS__, 'flush_total_cache' ), $priority, 0 );
		}

	}

	/**
	 * Assign the cache
	 *
	 * @param  string $data  Content of the page.
	 *
	 * @return  string        Content of the page.
	 * @since   0.1
	 * @change  2.0
	 */
	public static function set_cache( $data ) {
		/* Empty? */
		if ( empty( $data ) ) {
			return '';
		}

		/**
		 * Filters whether the buffered data should actually be cached
		 *
		 * @param  bool    $should_cache   Whether the data should be cached.
		 * @param  string  $data           The actual data.
		 * @param  object  $method         Instance of the selected caching method.
		 * @param  string  $cache_hash     The cache hash.
		 * @param  int     $cache_expires  Cache validity period.
		 *
		 * @since 2.3
		 */
		$should_cache = apply_filters( 'ionos_performancestore_item', true, $data, self::$method, self::_cache_hash(), self::_cache_expires() );

		/* Save? */
		if ( $should_cache ) {
			/**
			 * Filters the buffered data itself
			 *
			 * @param  string  $data           The actual data.
			 * @param  object  $method         Instance of the selected caching method.
			 * @param  string  $cache_hash     The cache hash.
			 * @param  int     $cache_expires  Cache validity period.
			 *
			 * @since 2.4
			 */
			$data = apply_filters( 'ionos_performancemodify_output', $data, self::$method, self::_cache_hash(), self::_cache_expires() );

			call_user_func(
				array(
					self::$method,
					'store_item',
				),
				self::_cache_hash(),
				self::_minify_cache( $data ),
				self::_cache_expires()
			);
		}

		return $data;
	}

	/**
	 * Get cache validity
	 *
	 * @return  integer    Validity period in seconds.
	 * @since   2.0.0
	 * @change  2.1.7
	 */
	private static function _cache_expires() {
		return HOUR_IN_SECONDS * self::$options['cache_expires'];
	}

	/**
	 * Minify HTML code
	 *
	 * @param  string $data  Original HTML code.
	 *
	 * @return  string        Minified code
	 *
	 * @hook    array   ionos_performanceminify_ignore_tags
	 * @since   0.9.2
	 * @change  2.0.9
	 */
	private static function _minify_cache( $data ) {
		/* Disabled? */
		if ( ! self::$options['compress_html'] ) {
			return $data;
		}

		/* Avoid slow rendering */
		if ( strlen( $data ) > 700000 ) {
			return $data;
		}

		/* Ignore this html tags */
		$ignore_tags = (array) apply_filters(
			'ionos_performanceminify_ignore_tags',
			array(
				'textarea',
				'pre',
			)
		);

		/* Add the script tag */
		if ( self::MINIFY_HTML_JS !== self::$options['compress_html'] ) {
			$ignore_tags[] = 'script';
		}

		/* Empty blacklist? | TODO: Make it better */
		if ( ! $ignore_tags ) {
			return $data;
		}

		/* Convert to string */
		$ignore_regex = implode( '|', $ignore_tags );

		/* Minify */
		$cleaned = preg_replace(
			array(
				'/<!--[^\[><](.*?)-->/s',
				'#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:' . $ignore_regex . ')\b))*+)(?:<(?>' . $ignore_regex . ')\b|\z))#',
			),
			array(
				'',
				' ',
			),
			$data
		);

		/* Fault */
		if ( strlen( $cleaned ) <= 1 ) {
			return $data;
		}

		return $cleaned;
	}

	/**
	 * Manage the cache.
	 *
	 * @since   0.1
	 * @change  2.3
	 */
	public static function manage_cache() {
		/* No caching? */
		if ( self::_skip_cache() ) {
			return;
		}

		/* Data present in cache */
		$cache = call_user_func(
			array(
				self::$method,
				'get_item',
			)
		);

		/* No cache? */
		if ( empty( $cache ) ) {
			ob_start( array( __CLASS__, 'set_cache' ) );

			return;
		}

		/* Process cache */
		call_user_func(
			array(
				self::$method,
				'print_cache',
			)
		);
	}

	/**
	 * Define exclusions for caching
	 *
	 * @return  boolean              TRUE on exclusion
	 *
	 * @hook    boolean  ionos_performanceskip_cache
	 * @since   0.2
	 * @change  2.3.0
	 * @change  2.4.0 Add check for sitemap feature and skip cache for other request methods than GET.
	 */
	private static function _skip_cache() {

		/* Plugin options */
		$options = self::$options;

		if ( Helper::has_conflicting_caching_plugins() ) {
			return true;
		}

		if ( 0 == $options['caching_enabled'] ) {
			return true;
		}

		/* Skip for all request methods except GET */
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false === strpos( $_SERVER['HTTP_ACCEPT'], 'text/html' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		if ( ! get_option( 'permalink_structure' ) ) {
			return true;
		}

		/* Only cache requests routed through main index.php (skip AJAX, WP-Cron, WP-CLI etc.) */
		if ( ! self::_is_index() ) {
			return true;
		}

		/* Logged in */
		if ( $options['only_guests'] && self::_is_logged_in() ) {
			return true;
		}

		/* No cache hook */
		if ( apply_filters( 'ionos_performanceskip_cache', false ) ) {
			return true;
		}

		/* Conditional Tags */
		if ( is_search() || is_404() || is_feed() || is_trackback() || is_robots() || is_preview() || post_password_required() ) {
			return true;
		}

		/* WooCommerce usage */
		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			return true;
		}

		/* Mobile request */
		if ( self::_is_mobile() ) {
			return true;
		}

		/* Post IDs */
		if ( $options['without_ids'] && is_singular() ) {
			$without_ids = array_map( 'intval', self::_preg_split( $options['without_ids'] ) );
			if ( in_array( $GLOBALS['wp_query']->get_queried_object_id(), $without_ids, true ) ) {
				return true;
			}
		}

		/* User Agents */
		if ( $options['without_agents'] && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent_strings = self::_preg_split( $options['without_agents'] );
			foreach ( $user_agent_strings as $user_agent_string ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( strpos( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), $user_agent_string ) !== false ) {
					return true;
				}
			}
		}

		// Sitemap feature added in WP 5.5.
		if ( get_query_var( 'sitemap' ) || get_query_var( 'sitemap-subtype' ) || get_query_var( 'sitemap-stylesheet' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for index page
	 *
	 * @return  boolean  TRUE if index
	 * @since   0.6
	 * @change  1.0
	 */
	private static function _is_index() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		return basename( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) === 'index.php';
	}

	/**
	 * Check if user is logged in or marked
	 *
	 * @return  boolean  $diff  TRUE on "marked" users
	 * @since   2.0.0
	 * @change  2.0.5
	 */
	private static function _is_logged_in() {
		/* Logged in */
		if ( is_user_logged_in() ) {
			return true;
		}

		/* Cookie? */
		if ( empty( $_COOKIE ) ) {
			return false;
		}

		/* Loop */
		foreach ( $_COOKIE as $k => $v ) {
			if ( preg_match( '/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for mobile devices
	 *
	 * @return  boolean  TRUE if mobile
	 * @since   0.9.1
	 * @change  2.3.0
	 */
	private static function _is_mobile() {
		$templatedir = get_template_directory();

		return ( strpos( $templatedir, 'wptouch' ) || strpos( $templatedir, 'carrington' ) || strpos( $templatedir, 'jetpack' ) || strpos( $templatedir, 'handheld' ) );
	}

	/**
	 * Split by comma
	 *
	 * @param  string $input  String to split.
	 *
	 * @return  array          Splitted values.
	 * @since   0.9.1
	 * @change  1.0
	 */
	private static function _preg_split( $input ) {
		return (array) preg_split( '/,/', $input, - 1, PREG_SPLIT_NO_EMPTY );
	}

	/**
	 * Register CSS
	 *
	 * @param  string $hook  Current hook.
	 *
	 * @since   1.0
	 * @change  2.3.0
	 */
	public static function add_admin_resources( $hook ) {
		/* Hooks check */
		if ( 'index.php' !== $hook && 'settings_page_ionos-performance' !== $hook ) {
			return;
		}

		/* Register css */
		switch ( $hook ) {
			case 'index.php':
				wp_enqueue_style( 'ionos-performance-dashboard' );
				break;

			default:
				break;
		}

	}

	/**
	 * Fixing some admin dashboard styles
	 *
	 * @since 2.3.0
	 */
	public static function admin_dashboard_styles() {
		$wp_version = get_bloginfo( 'version' );

		if ( version_compare( $wp_version, '5.3', '<' ) ) {
			echo '<style>#dashboard_right_now .ionos-performance-icon use { fill: #82878c; }</style>';
		}
	}

	/**
	 * Fixing some admin dashboard styles
	 *
	 * @since 2.3.0
	 */
	public static function admin_dashboard_dark_mode_styles() {
		echo '<style>#dashboard_right_now .ionos-performance-icon use { fill: #bbc8d4; }</style>';
	}

	/**
	 * Register settings
	 *
	 * @since   1.0
	 * @change  1.0
	 */
	public static function register_settings() {
		register_setting(
			'ionos-performance',
			'ionos-performance',
			array(
				__CLASS__,
				'validate_options',
			)
		);
	}

	/**
	 * Validate options
	 *
	 * @param  array $data  Array of form values.
	 *
	 * @return  array        Array of validated values.
	 * @since   1.0.0
	 * @change  2.1.3
	 */
	public static function validate_options( $data ) {
		/* Empty data? */
		if ( empty( $data ) ) {
			return array();
		}

		/* Flush cache */
		self::flush_total_cache( true );

		/* Return */

		return array(
			'caching_enabled'  => (int) ( ! empty( $data['caching_enabled'] ) ),
			'only_guests'      => (int) 1,
			'compress_html'    => (int) 0,
			'cache_expires'    => (int) ( isset( $data['cache_expires'] ) ? $data['cache_expires'] : self::$options['cache_expires'] ),
			'without_ids'      => (string) isset( $data['without_ids'] ) ? sanitize_text_field( $data['without_ids'] ) : '',
			'without_agents'   => (string) isset( $data['without_agents'] ) ? sanitize_text_field( $data['without_agents'] ) : '',
			'reset_on_post'    => (int) 1,
			'reset_on_comment' => (int) 1,
		);
	}

	/**
	 * Display options page
	 *
	 * @since   1.0
	 * @change  2.3.0
	 */
	public static function options_page() {
		$options = self::_get_options();
		?>

		<div class="wrap" id="ionos_performancesettings">
			<h1>IONOS Performance</h1>

			<?php
			/* Include current tab */
			include 'settings.php';
			?>
		</div>
		<?php
	}

	/**
	 * Available caching methods
	 *
	 * @return array           Array of actually available methods.
	 * @since  2.0.0
	 * @change 2.1.3
	 */
	private static function _method_select() {
		/* Defaults */
		$methods = array(
			self::METHOD_DB  => esc_html__( 'Database', 'ionos-performance' ),
			self::METHOD_APC => esc_html__( 'APC', 'ionos-performance' ),
			self::METHOD_HDD => esc_html__( 'Hard disk', 'ionos-performance' ),
			self::METHOD_MMC => esc_html__( 'Memcached', 'ionos-performance' ),
		);

		/* APC */
		if ( ! Ionos_PerformanceAPC::is_available() ) {
			unset( $methods[1] );
		}

		/* Memcached? */
		if ( ! Ionos_PerformanceMEMCACHED::is_available() ) {
			unset( $methods[3] );
		}

		/* HDD */
		if ( ! Ionos_PerformanceHDD::is_available() ) {
			unset( $methods[2] );
		}

		return $methods;
	}

	/**
	 * Minify cache dropdown
	 *
	 * @return  array    Key => value array
	 * @since   2.1.3
	 * @change  2.1.3
	 */
	private static function _minify_select() {
		return array(
			self::MINIFY_DISABLED  => esc_html__( 'No minify', 'ionos-performance' ),
			self::MINIFY_HTML_ONLY => esc_html__( 'HTML', 'ionos-performance' ),
			self::MINIFY_HTML_JS   => esc_html__( 'HTML + Inline JavaScript', 'ionos-performance' ),
		);
	}

	/**
	 * Autoload the class.
	 *
	 * @param  string $class  the class name.
	 */
	public function ionos_performance_autoload( $class ) {
		if ( in_array( $class, array( 'Caching', 'Helper' ), true ) ) {
			require_once sprintf(
				'%s/inc/class-%s.php',
				IONOS_PERFORMANCE_DIR,
				strtolower( str_replace( '_', '-', $class ) )
			);
		}
	}

	/**
	 * Outputs an admin notice if the permalink structure is set to simple
	 *
	 * @return void|null
	 */
	public static function add_permalink_warning() {
		if ( ! self::get_option( 'caching_enabled' ) || ! empty( get_option( 'permalink_structure' ) ) ) {
			return;
		}

		add_action(
			'admin_notices',
			function () {
				global $current_screen;
				if (
					'options-permalink.php' !== $GLOBALS['pagenow']
					&& 'ionos_page_ionos_performance' !== $current_screen->base
				) {
					return;
				}
				printf(
					'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
					esc_html__( 'Warning:', 'ionos-performance' ),
					esc_html__( 'You need to enable pretty permalinks to use the caching feature of IONOS Performance.', 'ionos-performance' )
				);
			}
		);
	}
}
