<?php

namespace Ionos\Performance\Data_Provider;

use Ionos\Performance\Config;
use Ionos\Performance\Options;

/**
 * Cache class
 */
class Cloud {

	/**
	 * Expiration time for the data cache (transient)
	 */
	const CACHE_EXPIRE = '-3 hour';

	/**
	 * @var string
	 */
	private $service;

	/**
	 * @var string
	 */
	private $service_urls = array(
		'config'      => 'https://s3-de-central.profitbricks.com/web-hosting/{tenant}/{environment}/config/{plugin}/{mode}/config.json',
		'plugin_info' => 'https://s3-de-central.profitbricks.com/web-hosting/{tenant}/{environment}/{tenant}-{plugin}.info.json',
	);

	/**
	 * Cloud_Data_Provider constructor.
	 *
	 * @param string       $service
	 * @param array|null $service_urls
	 */
	public function __construct( $service, $service_urls = null ) {
		if ( is_array( $service_urls ) ) {
			$this->service_urls = $service_urls;
		}
		if ( array_key_exists( $service, $this->service_urls ) ) {
			$this->set_service( $service );
		}
	}

	/**
	 * Select the service to call
	 *
	 * @param string $service
	 */
	public function set_service( string $service ) {
		$this->service = $service;
	}

	/**
	 * Retrieve or build the complete data object retrieved from service
	 * - is the data older than 3 hours?
	 *    - if yes, has the file changed?
	 *       - if yes, overwrite current data and timestamp
	 *       - if no, keep the current data from transient
	 *    - if no, overwrite current timestamp and keep current data
	 * - the data doesn't exist? create the transient with new data and timestamp
	 *
	 * @return array
	 */
	public function request() {
		if ( is_null( $this->service ) ) {
			return array();
		}

		$date = new \DateTimeImmutable();
		$transient_name = Options::get_tenant_name() . '_' . Options::get_plugin_name() . '_' . $this->service;
		$transient = get_transient( $transient_name );

		// Transient exists / is well formed
		if ( isset( $transient['last_checked'] ) && is_array( $transient['data'] ) ) {

			// Transient is out of date
			if ( $date->modify( self::CACHE_EXPIRE )->getTimestamp() > $transient['last_checked'] ) {

				// Remote data is still up to date
				if ( $this->get_remote_etag() === md5( json_encode( $transient['data'] ) ) ) {
					$transient['last_checked'] = $date->getTimestamp();

				// Remote data has changed
				} else {
					// If new data was successfully retrieved, set it and reset timestamp
					if ( is_array( $data = $this->get_remote_data() ) ) {
						$transient = array(
							'data'         => $data,
							'last_checked' => $date->getTimestamp()
						);
					}
				}
			}

		// Transient doesn't exist / is malformed
		} elseif ( is_array( $data = $this->get_remote_data() ) ) {
			$transient = array(
				'data'         => $data,
				'last_checked' => $date->getTimestamp()
			);

		// Transient doesn't exist / is malformed AND configuration wasn't successfully retrieved
		} else {
			$transient = array(
				'data'         => array(),
				'last_checked' => $date->modify( self::CACHE_EXPIRE )->modify( '+5 minute' )->getTimestamp()
			);
		}

		set_transient( $transient_name, $transient );

		return $transient['data'];
	}

	/**
	 * Build the service URL
	 *
	 * @return string
	 */
	public function build_url() {
		$url = $this->service_urls[ $this->service ];

		/**
		 * Filters the service URL before placeholders are replaced.
		 */
		$url = apply_filters(
			'ionos_library_service_url_before_placeholder_replacement',
			$url,
			$this->service,
			Options::get_tenant_name(),
			Options::get_plugin_name()
		);

		$url = str_replace( '{tenant}', Options::get_tenant_name(), $url );
		$url = str_replace( '{environment}', $this->get_environment(), $url );
		$url = str_replace( '{plugin}', Options::get_plugin_name(), $url );
		$url = str_replace( '{mode}', Options::get_installation_mode(), $url );

		/**
		 * Filters the service URL after placeholders are replaced.
		 */
		$url = apply_filters(
			'ionos_library_service_url_after_placeholder_replacement',
			$url,
			$this->service,
			Options::get_tenant_name(),
			Options::get_plugin_name()
		);

		return $url;
	}

	/**
	 * Get the remote data
	 *
	 * @return array|bool
	 */
	private function get_remote_data() {
		$response = wp_remote_get( $this->build_url() );

		if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
			if ( $body = wp_remote_retrieve_body( $response ) ) {
				$data = json_decode( strip_tags( $body ), true );
				return is_array( $data ) ? $data : false;
			}
		}

		return false;
	}

	/**
	 * Request ETag hash from our remote data
	 *
	 * @return string|bool
	 */
	private function get_remote_etag() {
		$response = wp_remote_head( $this->build_url() );

		if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
			return wp_remote_retrieve_header( $response, 'etag' );
		}

		return false;
	}

	/**
	 * Return the environment of the WordPress installation
	 *
	 * @return string
	 */
	private function get_environment() {
		if ( ! function_exists( 'wp_get_environment_type' ) ) {
			return 'live';
		}

		switch ( wp_get_environment_type() ) {
			case 'local':
			case 'development':
				return 'test';
				break;

			case 'staging':
				return 'qa';
				break;

			case 'production':
			default:
				return 'live';
		}
	}
}
