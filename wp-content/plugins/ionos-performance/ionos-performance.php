<?php
/**
 * Plugin Name:  IONOS Performance
 * Plugin URI:   https://www.ionos.com
 * Description:  IONOS Performance uses a cache to store HTML content generated by WordPress temporarily. You can use this cache to improve your website’s performance without the need for cumbersome configuration simply by activating this plugin.
 * Version:      1.2.3
 * License:      GPLv2 or later
 * Author:       IONOS
 * Author URI:   https://www.ionos.com
 * Text Domain:  ionos-performance
 */

/*
Copyright 2022 IONOS
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Online: http://www.gnu.org/licenses/gpl.txt
*/

namespace Ionos\Performance;

use Ionos\Performance\Config;
use Ionos\Performance\Menu;
use Ionos\Performance\Options;
use Ionos\Performance\Updater;
use Ionos\Performance\Warning;

require 'vendor/autoload.php';

define( 'IONOS_PERFORMANCE_FILE', __FILE__ );
define( 'IONOS_PERFORMANCE_DIR', dirname( __FILE__ ) );
define( 'IONOS_PERFORMANCE_BASE', plugin_basename( __FILE__ ) );
define( 'IONOS_PERFORMANCE_CACHE_DIR', WP_CONTENT_DIR . '/cache/ionos-performance' );

/**
 * Init plugin.
 *
 * @return void
 */
function init() {
	Options::set_tenant_and_plugin_name( 'ionos', 'performance' );
	new Updater();
	new Warning( 'ionos-performance' );
	$htaccess = new Htaccess();
	$htaccess->maybe_update();

	if ( ! Config::get( 'features.enabled' ) ) {
		return;
	}

	$htaccess->handle_plugin_changes();

	if ( get_option( 'ionos_performance_show_activation_admin_notice' ) ) {
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_script(
				'ionos_dismissible_notice',
				Helper::get_js_url('admin-notice-dismissible.js' ),
				array( 'jquery', 'common' ),
				filemtime( IONOS_PERFORMANCE_DIR . '/js/admin-notice-dismissible.js'),
				true
			);
		});

		add_action( 'wp_ajax_ionos_perfomance_dismiss_admin_notice', __NAMESPACE__ . '\dismiss_admin_notice' );

		add_action(
			'admin_notices',
			function() {
				global $current_screen;
				if ( 'dashboard' !== $current_screen->base ) {
					return;
				}

				printf(
					'<div data-dismissible="ionos_performance_show_activation_admin_notice" class="updated notice notice-info is-dismissible"><p>%s</p><p><a href="%s">%s</a></p></div>',
					__( 'We have installed the new IONOS Performance plugin for you. If you don’t use a caching plugin so far, you can significantly improve the loading time of your website with a few clicks.', 'ionos-performance' ),
					admin_url( 'admin.php?page=ionos_performance' ),
					__( 'Learn more', 'ionos-performance' )
				);
			}
		);
	}

	if ( Helper::has_conflicting_caching_plugins() ) {
		add_action(
			'admin_notices',
			function() {
				global $current_screen;
				if ( 'ionos_page_ionos_performance' !== $current_screen->base ) {
					return;
				}

				$message = __( 'IONOS Performance is not compatible with other caching plugins.', 'ionos-performance' );
				if ( Manager::get_option( 'caching_enabled' ) ) {
					$message = __( 'IONOS Performance has been disabled because it conflicts with another caching plugin.', 'ionos-performance' );
				}

				printf(
					'<div class="notice notice-info"><p>%s</p></div>',
					$message
				);
			}
		);
	}

	add_action(
		'admin_menu',
		function() {
			Menu::add_submenu_page(
				'Caching',
				'Caching',
				'manage_options',
				'ionos_performance',
				array( '\Ionos\Performance\Manager', 'options_page' ),
				null
			);
		}
	);

	new Caching();
	new Manager();
}

\add_action( 'plugins_loaded', 'Ionos\Performance\init' );

/**
 * Function to dismiss admin notice.
 * Called by ajax methods.
 *
 * @return void
 */
function dismiss_admin_notice() {
	delete_option( 'ionos_performance_show_activation_admin_notice' );
	wp_die();
}

/**
 * Plugin activation routine
 *
 * @return void
 */
function activate() {
	Options::set_tenant_and_plugin_name( 'ionos', 'performance' );
	$htaccess = new Htaccess();
	$htaccess->handle_activation();
}
register_activation_hook( __FILE__, 'Ionos\Performance\activate' );

/**
 * Plugin deactivation routine
 *
 * @return void
 */
function deactivate() {
	Options::set_tenant_and_plugin_name( 'ionos', 'performance' );
	$htaccess = new Htaccess();
	$htaccess->handle_deactivation();
}
register_deactivation_hook( __FILE__, 'Ionos\Performance\deactivate' );

/**
 * Plugin translation.
 *
 * @return void
 */
function load_textdomain() {
	\load_plugin_textdomain(
		'ionos-performance',
		false,
		\dirname( \plugin_basename( __FILE__ ) ) . '/languages/'
	);
}
\add_action( 'init', 'Ionos\Performance\load_textdomain' );
