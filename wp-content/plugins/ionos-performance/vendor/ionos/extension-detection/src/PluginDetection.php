<?php

namespace Ionos\PluginDetection;

class PluginDetection {
	/**
	 * @param array $plugins Array of plugins to check.
	 * @return bool
	 */
	public static function has_active( $plugins ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}
		return false;
	}
}
