<?php

namespace Assistant\Wizard;

class Market_Helper {
	public static function filter_assets_by_market( $assets ) {
		$market = get_option( 'ionos_market', null );
		if ( $market === null ) {
			return $assets;
		}

		$result = array();
		foreach ( $assets as $key => $asset ) {
			if ( isset( $asset['market'] ) && $asset['market'] !== $market ) {
				continue;
			}

			$result[ $key ] = $asset;
		}

		return $result;
	}
}