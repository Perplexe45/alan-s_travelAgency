<?php
/**
 * Plugin Name:  IONOS Assistant
 * Plugin URI:   https://www.ionos.com
 * Description:  IONOS Assistant will help you complete the first setup of your WordPress in quick and easy steps. It will help you find a theme to start with and add some plugins that will help you with the purpose of your WordPress installation.
 * Version:      8.4.2
 * License:      GPL-2.0-or-later
 * Author:       IONOS
 * Author URI:   https://www.ionos.com
 * Text Domain:  ionos-assistant
 * Domain Path:  /languages
 */

/*
Copyright 2020 IONOS by 1&1
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


namespace Ionos\Assistant;

use Ionos\Assistant\Options;
use Ionos\Assistant\Config;
use Ionos\Assistant\Warning;
use Ionos\Assistant\Updater;

define( 'IONOS_ASSISTANT_FILE', __FILE__ );
define( 'IONOS_ASSISTANT_DIR', __DIR__ );
define( 'IONOS_ASSISTANT_BASE', plugin_basename( __FILE__ ) );

$autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoloader ) ) {
	require_once $autoloader;
}

/**
 * Init plugin.
 *
 * @return void
 */
function init() {
	Options::set_tenant_and_plugin_name( 'ionos', 'assistant' );

	// Change the source of the config to get the right version for v8
	add_filter(
		'ionos_library_service_url_before_placeholder_replacement',
		function( $url, $service, $tenant, $plugin ) {
			if ( 'config' !== $service || 'assistant' !== $plugin ) {
				return $url;
			}

			$url = str_replace( 'config.json', 'config-v8.json', $url );
			return $url;
		},
		10,
		4
	);

	new Updater();
	new Warning( 'ionos-assistant' );

	add_action( 'admin_init', function() {
		if ( false === Config::get( 'features.wizard' ) ) {
			delete_transient( 'ionos_assistant_config' );
		}
	} );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Plugin translation.
 *
 * @return void
 */
function load_textdomain() {
	load_plugin_textdomain(
		'ionos-assistant',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/'
	);
}
add_action( 'init', __NAMESPACE__ . '\load_textdomain' );
