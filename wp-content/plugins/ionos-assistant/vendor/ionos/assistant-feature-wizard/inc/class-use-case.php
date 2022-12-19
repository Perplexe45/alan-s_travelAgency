<?php

namespace Assistant\Wizard;

class Use_Case {
	protected $config;

	public function __construct( $config_part ) {
		$this->config = $config_part;
	}

	public function get_themes() {
		if ( ! isset( $this->config['themes'] ) ) {
			return array();
		}

		return Market_Helper::filter_assets_by_market( $this->config['themes'] );
	}

	public function get_required_plugins() {
		if ( ! isset( $this->config['plugins']['required'] ) ) {
			return array();
		}

		return Market_Helper::filter_assets_by_market( $this->config['plugins']['required'] );
	}

	public function get_recommended_plugins() {
		if ( ! isset( $this->config['plugins']['recommended'] ) ) {
			return array();
		}

		return Market_Helper::filter_assets_by_market( $this->config['plugins']['recommended'] );
	}
}