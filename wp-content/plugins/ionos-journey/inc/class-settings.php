<?php
namespace Ionos\Journey;


use function __;
use function add_action;
use function add_settings_field;
use function add_settings_section;
use function checked;
use function defined;
use function get_option;
use function is_admin;
use function register_setting;
use function sprintf;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Settings class.
 */
class Settings {
}
