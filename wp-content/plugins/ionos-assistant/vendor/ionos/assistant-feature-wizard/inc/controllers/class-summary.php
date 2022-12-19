<?php

namespace Assistant\Wizard\Controllers;

use Ionos\Assistant\Config;
use Assistant\Wizard\Manager;
use Assistant\Wizard\Request_Validator;
use Assistant\Wizard\Theme;
use Assistant\Wizard\Use_Case;
use Assistant\Wizard\Wp_Org_Api;

class Summary implements View_Controller {
	private static $plugin_infos;
	private static $selected_use_case;
	private static $selected_theme;

	public static function render() {
		$optional_plugins        = array();

		if ( ! empty( $_GET[ Manager::STATE_INPUT_NAMES['plugins'] ] ) ) {
			$optional_plugins = $_GET[ Manager::STATE_INPUT_NAMES['plugins'] ];
		}

        if ( isset( $_GET['install_promoted'] ) ) {
            if ( Plugin_Advertising::validate_promoted_plugin() ) {
				$promoted_plugin = Config::get( 'features.wizard.promotedPlugin' );
                $optional_plugins = array_merge( $optional_plugins, array( array_key_first( $promoted_plugin ) ) );
            }
        }

        $template_array = array (
            'counter_text'     => __( 'Step 2 of 2', 'ionos-assistant' ),
            'heading_text'     => __( 'Abort Summary', 'ionos-assistant' ),
            'next_step'        => 'install',
            'plugins'          => $optional_plugins,
            'plugin_infos'     => self::$plugin_infos,
        );

        $plugin_slugs = $optional_plugins;
        $template_view = ASSISTANT_WIZARD_VIEWS_DIR . '/abort-summary.php';

        if ( false === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) ) {
            self::$selected_use_case = $_GET[Manager::STATE_INPUT_NAMES['use_case']];
            self::$selected_theme = $_GET[Manager::STATE_INPUT_NAMES['theme']];

            $info = Wp_Org_Api::get_theme_infos(array(self::$selected_theme));
            if (empty($info)) {
                return;
            }

            $use_case_info = Config::get('features.wizard.usecases.' . self::$selected_use_case);
            $theme_info = $use_case_info['themes'][self::$selected_theme];

			$use_case = new Use_Case( $use_case_info );
			$theme = new Theme( $theme_info );

			$required_plugins = array_merge(
				$use_case->get_required_plugins(),
				$theme->get_required_plugins()
			);

            $plugin_slugs = array_merge(array_keys($required_plugins), $optional_plugins);

            $template_array = array(
                'counter_text' => __('Step 5 of 5', 'ionos-assistant'),
                'heading_text' => __('Summary', 'ionos-assistant'),
                'next_step' => 'install',
                'use_case' => self::$selected_use_case,
                'theme' => self::$selected_theme,
                'info' => $info[self::$selected_theme],
                'plugins' => $optional_plugins,
                'required_plugins' => $required_plugins,
                'preview_link' => 'https://wp-themes.com/' . self::$selected_theme,
                'plugin_infos' => self::$plugin_infos,
            );

            $template_view = ASSISTANT_WIZARD_VIEWS_DIR . '/summary.php';
        }

		self::$plugin_infos = Wp_Org_Api::get_plugin_infos( $plugin_slugs );

		load_template(
			$template_view,
			true,
			$template_array
		);
	}

	public static function validate_request_params() {
        if ( true === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) ) {
            return true;
        }

		return Request_Validator::validate( array( 'use_case', 'theme' ) );
	}

	public static function get_page_title() {
		return __( 'Summary', 'ionos-assistant' );
	}

	public static function get_plugin_name( $slug ) {
        if ( !is_array($slug) ) {
            $paths = array();

            if ( false === (bool) get_transient( Abort_Plugin_Selection::ABORT_SCREEN_TRANSIENT_NAME ) ) {

                $use_case = self::$selected_use_case;
                $theme = self::$selected_theme;
                $paths = array(
                    "features.wizard.usecases.$use_case.plugins.recommended.$slug.name",
                    "features.wizard.usecases.$use_case.plugins.required.$slug.name",
                    "features.wizard.usecases.$use_case.themes.$theme.plugins.recommended.$slug.name",
                    "features.wizard.usecases.$use_case.themes.$theme.plugins.required.$slug.name",
                );
            } else {
                $paths = array(
                    "features.wizard.plugins.$slug.name",
                );
            }

            foreach ($paths as $path) {
                $name = Config::get($path);
                if ($name) {
                    return $name;
                }
            }

            if (isset(self::$plugin_infos[$slug]['name'])) {
                return self::$plugin_infos[$slug]['name'];
            }

            return '';
        }

        return $slug['name'];
	}

	public static function setup() {
		// TODO: Implement setup() method.
	}
}
