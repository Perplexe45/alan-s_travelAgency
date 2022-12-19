<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie_Notice_Frontend class.
 *
 * @class Cookie_Notice_Frontend
 */
class Cookie_Notice_Frontend {

	private $is_bot = false;
	private $hide_banner = false;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// actions
		// add_action( 'plugins_loaded', [ $this, 'init_modules' ] );
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize frontend.
	 *
	 * @global string $pagenow
	 *
	 * @return void
	 */
	public function init() {
		// purge cache
		if ( isset( $_GET['hu_purge_cache'] ) )
			$this->purge_cache();

		// get main instance
		$cn = Cookie_Notice();

		// is it preview mode?
		$this->preview_mode = isset( $_GET['cn_preview_mode'] );

		// is it a bot?
		$this->is_bot = $cn->bot_detect->is_crawler();

		// is user logged in and hiding the banner is enabled
		$this->hide_banner = is_user_logged_in() && $cn->options['general']['hide_banner'] === true;

		global $pagenow;

		// bail if in preview mode or it's a bot request
		if ( ! $this->preview_mode && ! $this->is_bot && ! $this->hide_banner && ! ( is_admin() && $pagenow === 'widgets.php' && isset( $_GET['legacy-widget-preview'] ) ) ) {
			// init cookie compliance
			if ( $cn->get_status() === 'active' ) {
				add_action( 'send_headers', [ $this, 'add_compliance_http_header' ] );
				add_action( 'wp_head', [ $this, 'add_cookie_compliance' ], 0 );

				// autoptimize
				if ( function_exists( 'autoptimize' ) )
					include_once( COOKIE_NOTICE_PATH . 'includes/modules/autoptimize/autoptimize.php' );

				// is blocking active?
				if ( $cn->options['general']['app_blocking'] ) {
					// contact form 7 5.1+ recaptcha v3 compatibility
					if ( class_exists( 'WPCF7' ) && class_exists( 'WPCF7_RECAPTCHA' ) && defined( 'WPCF7_VERSION' ) && version_compare( WPCF7_VERSION, '5.1', '>=' ) )
						include_once( COOKIE_NOTICE_PATH . 'includes/modules/contact-form-7/contact-form-7.php' );
				}
			// init cookie notice
			} else {
				// actions
				add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_notice_scripts' ] );
				add_filter( 'script_loader_tag', [ $this, 'wp_enqueue_script_async' ], 10, 3 );
				add_action( 'wp_head', [ $this, 'wp_print_header_scripts' ] );
				add_action( 'wp_print_footer_scripts', [ $this, 'wp_print_footer_scripts' ] );
				add_action( 'wp_footer', [ $this, 'add_cookie_notice' ], 1000 );

				// filters
				add_filter( 'body_class', [ $this, 'change_body_class' ] );
			}
		}
	}

	/**
	 * Add CORS header for API requests and purge cache.
	 *
	 * @return void
	 */
	public function add_compliance_http_header() {
		header( 'Access-Control-Allow-Origin: ' . Cookie_Notice()->get_url( 'host' ) );
		header( 'Access-Control-Allow-Methods: GET' );
	}

	/**
	 * Run Cookie Compliance.
	 *
	 * @return void
	 */
	public function add_cookie_compliance() {
		// get main instance
		$cn = Cookie_Notice();

		// get site language
		$locale = get_locale();
		$locale_code = explode( '_', $locale );

		// exceptions, norwegian
		if ( in_array( $locale_code, [ 'nb', 'nn' ] ) )
			$locale_code = 'no';

		$options = apply_filters(
			'cn_cookie_compliance_args',
			[
				'appID'				=> $cn->options['general']['app_id'],
				'currentLanguage'	=> $locale_code[0],
				'blocking'			=> ( ! is_user_logged_in() ? (bool) $cn->options['general']['app_blocking'] : false ),
				'globalCookie'		=> ( is_multisite() && $cn->options['general']['global_cookie'] && is_subdomain_install() )
			]
		);

		// debug mode
		if ( $cn->options['general']['debug_mode'] )
			$options['debugMode'] = true;
		
		// custom scripts?
		if ( (bool) $cn->options['general']['app_blocking'] ) { // $options['blocking'] === true
			if ( is_multisite() && $cn->is_network_admin() && $cn->is_plugin_network_active() && $cn->network_options['global_override'] )
				$blocking = get_site_option( 'cookie_notice_app_blocking' );
			else
				$blocking = get_option( 'cookie_notice_app_blocking' );
			
			$providers = ! empty( $blocking[ 'providers'] ) && is_array( $blocking[ 'providers'] ) ? $this->get_custom_items( $blocking[ 'providers'] ) : [];
			
			$patterns = ! empty( $blocking[ 'patterns'] ) && is_array( $blocking[ 'patterns'] ) ? $this->get_custom_items( $blocking[ 'patterns' ] ) : [];
			
			$options['customProviders'] = ! empty( $providers ) ? $providers : [];
			$options['customPatterns'] = ! empty( $patterns ) ? $patterns : [];
		}

		// message output
		$output = '
		<!-- Cookie Compliance -->
		<script type="text/javascript">
			var huOptions = ' . json_encode( $options ) . ';
		</script>
		<script type="text/javascript" src="' . $cn->get_url( 'widget' ) . '"></script>';

		echo apply_filters( 'cn_cookie_compliance_output', $output, $options );
	}

	/**
	 * Cookie notice output.
	 *
	 * @return void
	 */
	public function add_cookie_notice() {
		// get main instance
		$cn = Cookie_Notice();

		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			$cn->options['general']['message_text'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['message_text'], 'Cookie Notice', 'Message in the notice' );
			$cn->options['general']['accept_text'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['accept_text'], 'Cookie Notice', 'Button text' );
			$cn->options['general']['refuse_text'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['refuse_text'], 'Cookie Notice', 'Refuse button text' );
			$cn->options['general']['revoke_message_text'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['revoke_message_text'], 'Cookie Notice', 'Revoke message text' );
			$cn->options['general']['revoke_text'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['revoke_text'], 'Cookie Notice', 'Revoke button text' );
			$cn->options['general']['see_more_opt']['text'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['see_more_opt']['text'], 'Cookie Notice', 'Privacy policy text' );
			$cn->options['general']['see_more_opt']['link'] = apply_filters( 'wpml_translate_single_string', $cn->options['general']['see_more_opt']['link'], 'Cookie Notice', 'Custom link' );
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_t' ) ) {
			$cn->options['general']['message_text'] = icl_t( 'Cookie Notice', 'Message in the notice', $cn->options['general']['message_text'] );
			$cn->options['general']['accept_text'] = icl_t( 'Cookie Notice', 'Button text', $cn->options['general']['accept_text'] );
			$cn->options['general']['refuse_text'] = icl_t( 'Cookie Notice', 'Refuse button text', $cn->options['general']['refuse_text'] );
			$cn->options['general']['revoke_message_text'] = icl_t( 'Cookie Notice', 'Revoke message text', $cn->options['general']['revoke_message_text'] );
			$cn->options['general']['revoke_text'] = icl_t( 'Cookie Notice', 'Revoke button text', $cn->options['general']['revoke_text'] );
			$cn->options['general']['see_more_opt']['text'] = icl_t( 'Cookie Notice', 'Privacy policy text', $cn->options['general']['see_more_opt']['text'] );
			$cn->options['general']['see_more_opt']['link'] = icl_t( 'Cookie Notice', 'Custom link', $cn->options['general']['see_more_opt']['link'] );
		}

		if ( $cn->options['general']['see_more_opt']['link_type'] === 'page' ) {
			// multisite with global override?
			if ( is_multisite() && $cn->is_plugin_network_active() && $cn->network_options['global_override'] ) {
				// get main site id
				$main_site_id = get_main_site_id();

				// switch to main site
				switch_to_blog( $main_site_id );

				// update page id for current language if needed
				if ( function_exists( 'icl_object_id' ) )
					$cn->options['general']['see_more_opt']['id'] = icl_object_id( $cn->options['general']['see_more_opt']['id'], 'page', true );

				// get main site privacy policy link
				$permalink = get_permalink( $cn->options['general']['see_more_opt']['id'] );

				// restore current site
				restore_current_blog();
			} else {
				// update page id for current language if needed
				if ( function_exists( 'icl_object_id' ) )
					$cn->options['general']['see_more_opt']['id'] = icl_object_id( $cn->options['general']['see_more_opt']['id'], 'page', true );

				// get privacy policy link
				$permalink = get_permalink( $cn->options['general']['see_more_opt']['id'] );
			}
		}

		// get cookie container args
		$options = apply_filters( 'cn_cookie_notice_args', [
			'position'				=> $cn->options['general']['position'],
			'css_class'				=> $cn->options['general']['css_class'],
			'button_class'			=> 'cn-button',
			'colors'				=> $cn->options['general']['colors'],
			'message_text'			=> $cn->options['general']['message_text'],
			'accept_text'			=> $cn->options['general']['accept_text'],
			'refuse_text'			=> $cn->options['general']['refuse_text'],
			'revoke_message_text'	=> $cn->options['general']['revoke_message_text'],
			'revoke_text'			=> $cn->options['general']['revoke_text'],
			'refuse_opt'			=> $cn->options['general']['refuse_opt'],
			'revoke_cookies'		=> $cn->options['general']['revoke_cookies'],
			'see_more'				=> $cn->options['general']['see_more'],
			'see_more_opt'			=> $cn->options['general']['see_more_opt'],
			'link_target'			=> $cn->options['general']['link_target'],
			'link_position'			=> $cn->options['general']['link_position'],
			'aria_label'			=> 'Cookie Notice'
		] );

		// check legacy parameters
		$options = $cn->check_legacy_params( $options, [ 'refuse_opt', 'see_more' ] );

		if ( $options['see_more'] === true )
			$options['message_text'] = do_shortcode( wp_kses_post( $options['message_text'] ) );
		else
			$options['message_text'] = wp_kses_post( $options['message_text'] );

		$options['revoke_message_text'] = wp_kses_post( $options['revoke_message_text'] );

		// escape css classes
		$options['css_class'] = esc_attr( $options['css_class'] );
		$options['button_class'] = esc_attr( $options['button_class'] );

		// message output
		$output = '
		<!-- Cookie Notice plugin v' . $cn->defaults['version'] . ' by Hu-manity.co https://hu-manity.co/ -->
		<div id="cookie-notice" role="dialog" class="cookie-notice-hidden cookie-revoke-hidden cn-position-' . esc_attr( $options['position'] ) . '" aria-label="' . esc_attr( $options['aria_label'] ) . '" style="background-color: rgba(' . implode( ',', $cn->hex2rgb( $options['colors']['bar'] ) ) . ',' . ( (int) $options['colors']['bar_opacity'] ) * 0.01 . ');">'
			. '<div class="cookie-notice-container" style="color: ' . esc_attr( $options['colors']['text'] ) . ';">'
			. '<span id="cn-notice-text" class="cn-text-container">'. $options['message_text'] . '</span>'
			. '<span id="cn-notice-buttons" class="cn-buttons-container"><a href="#" id="cn-accept-cookie" data-cookie-set="accept" class="cn-set-cookie ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['accept_text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['accept_text'] ) . '</a>'
			. ( $options['refuse_opt'] === true ? '<a href="#" id="cn-refuse-cookie" data-cookie-set="refuse" class="cn-set-cookie ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['refuse_text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['refuse_text'] ) . '</a>' : '' )
			. ( $options['see_more'] === true && $options['link_position'] === 'banner' ? '<a href="' . ( $options['see_more_opt']['link_type'] === 'custom' ? esc_url( $options['see_more_opt']['link'] ) : esc_url( $permalink ) ) . '" target="' . esc_attr( $options['link_target'] ) . '" id="cn-more-info" class="cn-more-info ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['see_more_opt']['text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['see_more_opt']['text'] ) . '</a>' : '' )
			. '</span><span id="cn-close-notice" data-cookie-set="accept" class="cn-close-icon" title="' . esc_attr( $options['refuse_text'] ) . '"></span>'
			. '</div>
			' . ( $options['refuse_opt'] === true && $options['revoke_cookies'] == true ?
			'<div class="cookie-revoke-container" style="color: ' . esc_attr( $options['colors']['text'] ) . ';">'
			. ( ! empty( $options['revoke_message_text'] ) ? '<span id="cn-revoke-text" class="cn-text-container">' . $options['revoke_message_text'] . '</span>' : '' )
			. '<span id="cn-revoke-buttons" class="cn-buttons-container"><a href="#" class="cn-revoke-cookie ' . $options['button_class'] . ( $options['css_class'] !== '' ? ' cn-button-custom ' . $options['css_class'] : '' ) . '" aria-label="' . esc_attr( $options['revoke_text'] ) . '"' . ( $options['css_class'] == '' ? ' style="background-color: ' . esc_attr( $options['colors']['button'] ) . '"' : '' ) . '>' . esc_html( $options['revoke_text'] ) . '</a></span>
			</div>' : '' ) . '
		</div>
		<!-- / Cookie Notice plugin -->';

		echo apply_filters( 'cn_cookie_notice_output', $output, $options );
	}

	/**
	 * Load notice scripts and styles - frontend.
	 *
	 * @return void
	 */
	public function wp_enqueue_notice_scripts() {
		// get main instance
		$cn = Cookie_Notice();

		wp_enqueue_script( 'cookie-notice-front', COOKIE_NOTICE_URL . '/js/front' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', [], $cn->defaults['version'], isset( $cn->options['general']['script_placement'] ) && $cn->options['general']['script_placement'] === 'footer' );

		wp_localize_script(
			'cookie-notice-front',
			'cnArgs',
			[
				'ajaxUrl'				=> admin_url( 'admin-ajax.php' ),
				'nonce'					=> wp_create_nonce( 'cn_save_cases' ),
				'hideEffect'			=> $cn->options['general']['hide_effect'],
				'position'				=> $cn->options['general']['position'],
				'onScroll'				=> (int) $cn->options['general']['on_scroll'],
				'onScrollOffset'		=> (int) $cn->options['general']['on_scroll_offset'],
				'onClick'				=> (int) $cn->options['general']['on_click'],
				'cookieName'			=> 'cookie_notice_accepted',
				'cookieTime'			=> $cn->settings->times[$cn->options['general']['time']][1],
				'cookieTimeRejected'	=> $cn->settings->times[$cn->options['general']['time_rejected']][1],
				'globalCookie'			=> (int) ( is_multisite() && $cn->options['general']['global_cookie'] && is_subdomain_install() ),
				'redirection'			=> (int) $cn->options['general']['redirection'],
				'cache'					=> (int) ( defined( 'WP_CACHE' ) && WP_CACHE ),
				'refuse'				=> (int) $cn->options['general']['refuse_opt'],
				'revokeCookies'			=> (int) $cn->options['general']['revoke_cookies'],
				'revokeCookiesOpt'		=> $cn->options['general']['revoke_cookies_opt'],
				'secure'				=> (int) is_ssl()
			]
		);

		wp_enqueue_style( 'cookie-notice-front', COOKIE_NOTICE_URL . '/css/front' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css' );
	}

	/**
	 * Make a JavaScript Asynchronous.
	 *
	 * @param string $tag The original enqueued script tag
	 * @param string $handle The registered unique name of the script
	 * @param string $src
	 * @return string $tag The modified script tag
	 */
	public function wp_enqueue_script_async( $tag, $handle, $src ) {
		if ( 'cookie-notice-front' === $handle )
			$tag = str_replace( '<script', '<script async', $tag );

		return $tag;
	}

	/**
	 * Print non functional JavaScript in body.
	 *
	 * @return void
	 */
	public function wp_print_footer_scripts() {
		if ( Cookie_Notice()->cookies_accepted() ) {
			$scripts = apply_filters( 'cn_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code'], Cookie_Notice()->get_allowed_html() ) ) ), 'body' );

			if ( ! empty( $scripts ) )
				echo $scripts;
		}
	}

	/**
	 * Print non functional JavaScript in header.
	 *
	 * @return void
	 */
	public function wp_print_header_scripts() {
		if ( Cookie_Notice()->cookies_accepted() ) {
			$scripts = apply_filters( 'cn_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code_head'], Cookie_Notice()->get_allowed_html() ) ) ), 'head' );

			if ( ! empty( $scripts ) )
				echo $scripts;
		}
	}
	
	/**
	 * Get custom providers or patterns.
	 * 
	 * @param type $items
	 * @return type
	 */
	public function get_custom_items( $items ) {
		$result = [];
		
		if ( ! empty( $items ) && is_array( $items ) ) {
			foreach ( $items as $index => $item ) {
				if ( isset( $item->IsCustom ) && $item->IsCustom == true ) {
					$sanitized_item = [];
					
					foreach ( $item as $key => $value ) {
						$sanitized_item[$key] = $this->sanitize_field( $value, $key );
					}
					
					$result[] = (object) $sanitized_item;
				}
			}
		}
		
		return $result;
	}
	
	private function sanitize_field( $value, $key ) {
		$sanitized_value = $value;
		
		switch ( $key ) {
			case 'CategoryID':
				$sanitized_value = (int) $value;
				break;
			case 'IsCustom':
				$sanitized_value = (bool) $value;
				break;
		}
		
		return $sanitized_value;
	}

	/**
	 * Add new body classes.
	 *
	 * @param array $classes Body classes
	 * @return array
	 */
	public function change_body_class( $classes ) {
		if ( is_admin() )
			return $classes;

		if ( Cookie_Notice()->cookies_set() ) {
			$classes[] = 'cookies-set';

			if ( Cookie_Notice()->cookies_accepted() )
				$classes[] = 'cookies-accepted';
			else
				$classes[] = 'cookies-refused';
		} else
			$classes[] = 'cookies-not-set';

		return $classes;
	}

	/**
	 * Purge config cache.
	 *
	 * @return void
	 */
	public function purge_cache() {
		delete_transient( 'cookie_notice_compliance_cache' );
	}
}