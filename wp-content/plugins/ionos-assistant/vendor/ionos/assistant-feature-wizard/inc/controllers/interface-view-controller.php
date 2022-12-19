<?php

namespace Assistant\Wizard\Controllers;

interface View_Controller {
	public static function get_page_title();
	public static function validate_request_params();
	public static function setup();
	public static function render();
}
