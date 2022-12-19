<?php

namespace Assistant\AutoUpdate;

use Ionos\Assistant\Config;

class Manager {
	const HINT_TEMPLATE = '<p><span class="ionos-auto-update">%s</span></p>';

	public static function init() {
		if ( Config::get( 'features.autoUpdate.enabled' ) ) {
			// Enable WordPress to automatically update plugins and themes to the newest version
			// https://make.wordpress.org/core/2020/07/15/controlling-plugin-and-theme-auto-updates-ui-in-wordpress-5-5/
			add_filter( 'auto_update_plugin', '__return_true' );
			add_filter( 'auto_update_theme', '__return_true' );

			// Modify the auto update hints shown for plugins and themes
			if ( Config::get( 'features.autoUpdate.modifyHints' ) ) {
				add_filter( 'plugin_auto_update_setting_html', array( __CLASS__, 'modify_auto_update_plugin_setting' ), 10, 0 );
				add_filter( 'theme_auto_update_setting_template', array( __CLASS__, 'modify_auto_update_theme_setting' ), 10, 0 );
				add_filter( 'theme_auto_update_setting_html', array( __CLASS__, 'modify_auto_update_theme_setting' ), 10, 0 );
			}
		}
	}

	/**
	 * Inform the user of the enforced plugins auto-update process
	 */
	public static function modify_auto_update_theme_setting() {
		return sprintf(
			self::HINT_TEMPLATE,
			apply_filters( 'ionos_auto_update_hint_theme', __( 'Permanently active', 'ionos-assistant' ) )
		);
	}

	/**
	 * Inform the user of the enforced themes auto-update process
	 */
	public static function modify_auto_update_plugin_setting() {
		return sprintf(
			self::HINT_TEMPLATE,
			apply_filters( 'ionos_auto_update_hint_plugin', __( 'Permanently active', 'ionos-assistant' ) )
		);
	}
}
