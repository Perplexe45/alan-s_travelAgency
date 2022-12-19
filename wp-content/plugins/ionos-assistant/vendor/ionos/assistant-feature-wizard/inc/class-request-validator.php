<?php

namespace Assistant\Wizard;

use Ionos\Assistant\Config;

class Request_Validator {

	public static function validate( $to_validate ) {
		foreach ( $to_validate as $param_key ) {
			if ( ! isset( $_GET[ $param_key ] ) ) {
				return false;
			}

			switch ( $param_key ) {
				case 'use_case':
					$is_valid = self::validate_use_case();
					break;
				case 'theme':
					$is_valid = self::validate_theme();
					break;
				case 'plugins':
					$is_valid = self::validate_plugins();
					break;
				default:
					$is_valid = false;
			}

			if ( ! $is_valid ) {
				return false;
			}
		}

		return true;
	}

	private static function validate_use_case() {
		$use_case = $_GET['use_case'];
		return false !== Config::get( "features.wizard.usecases.$use_case" );
	}

	private static function validate_theme() {
		$use_case = $_GET['use_case'];
		$theme    = $_GET['theme'];
		return false !== Config::get( "features.wizard.usecases.$use_case.themes.$theme" );
	}

	private static function validate_plugins() {
		$plugins  = $_GET['plugins'];
		$use_case = $_GET['use_case'];
		$theme    = $_GET['theme'];

		if ( ! is_array( $plugins ) || empty( $plugins ) ) {
			return false;
		}

		foreach ( $plugins as $slug ) {
			$paths = array(
				"features.wizard.usecases.$use_case.plugins.recommended.$slug",
				"features.wizard.usecases.$use_case.plugins.required.$slug",
				"features.wizard.usecases.$use_case.themes.$theme.plugins.recommended.$slug",
				"features.wizard.usecases.$use_case.themes.$theme.plugins.required.$slug",
			);

			$is_valid = false;

			foreach ( $paths as $path ) {

				if ( false !== Config::get( $path ) ) {
					$is_valid = true;
					break;
				}
			}

			if ( ! $is_valid ) {
				return false;
			}
		}

		return true;
	}
}
