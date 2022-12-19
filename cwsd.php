<?php

/**
 * The plugin loader file
 *
 * @link              https://cobweb-security.com
 * @since             1.0.1
 * @package           Cwsd
 */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Include the plugin router file. */
@require_once(ABSPATH . 'wp-content/plugins/cwis-antivirus-malware-detected/cwsd-router.php');