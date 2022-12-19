<?php

namespace Assistant\Banner;

use Ionos\Assistant\Config;
use Ionos\Assistant\Options;

class Manager {
	public function init() {
		Options::set_tenant_and_plugin_name( 'ionos', 'assistant' );
		if ( ! Config::get( 'features.banner.enabled' ) ) {
			return;
		}

		Branding::setup_branding_info();

		add_action( 'in_admin_header', array( __CLASS__, 'render_panels' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function render_panels() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$admin_screen = get_current_screen()->id;
		if ( ! Config::get( "features.banner.$admin_screen.enabled" ) ) {
			// Todo: Add a `return` after the config was adapted.
		}

		switch ( $admin_screen ) {
			case 'dashboard':
				// Remove core banner.
				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				add_meta_box(
					'wp_welcome_panel',
					__( 'IONOS WordPress', 'ionos-assistant' ),
					function() {},
					'dashboard'
				);
				add_action(
					'welcome_panel',
					function() {
						self::render_template( 'dashboard' );
					}
				);
				add_filter(
					'hidden_meta_boxes',
					function( $meta_boxes ) {
						$option = (int) get_user_meta( get_current_user_id(), 'show_welcome_panel', true );
						$hide = ( 0 === $option || ( 2 === $option && wp_get_current_user()->user_email !== get_option( 'admin_email' ) ) );
						if ( $hide ) {
							$meta_boxes[] = 'wp_welcome_panel';
						}
						return $meta_boxes;
					}
				);
				break;
		}
	}

	private static function render_template( $admin_screen ) {
		$assistant_banner_dir = untrailingslashit( ASSISTANT_BANNER_DIR );
		if ( ! file_exists( "$assistant_banner_dir/views/$admin_screen.php" ) ) {
			return;
		}

		$args = array();

		if ( 'dashboard' === $admin_screen ) {
			$blog_url = Config::get( 'links.blog_' . Options::get_market() );
			if ( ! $blog_url ) {
				$blog_url = Config::get( 'links.blog_US' );
			}
			$args['blog_url']           = $blog_url;
			$args['cp_application_url'] = Config::get( 'links.control_panel_applications_' . Options::get_market() );
			$args['cp_emails_url']      = ! empty( $is_product_domain ) && true === $is_product_domain ? null : Manager::get_manage_email_link();

			$args['logo_src']     = Branding::get_logo();
			$args['logo_alt']     = sprintf( __( 'by %s' ), Branding::get_name() );
			$args['visual']       = Branding::get_visual( 1 );
			$args['journey_link'] = '';
			if ( is_plugin_active( 'ionos-journey/ionos-journey.php' ) ) {
				$args['journey_link'] = add_query_arg(
					array(
						'wp_tour' => 'started',
					),
					get_admin_url()
				);
			}

			$args['is_product_domain'] = self::is_product_domain();
		}

		load_template( "$assistant_banner_dir/views/$admin_screen.php", true, $args );
	}

	public static function get_root_tenant_name() {
		return str_replace( '.', '', 'i.o.n.o.s' );
	}

	public static function get_manage_email_link() {
		if ( self::get_root_tenant_name() !== Options::get_tenant_name() ) {
			return null;
		}

		$market             = Options::get_market();
		$market_email_datas = array(
			'DE' => array(
				'host'        => 'mein.ionos.de',
				'action_code' => 'OM.BW.BW263K422193T7073a',
			),
			'UK' => array(
				'host'        => 'my.ionos.co.uk',
				'action_code' => 'OM.CE.CE263K422194T7073a',
			),
			'FR' => array(
				'host'        => 'my.ionos.fr',
				'action_code' => 'OM.CF.CF263K422195T7073a',
			),
			'ES' => array(
				'host'        => 'my.ionos.es',
				'action_code' => 'OM.CS.CS263K422196T7073a',
			),
			'IT' => array(
				'host'        => 'my.ionos.it',
				'action_code' => 'OM.CI.CI263K422197T7073a',
			),
			'US' => array(
				'host'        => 'my.ionos.com',
				'action_code' => 'OM.CU.CU263K422198T7073a',
			),
			'MX' => array(
				'host'        => 'my.ionos.mx',
				'action_code' => 'OM.CM.CM263K422199T7073a',
			),
			'CA' => array(
				'host'        => 'my.ionos.ca',
				'action_code' => 'OM.CC.CC263K422200T7073a',
			),
		);

		if ( ! isset( $market_email_datas[ $market ] ) ) {
			return null;
		}
		$market_email_data = $market_email_datas[ $market ];

		return sprintf(
			'https://%1$s/select-contract-silent?contract-selection-target=email-package-selection&contract-selection-domain=%3$s&contract-selection-nodata=&domain=%3$s&mail-business-ac=%2$s&mail-basic-ac=%2$s&utm_source=wordpress&utm_campaign=wp-email-create',
			$market_email_data['host'],
			$market_email_data['action_code'],
			parse_url( get_site_url(), PHP_URL_HOST )
		);
	}

	public static function enqueue_assets() {
		if ( 'dashboard' === get_current_screen()->id ) {
			wp_enqueue_style(
				'assistant-banner-style',
				plugins_url( 'css/dashboard.css', ASSISTANT_BANNER_FILE ),
				array(),
				filemtime( plugin_dir_path( ASSISTANT_BANNER_FILE ) . 'css/dashboard.css' )
			);

			wp_enqueue_script(
				'assistant-banner-script',
				plugins_url( 'js/dashboard.js', ASSISTANT_BANNER_FILE ),
				array(),
				filemtime( plugin_dir_path( ASSISTANT_BANNER_FILE ) . 'js/dashboard.js' ),
				true
			);

			wp_localize_script(
				'assistant-banner-script',
				'assistantLocalizeObj',
				array(
					'closeLinkLabel' => __( 'Close IONOS WordPress panel', 'ionos-assistant' ),
				)
			);
		}
	}

	/**
	 * Check if the current WP Domain is a product domain
	 * (If yes a link will be shown to redirect the user to the Control Panel, where a new domain can be assigned)
	 *
	 * @return boolean
	 */
	private static function is_product_domain() {
		$product_domains = array(
			'.apps-1and1.net',
			'.apps-1and1.com',
			'.online.de',
			'.live-website.com',
		);
		$domain          = get_site_url();
		foreach ( $product_domains as $product_domain ) {
			if ( stripos( $domain, $product_domain ) !== false ) {
				return true;
			}
		}
		return false;
	}
}
