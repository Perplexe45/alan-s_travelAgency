<?php

namespace Assistant\JetpackBackupFlow\Controllers;

interface ViewController {
	public static function get_page_title();
	public static function setup();
	public static function render();
}
