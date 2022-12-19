<?php

namespace Assistant\Wizard\Controllers;

use Assistant\Wizard\Manager;
use Assistant\Wizard\Request_Validator;
use Ionos\Assistant\Config;
use Assistant\Wizard\Theme;
use Assistant\Wizard\Use_Case;
use Assistant\Wizard\Wp_Org_Api;

class Plugin_Selection implements View_Controller {

	private static $selected_use_case;
	private static $selected_theme;
	private static $use_case_info;
	private static $plugin_infos;

	public static function render() {
		self::$selected_use_case = $_GET[ Manager::STATE_INPUT_NAMES['use_case'] ];
		self::$selected_theme    = $_GET[ Manager::STATE_INPUT_NAMES['theme'] ];

		self::$use_case_info = Config::get( 'features.wizard.usecases.' . self::$selected_use_case );
		$theme_info          = self::$use_case_info['themes'][ self::$selected_theme ];

		$use_case = new Use_Case( self::$use_case_info );
		$theme = new Theme( $theme_info );

		$required_plugins = array_merge(
			$use_case->get_required_plugins(),
			$theme->get_required_plugins()
		);

		$optional_plugins = array_merge(
			$use_case->get_recommended_plugins(),
			$theme->get_recommended_plugins()
		);

		$plugin_slugs       = array_merge( array_keys( $required_plugins ), array_keys( $optional_plugins ) );
		self::$plugin_infos = Wp_Org_Api::get_plugin_infos( $plugin_slugs );

		$next_step = Plugin_Advertising::validate_promoted_plugin() ? 'plugin-advertising' : 'summary';

		load_template(
			ASSISTANT_WIZARD_VIEWS_DIR . '/plugin-selection.php',
			true,
			array(
				'counter_text'     => __( 'Step 4 of 5', 'ionos-assistant' ),
				'heading_text'     => __( 'Pick some plugins', 'ionos-assistant' ),
				'next_step'        => $next_step,
				'theme'            => self::$selected_theme,
				'use_case'         => self::$selected_use_case,
				'required_plugins' => $required_plugins,
				'optional_plugins' => $optional_plugins,
				'plugin_infos'     => self::$plugin_infos,
				'selected_plugins' => isset( $_GET[ Manager::STATE_INPUT_NAMES['plugins'] ] ) ? $_GET[ Manager::STATE_INPUT_NAMES['plugins'] ] : array(),
			)
		);
	}

	public static function validate_request_params() {
		return Request_Validator::validate( array( 'use_case', 'theme' ) );
	}

	public static function get_plugin_description( $slug ) {
		$use_case = self::$selected_use_case;
		$theme    = self::$selected_theme;
		$paths    = array(
			"features.wizard.usecases.$use_case.plugins.recommended.$slug.description",
			"features.wizard.usecases.$use_case.plugins.required.$slug.description",
			"features.wizard.usecases.$use_case.themes.$theme.plugins.recommended.$slug.description",
			"features.wizard.usecases.$use_case.themes.$theme.plugins.required.$slug.description",
		);

		foreach ( $paths as $path ) {
			$description = Config::get( $path );
			if ( $description ) {
				return $description;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['short_description'] ) ) {
			return self::$plugin_infos[ $slug ]['short_description'];
		}

		return '';
	}

	public static function get_plugin_icon_url( $slug ) {
		$use_case = self::$selected_use_case;
		$theme    = self::$selected_theme;
		$paths    = array(
			"features.wizard.usecases.$use_case.plugins.recommended.$slug.icon_url",
			"features.wizard.usecases.$use_case.plugins.required.$slug.icon_url",
			"features.wizard.usecases.$use_case.themes.$theme.plugins.recommended.$slug.icon_url",
			"features.wizard.usecases.$use_case.themes.$theme.plugins.required.$slug.icon_url",
		);

		foreach ( $paths as $path ) {
			$icon_url = Config::get( $path );
			if ( $icon_url ) {
				return $icon_url;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['icons']['1x'] ) ) {
			return self::$plugin_infos[ $slug ]['icons']['1x'];
		}

		return '';
	}

	public static function get_plugin_name( $slug ) {
		$use_case = self::$selected_use_case;
		$theme    = self::$selected_theme;
		$paths    = array(
			"features.wizard.usecases.$use_case.plugins.recommended.$slug.name",
			"features.wizard.usecases.$use_case.plugins.required.$slug.name",
			"features.wizard.usecases.$use_case.themes.$theme.plugins.recommended.$slug.name",
			"features.wizard.usecases.$use_case.themes.$theme.plugins.required.$slug.name",
		);

		foreach ( $paths as $path ) {
			$name = Config::get( $path );
			if ( $name ) {
				return $name;
			}
		}

		if ( isset( self::$plugin_infos[ $slug ]['name'] ) ) {
			return self::$plugin_infos[ $slug ]['name'];
		}

		return '';
	}

	public static function get_page_title() {
		return __( 'Plugin selection', 'ionos-assistant' );
	}

	public static function setup() {
		// TODO: Implement setup() method.
	}


	public static function validate_image_url( $url ) {
		// TODO: Implement validate_image_url() for images in plugin selection with status code 403/404

	}
}
