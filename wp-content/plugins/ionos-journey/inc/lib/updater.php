<?php

namespace Ionos\Journey;

/**
 * Manager class
 */
class Updater {

	/**
	 * Updater constructor.
	 */
	public function __construct() {
		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ), 10, 1 );
		add_filter( 'auto_update_plugin', array( $this, 'force_auto_update' ), 10, 2 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
	}

	/**
	 * Compares the current version with the latest one and, if necessary, issues the info that an update is pending.
	 *
	 * @param  $transient
	 *
	 * @return mixed
	 */
	public function check_update( $transient ) {
		$slug = Options::get_plugin_slug();
		$base_name = "$slug/$slug.php";

		if ( empty( $transient->checked ) || empty( $transient->checked[ $base_name ] ) ) {
			return $transient;
		}

		$data_provider = new Data_Provider\Cloud( 'plugin_info' );
		$update_info = $data_provider->request();

		if ( $this->is_valid_update_info( $update_info ) === false ) {
			error_log( 'Update info isn\'t valid' );
			return null;
		}

		if ( version_compare( $transient->checked[ $base_name ], $update_info['latest_version'] ) == - 1 ) {
			$transient->response[ $base_name ] = ( object ) array(
				'id'            => $base_name,
				'slug'          => $slug,
				'plugin'        => $base_name,
				'new_version'   => $update_info['latest_version'],
				'url'           => 'https://www.ionos.com',
				'package'       => $update_info['download_url'],
				'compatibility' => new \stdClass(),
				'icons'         => Config::get( 'branding.icon_svg' ) ? array( 'svg' => Config::get( 'branding.icon_svg' ) ) : array(),
			);

			if ( isset( $transient->no_update[ $base_name ] ) ) {
				unset( $transient->no_update[ $base_name ] );
			}

		} else {
			$transient->no_update[ $base_name ] = ( object ) array(
				'id'          => $base_name,
				'slug'        => $slug,
				'plugin'      => $base_name,
				'new_version' => $transient->checked[ $base_name ],
			);

			if ( isset( $transient->response[ $base_name ] ) ) {
				unset( $transient->response[ $base_name ] );
			}
		}

		return $transient;
	}

	/**
	 * returns the update information popup
	 *
	 * @param $result
	 * @param string $action
	 * @param $args
	 *
	 * @return false|object
	 */
	public function plugin_popup( $result, string $action, $args ) {
		$slug = Options::get_plugin_slug();

		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( ! empty( $args->slug ) && $args->slug === $slug ) {
			$data_provider = new Data_Provider\Cloud( 'plugin_info' );
			$update_info = $data_provider->request();

			if ( $this->is_valid_update_info( $update_info ) === false ) {
				error_log( 'Update info isn\'t valid' );
				return null;
			}

			if ( is_admin() ) {
				if ( ! function_exists( 'get_plugin_data' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}
				$plugin_data = get_plugin_data( __FILE__ );
			}

			$result = ( object ) array(
				'name'              => $plugin_data['Name'] ?? '',
				'slug'              => $args->slug,
				'requires'          => $update_info['requires_wp'] ?? $args->wp_version,
				'tested'            => $update_info['tested_to'] ?? $args->wp_version,
				'icons'             => Config::get( 'branding.icon_svg' ) ? array( 'svg' => Config::get( 'branding.icon_svg' ) ) : array(),
				'version'           => $update_info['latest_version'],
				'last_updated'      => $update_info['last_updated'],
				'homepage'          => $plugin_data['Homepage'] ?? '',
				'short_description' => $plugin_data['Description'] ?? '',
				'sections'          => array(
					'Changelog' => $this->render_changelog( $update_info['changelog'] ),
				),
				'download_link'     => $update_info['download_url'],
			);
		}

		return $result;
	}

	/**
	 * return changelog html
	 *
	 * @param  array  $changelog
	 *
	 * @return string
	 */
	public function render_changelog( array $changelog ) {
		$result = '';

		if ( is_array( $changelog ) ) {
			foreach ( $changelog as $version ) {
				if ( isset( $version['version'] ) ) {
					$result .= '<h4>' . $version['version'] . '</h4>';
					if ( isset( $version['changes'] ) && is_array( $version['changes'] ) ) {
						$result .= '<ul>';
						foreach ( $version['changes'] as $change ) {
							$result .= '<li>' . $change . '</li>';
						}
						$result .= '</ul>';
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Force auto update
	 *
	 * @param $update
	 * @param $item
	 *
	 * @return bool
	 */
	public function force_auto_update( $update, $item ) {
		if ( $item->slug == Options::get_plugin_slug() ) {
			return true;
		} else {
			return $update;
		}
	}

	/**
	 * Validate info
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	private function is_valid_update_info( $data ) {
		return is_array( $data )
		       && array_key_exists( 'icons', $data )
		       && array_key_exists( 'changelog', $data )
		       && array_key_exists( 'download_url', $data ) && is_string( $data['download_url'] )
		       && array_key_exists( 'latest_version', $data ) && is_string( $data['latest_version'] )
		       && array_key_exists( 'last_updated', $data );
	}
}

