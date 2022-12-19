<?php

namespace Ionos\Performance;

/**
 * Config singleton
 */
class Config {

	/**
	 * @var Config
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var
	 */
	private $data_provider;

	/**
	 * Create Singleton object
	 *
	 * @param Data_Provider\Cloud $data_provider
	 *
	 * @return Config
	 */
	public static function get_instance( Data_Provider\Cloud $data_provider = null ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $data_provider );
		}

		return self::$instance;
	}

	/**
	 * Delete Singleton object
	 */
	public static function delete_instance()
	{
		self::$instance = null;
	}

	/**
	 * Singleton wrapper function to retrieve a specific parameter without much code
	 * Call: Config::get()
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function get( string $path ) {
		return self::get_instance()->get_parameter( $path );
	}

	/**
	 * Config constructor.
	 *
	 * @param Data_Provider\Cloud|null $data_provider
	 */
	private function __construct( $data_provider = null ) {
		if ( ! $data_provider instanceof Data_Provider\Cloud ) {
			$this->data_provider = new Data_Provider\Cloud( 'config' );
		} else {
			$this->data_provider = $data_provider;
		}
		$this->config = $this->data_provider->request();
	}

	/**
	 * Returns specific plugin configuration element
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function get_parameter( string $path ) {

		// Any configuration parameter can be overridden with a WP Option
		$option_key = strtolower( Options::get_tenant_name() )
			. '_' . str_replace( '-', '_', Options::get_plugin_name() )
			. '_' . str_replace( '.', '_', $path
		);
		if ( ( $option = \get_option( $option_key ) ) !== false ) {
			return $option;
		}

		// If no option is set, retrieve parameter from config object
		$element = $this->config;
		foreach ( explode(  '.', $path ) as $key ) {
			if ( is_array( $element ) && array_key_exists( $key, $element ) ) {
				$element = $element[$key];
			} else {
				return false;
			}
		}

		return $element;
	}
}