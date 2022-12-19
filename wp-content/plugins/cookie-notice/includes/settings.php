<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie_Notice_Settings class.
 *
 * @class Cookie_Notice_Settings
 */
class Cookie_Notice_Settings {

	public $positions = [];
	public $styles = [];
	public $links = [];
	public $link_targets = [];
	public $link_positions = [];
	public $colors = [];
	public $effects = [];
	public $times = [];
	public $script_placements = [];
	public $countries = [];
	public $level_names = [];
	public $text_strings = [];

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// actions
		add_action( 'admin_menu', [ $this, 'admin_menu_options' ] );
		add_action( 'network_admin_menu', [ $this, 'admin_menu_options' ] );
		add_action( 'after_setup_theme', [ $this, 'load_defaults' ] );
		add_action( 'admin_init', [ $this, 'validate_network_options' ], 9 );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'admin_print_styles', [ $this, 'admin_print_styles' ] );
		add_action( 'wp_ajax_cn_purge_cache', [ $this, 'ajax_purge_cache' ] );
		add_action( 'admin_notices', [ $this, 'settings_errors' ] );
		add_action( 'network_admin_notices', [ $this, 'settings_errors' ] );
	}

	/**
	 * Load plugin defaults.
	 *
	 * @return void
	 */
	public function load_defaults() {
		$this->positions = [
			'top'		=> __( 'Top', 'cookie-notice' ),
			'bottom'	=> __( 'Bottom', 'cookie-notice' )
		];

		$this->styles = [
			'none'			=> __( 'None', 'cookie-notice' ),
			'wp-default'	=> __( 'Light', 'cookie-notice' ),
			'bootstrap'		=> __( 'Dark', 'cookie-notice' )
		];

		$this->revoke_opts = [
			'automatic'	=> __( 'Automatic', 'cookie-notice' ),
			'manual'	=> __( 'Manual', 'cookie-notice' )
		];

		$this->links = [
			'page'		=> __( 'Page link', 'cookie-notice' ),
			'custom'	=> __( 'Custom link', 'cookie-notice' )
		];

		$this->link_targets = [ '_blank', '_self' ];

		$this->link_positions = [
			'banner'	=> __( 'Banner', 'cookie-notice' ),
			'message'	=> __( 'Message', 'cookie-notice' )
		];

		$this->colors = [
			'text'		=> __( 'Text color', 'cookie-notice' ),
			'button'	=> __( 'Button color', 'cookie-notice' ),
			'bar'		=> __( 'Bar color', 'cookie-notice' )
		];

		$this->times = apply_filters(
			'cn_cookie_expiry',
			[
				'hour'		=> [ __( 'An hour', 'cookie-notice' ), 3600 ],
				'day'		=> [ __( '1 day', 'cookie-notice' ), 86400 ],
				'week'		=> [ __( '1 week', 'cookie-notice' ), 604800 ],
				'month'		=> [ __( '1 month', 'cookie-notice' ), 2592000 ],
				'3months'	=> [ __( '3 months', 'cookie-notice' ), 7862400 ],
				'6months'	=> [ __( '6 months', 'cookie-notice' ), 15811200 ],
				'year'		=> [ __( '1 year', 'cookie-notice' ), 31536000 ],
				'infinity'	=> [ __( 'infinity', 'cookie-notice' ), 2147483647 ]
			]
		);

		$this->effects = [
			'none'	=> __( 'None', 'cookie-notice' ),
			'fade'	=> __( 'Fade', 'cookie-notice' ),
			'slide'	=> __( 'Slide', 'cookie-notice' )
		];

		$this->script_placements = [
			'header'	=> __( 'Header', 'cookie-notice' ),
			'footer'	=> __( 'Footer', 'cookie-notice' )
		];

		$this->level_names = [
			1 => [
				1 => __( 'Silver', 'cookie-notice' ),
				2 => __( 'Gold', 'cookie-notice' ),
				3 => __( 'Platinum', 'cookie-notice' )
			],
			2 => [
				1 => __( 'Private', 'cookie-notice' ),
				2 => __( 'Balanced', 'cookie-notice' ),
				3 => __( 'Personalized', 'cookie-notice' )
			],
			3 => [
				1 => __( 'Reject All', 'cookie-notice' ),
				2 => __( 'Accept Some', 'cookie-notice' ),
				3 => __( 'Accept All', 'cookie-notice' )
			]
		];

		$this->text_strings = [
			'saveBtnText'		=> __( 'Save my preferences', 'cookie-notice' ),
			// 'acceptBtnText'		=> __( 'Accept', 'cookie-notice' ),
			// 'rejectBtnText'		=> __( 'Reject', 'cookie-notice' ),
			// 'revokeBtnText'		=> __( 'Revoke Cookies', 'cookie-notice' ),
			'privacyBtnText'	=> __( 'Privacy policy', 'cookie-notice' ),
			'dontSellBtnText'	=> __( 'Do Not Sell', 'cookie-notice' ),
			'customizeBtnText'	=> __( 'Preferences', 'cookie-notice' ),
			'headingText'		=> __( "We believe your data is your property and support your right to privacy and transparency.", 'cookie-notice' ),
			'bodyText'			=> __( "Select a Data Access Level and Duration to choose how we use and share your data.", 'cookie-notice' ),
			'levelBodyText_1'	=> __( 'Highest level of privacy. Data accessed for necessary site operations only. Data shared with 3rd parties to ensure the site is secure and works on your device.', 'cookie-notice' ),
			'levelBodyText_2'	=> __( 'Balanced experience. Data accessed for content personalisation and site optimisation. Data shared with 3rd parties may be used to track and store your preferences for this site.', 'cookie-notice' ),
			'levelBodyText_3'	=> __( 'Highest level of personalisation. Data accessed to make ads and media more relevant. Data shared with 3rd parties may be use to track you on this site and other sites you visit.', 'cookie-notice' ),
			'levelNameText_1'	=> $this->level_names[1][1],
			'levelNameText_2'	=> $this->level_names[1][2],
			'levelNameText_3'	=> $this->level_names[1][3],
			'monthText'			=> __( 'month', 'cookie-notice' ),
			'monthsText'		=> __( 'months', 'cookie-notice' )
		];

		// get main instance
		$cn = Cookie_Notice();

		// set default text strings
		$cn->defaults['general']['message_text'] = __( 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'cookie-notice' );
		$cn->defaults['general']['accept_text'] = __( 'Ok', 'cookie-notice' );
		$cn->defaults['general']['refuse_text'] = __( 'No', 'cookie-notice' );
		$cn->defaults['general']['revoke_message_text'] = __( 'You can revoke your consent any time using the Revoke consent button.', 'cookie-notice' );
		$cn->defaults['general']['revoke_text'] = __( 'Revoke consent', 'cookie-notice' );
		$cn->defaults['general']['see_more_opt']['text'] = __( 'Privacy policy', 'cookie-notice' );

		// set translation strings on plugin activation
		if ( $cn->options['general']['translate'] === true ) {
			$cn->options['general']['translate'] = false;

			$cn->options['general']['message_text'] = $cn->defaults['general']['message_text'];
			$cn->options['general']['accept_text'] = $cn->defaults['general']['accept_text'];
			$cn->options['general']['refuse_text'] = $cn->defaults['general']['refuse_text'];
			$cn->options['general']['revoke_message_text'] = $cn->defaults['general']['revoke_message_text'];
			$cn->options['general']['revoke_text'] = $cn->defaults['general']['revoke_text'];
			$cn->options['general']['see_more_opt']['text'] = $cn->defaults['general']['see_more_opt']['text'];

			if ( $cn->is_network_admin() )
				update_site_option( 'cookie_notice_options', $cn->options['general'] );
			else
				update_option( 'cookie_notice_options', $cn->options['general'] );
		}

		// WPML >= 3.2
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
			$this->register_wpml_strings();
		// WPML and Polylang compatibility
		} elseif ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'Cookie Notice', 'Message in the notice', $cn->options['general']['message_text'] );
			icl_register_string( 'Cookie Notice', 'Button text', $cn->options['general']['accept_text'] );
			icl_register_string( 'Cookie Notice', 'Refuse button text', $cn->options['general']['refuse_text'] );
			icl_register_string( 'Cookie Notice', 'Revoke message text', $cn->options['general']['revoke_message_text'] );
			icl_register_string( 'Cookie Notice', 'Revoke button text', $cn->options['general']['revoke_text'] );
			icl_register_string( 'Cookie Notice', 'Privacy policy text', $cn->options['general']['see_more_opt']['text'] );
			icl_register_string( 'Cookie Notice', 'Custom link', $cn->options['general']['see_more_opt']['link'] );
		}
	}

	/**
	 * Add submenu.
	 *
	 * @return void
	 */
	public function admin_menu_options() {
		if ( current_action() === 'network_admin_menu' && ! Cookie_Notice()->is_plugin_network_active() )
			return;

		add_menu_page( __( 'Cookie Notice', 'cookie-notice' ), __( 'Cookies', 'cookie-notice' ), apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ), 'cookie-notice', [ $this, 'options_page' ], 'none', '99.3' );
	}

	/**
	 * Options page output.
	 *
	 * @return void
	 */
	public function options_page() {
		// get main instance
		$cn = Cookie_Notice();

		// get cookie compliance status
		$status = $cn->get_status();
		$subscription = $cn->get_subscription();
		$upgrade_link = $cn->get_url( 'host', '?utm_campaign=upgrade+to+pro&utm_source=wordpress&utm_medium=link#/en/cc/dashboard?app-id=' . $cn->options['general']['app_id'] . '&open-modal=payment' );

		echo '
		<div class="wrap">
			<h2>' . __( 'Cookie Notice & Compliance for GDPR/CCPA', 'cookie-notice' ) . '</h2>
			<div class="cookie-notice-settings">
				<div class="cookie-notice-sidebar">
					<div class="cookie-notice-credits">
						<div class="inside">
							<div class="inner">';

		// compliance enabled
		if ( $status === 'active' ) {
				echo '			
							<div class="cn-pricing-info">
								<div class="cn-pricing-head">
									<p>' . __( 'Your Cookie Compliance plan:', 'cookie-notice' ) . '</p>
									<h2>' . ( $subscription === 'pro' ? __( 'Professional', 'cookie-notice' ) : __( 'Basic', 'cookie-notice' ) ) . '</h2>
								</div>
								<div class="cn-pricing-body">
									<p class="cn-active"><span class="cn-icon"></span>' . __( 'GDPR, CCPA, ePrivacy, PECR compliance', 'cookie-notice' ) . '</p>
									<p class="cn-active"><span class="cn-icon"></span>' . __( 'Consent Analytics Dashboard', 'cookie-notice' ) . '</p>
									<p class="' . ( $subscription === 'pro' ? 'cn-active' : 'cn-inactive' ) . '"><span class="cn-icon"></span>' . __( '<b>Unlimited</b> visits', 'cookie-notice' ) . '</p>
									<p class="' . ( $subscription === 'pro' ? 'cn-active' : 'cn-inactive' ) . '"><span class="cn-icon"></span>' . __( '<b>Lifetime</b> consent storage', 'cookie-notice' ) . '</p>
									<p class="' . ( $subscription === 'pro' ? 'cn-active' : 'cn-inactive' ) . '"><span class="cn-icon"></span>' . __( '<b>Geolocation</b> support', 'cookie-notice' ) . '</p>
									<p class="' . ( $subscription === 'pro' ? 'cn-active' : 'cn-inactive' ) . '"><span class="cn-icon"></span>' . __( '<b>Unlimited</b> languages', 'cookie-notice' ) . '</p>
									<p class="' . ( $subscription === 'pro' ? 'cn-active' : 'cn-inactive' ) . '"><span class="cn-icon"></span>' . __( '<b>Priority</b> Support', 'cookie-notice' ) . '</p>
								</div>';
				
				if ( $subscription !== 'pro' ) {
					echo '		<div class="cn-pricing-footer">
									<a href="' . $upgrade_link . '" class="button button-secondary button-hero cn-button" target="_blank">' . __( 'Upgrade to Pro', 'cookie-notice' ) . '</a>
								</div>';
				}
				
				echo '		</div>';
				
		// compliance disabled
		} else {
			echo '			<h1><b>Protect your business</b></h1>
								<h2>' . __( 'with Cookie Compliance&trade;', 'cookie-notice' ) . '</h2>
								<div class="cn-lead">
									<p>' . __( 'Deliver better consent experiences and comply with GDPR, CCPA and other data privacy laws more effectively.', 'cookie-notice' ) . '</p>
								</div>
								<img alt="' . __( 'Cookie Compliance dashboard', 'cookie-notice' ) . '" src="' . COOKIE_NOTICE_URL . '/img/screen-compliance.png">
								<p><a href="https://cookie-compliance.co/?utm_campaign=learn+more&utm_source=wordpress&utm_medium=banner" class="button button-secondary button-hero cn-button" target="_blank">' . __( 'Learn more', 'cookie-notice' ) . '</a></p>';
		}

		echo '
							</div>
						</div>
					</div>';

		echo '
					<div class="cookie-notice-faq">
						<h2>' . __( 'F.A.Q.', 'cookie-notice' ) . '</h2>
						<div class="cn-toggle-container">
							<label for="cn-faq-1" class="cn-toggle-item">
								<input id="cn-faq-1" type="checkbox" />
								<span class="cn-toggle-heading">' . __( 'Does the Cookie Notice make my site fully compliant with GDPR?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'It is not possible to provide the required technical compliance features using only a WordPress plugin. Features like consent record storage, purpose categories and script blocking that bring your site into full compliance with GDPR are only available through the Cookie Compliance integration.', 'cookie-notice' ) . '
							</label>
							<label for="cn-faq-2" class="cn-toggle-item">
								<input id="cn-faq-2" type="checkbox" />
								<span class="cn-toggle-heading">' . __( 'Does the Cookie Compliance integration make my site fully compliant with GDPR?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'Yes! The plugin + web application version includes technical compliance features to meet requirements for over 100 countries and legal jurisdictions.', 'cookie-notice' ) . '</span>
							</label>
							<label for="cn-faq-3" class="cn-toggle-item">
								<input id="cn-faq-3" type="checkbox" />
								<span class="cn-toggle-heading">' . __( 'Is Cookie Compliance free?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'Yes, but with limits. Cookie Compliance includes both free and paid plans to choose from depending on your needs and your website monthly traffic.', 'cookie-notice' ) . '</span>
							</label>
							<label for="cn-faq-4" class="cn-toggle-item">
								<input id="cn-faq-4" type="checkbox" />
								<span class="cn-toggle-heading">' . __( 'Where can I find pricing options?', 'cookie-notice' ) . '</span>
								<span class="cn-toggle-body">' . __( 'You can learn more about the features and pricing by visiting the Cookie Compliance website here:', 'cookie-notice' ) . ' <a href="https://cookie-compliance.co/?utm_campaign=pricing+options&utm_source=wordpress&utm_medium=textlink" target="_blank">https://cookie-compliance.co/</a></span>
							</label>
						</div>
					</div>';

		echo '
				</div>';

		// multisite?
		if ( is_multisite() ) {
			// network admin?
			if ( $cn->is_network_admin() ) {
				$form_class = ( $cn->is_plugin_network_active() && ! $cn->options['general']['global_override'] ? ' class="cn-options-disabled"' : '' );
				$form_page = 'admin.php?page=cookie-notice';
				$hidden_input = '<input type="hidden" name="cn-network-settings" value="true" />';
			// single network site
			} else {
				$form_class = ( $cn->is_plugin_network_active() && $cn->network_options['global_override'] ? ' class="cn-options-disabled cn-options-submit-disabled"' : '' );
				$form_page = 'options.php';
				$hidden_input = '';
			}
		// single site
		} else {
			$form_class = '';
			$form_page = 'options.php';
			$hidden_input = '';
		}

		echo '
				<form action="' . $form_page . '" method="post"' . $form_class . '>';

		settings_fields( 'cookie_notice_options' );

		echo $hidden_input;
		echo '
					<div class="cn-options">';

		do_settings_sections( 'cookie_notice_options' );

		echo '		</div>
					<p class="submit">';
		submit_button( '', 'primary', 'save_cookie_notice_options', false );

		echo ' ';

		submit_button( __( 'Reset to defaults', 'cookie-notice' ), 'secondary', 'reset_cookie_notice_options', false );
		echo '
					</p>
				</form>
			</div>
			<div class="clear"></div>
		</div>';
	}

	/**
	 * Regiseter plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'cookie_notice_options', 'cookie_notice_options', [ $this, 'validate_options' ] );

		// get main instance
		$cn = Cookie_Notice();

		$status = $cn->get_status();

		// multisite?
		if ( is_multisite() ) {
			// network admin?
			if ( $cn->is_network_admin() ) {
				// network section
				add_settings_section( 'cookie_notice_network', __( 'Network Settings', 'cookie-notice' ), '', 'cookie_notice_options' );
				add_settings_field( 'cn_global_override', __( 'Global Settings Override', 'cookie-notice' ), [ $this, 'cn_global_override' ], 'cookie_notice_options', 'cookie_notice_network' );
				add_settings_field( 'cn_global_cookie', __( 'Global Cookie', 'cookie-notice' ), [ $this, 'cn_global_cookie' ], 'cookie_notice_options', 'cookie_notice_network' );
			} elseif ( $cn->is_plugin_network_active() && $cn->network_options['global_override'] ) {
				// network section
				add_settings_section( 'cookie_notice_network', __( 'Network Settings', 'cookie-notice' ), [ $this, 'cn_network_section' ], 'cookie_notice_options' );
				add_settings_field( 'cn_dummy', '', '__return_empty_string', 'cookie_notice_options', 'cookie_notice_network' );

				// get default status data
				$default_data = $cn->defaults['data'];

				// get real status of current site
				$status_data = get_option( 'cookie_notice_status', $default_data );

				// old status format?
				if ( ! is_array( $status_data ) ) {
					// old value saved as string
					if ( is_string( $status_data ) && $cn->check_status( $status_data ) ) {
						// update status
						$default_data['status'] = $status_data;

						if ( $default_data['status'] === 'active' )
							$default_data['subscription'] = 'pro';
					}

					// set data
					$status_data = $default_data;
				}

				// get valid status
				$status = $cn->check_status( $status_data['status'] );
			}
		}

		// compliance enabled
		if ( $status === 'active' ) {
			// compliance section
			add_settings_section( 'cookie_notice_compliance', __( 'Compliance Settings', 'cookie-notice' ), '', 'cookie_notice_options' );
			add_settings_field( 'cn_app_status', __( 'Compliance status', 'cookie-notice' ), [ $this, 'cn_app_status' ], 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_id', __( 'App ID', 'cookie-notice' ), [ $this, 'cn_app_id' ], 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_key', __( 'App Key', 'cookie-notice' ), [ $this, 'cn_app_key' ], 'cookie_notice_options', 'cookie_notice_compliance' );

			// configuration section
			add_settings_section( 'cookie_notice_configuration', __( 'Miscellaneous Settings', 'cookie-notice' ), '', 'cookie_notice_options' );
			add_settings_field( 'cn_app_blocking', __( 'Autoblocking', 'cookie-notice' ), [ $this, 'cn_app_blocking' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_hide_banner', __( 'Hide for logged in', 'cookie-notice' ), [ $this, 'cn_hide_banner' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_debug_mode', __( 'Debug mode', 'cookie-notice' ), [ $this, 'cn_debug_mode' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_app_purge_cache', __( 'Cache', 'cookie-notice' ), [ $this, 'cn_app_purge_cache' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), [ $this, 'cn_script_placement' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), [ $this, 'cn_deactivation_delete' ], 'cookie_notice_options', 'cookie_notice_configuration' );
		// compliance disabled
		} else {
			// compliance section
			add_settings_section( 'cookie_notice_compliance', __( 'Compliance Settings', 'cookie-notice' ), '', 'cookie_notice_options' );
			add_settings_field( 'cn_app_status', __( 'Compliance status', 'cookie-notice' ), [ $this, 'cn_app_status' ], 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_id', __( 'App ID', 'cookie-notice' ), [ $this, 'cn_app_id' ], 'cookie_notice_options', 'cookie_notice_compliance' );
			add_settings_field( 'cn_app_key', __( 'App Key', 'cookie-notice' ), [ $this, 'cn_app_key' ], 'cookie_notice_options', 'cookie_notice_compliance' );

			// configuration section
			add_settings_section( 'cookie_notice_configuration', __( 'Notice Settings', 'cookie-notice' ), '', 'cookie_notice_options' );
			add_settings_field( 'cn_message_text', __( 'Message', 'cookie-notice' ), [ $this, 'cn_message_text' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_accept_text', __( 'Button text', 'cookie-notice' ), [ $this, 'cn_accept_text' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_see_more', __( 'Privacy policy', 'cookie-notice' ), [ $this, 'cn_see_more' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_refuse_opt', __( 'Refuse consent', 'cookie-notice' ), [ $this, 'cn_refuse_opt' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_revoke_opt', __( 'Revoke consent', 'cookie-notice' ), [ $this, 'cn_revoke_opt' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_refuse_code', __( 'Script blocking', 'cookie-notice' ), [ $this, 'cn_refuse_code' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_redirection', __( 'Reloading', 'cookie-notice' ), [ $this, 'cn_redirection' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_on_scroll', __( 'On scroll', 'cookie-notice' ), [ $this, 'cn_on_scroll' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_on_click', __( 'On click', 'cookie-notice' ), [ $this, 'cn_on_click' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_time', __( 'Accepted expiry', 'cookie-notice' ), [ $this, 'cn_time' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_time_rejected', __( 'Rejected expiry', 'cookie-notice' ), [ $this, 'cn_time_rejected' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_script_placement', __( 'Script placement', 'cookie-notice' ), [ $this, 'cn_script_placement' ], 'cookie_notice_options', 'cookie_notice_configuration' );
			add_settings_field( 'cn_deactivation_delete', __( 'Deactivation', 'cookie-notice' ), [ $this, 'cn_deactivation_delete' ], 'cookie_notice_options', 'cookie_notice_configuration' );

			// design section
			add_settings_section( 'cookie_notice_design', __( 'Notice Design', 'cookie-notice' ), '', 'cookie_notice_options' );
			add_settings_field( 'cn_position', __( 'Position', 'cookie-notice' ), [ $this, 'cn_position' ], 'cookie_notice_options', 'cookie_notice_design' );
			add_settings_field( 'cn_hide_effect', __( 'Animation', 'cookie-notice' ), [ $this, 'cn_hide_effect' ], 'cookie_notice_options', 'cookie_notice_design' );
			add_settings_field( 'cn_colors', __( 'Colors', 'cookie-notice' ), [ $this, 'cn_colors' ], 'cookie_notice_options', 'cookie_notice_design' );
			add_settings_field( 'cn_css_class', __( 'Button class', 'cookie-notice' ), [ $this, 'cn_css_class' ], 'cookie_notice_options', 'cookie_notice_design' );
		}
	}

	/**
	 * Network settings override option.
	 *
	 * @return void
	 */
	public function cn_global_override() {
		echo '
		<div id="cn_global_override">
			<label><input type="checkbox" name="cookie_notice_options[global_override]" value="1" ' . checked( true, Cookie_Notice()->options['general']['global_override'], false ) . ' />' . __( 'Enable global network settings override.', 'cookie-notice' ) . '</label>
			<p class="description">' . __( 'Every site in the network will use the same settings. Site administrators will not be able to change them.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Network cookie acceptance option.
	 *
	 * @return void
	 */
	public function cn_global_cookie() {
		$multi_folders = is_multisite() && ! is_subdomain_install();

		// multisite with path-based network?
		if ( $multi_folders )
			$desc = ' ' . __( 'This option works only for domain-based networks.', 'cookie-notice' );
		else
			$desc = '';

		echo '
		<div id="cn_global_cookie">
			<label><input type="checkbox" name="cookie_notice_options[global_cookie]" value="1" ' . checked( true, Cookie_Notice()->options['general']['global_cookie'], false ) . ' ' . disabled( $multi_folders, true, false ) . ' />' . __( 'Enable global network cookie consent.', 'cookie-notice' ) . '</label>
			<p class="description">' . __( 'Cookie consent in one of the network sites results in a consent in all of the sites on the network.', 'cookie-notice' ) . $desc . '</p>
		</div>';
	}

	/**
	 * Network settings section.
	 *
	 * @return void
	 */
	public function cn_network_section() {
		echo '
		<p>' . __( 'Global network settings override is active. Every site will use the same network settings. Please contact super administrator if you want to have more control over the settings.', 'cookie-notice' ) . '</p>';
	}

	/**
	 * Compliance status.
	 *
	 * @return void
	 */
	public function cn_app_status() {
		// get main instance
		$cn = Cookie_Notice();

		// get cookie compliance status
		$app_status = $cn->get_status();

		switch ( $app_status ) {
			case 'active':
				echo '
				<div id="cn_app_status">
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Notice', 'cookie-notice' ) . '</span>: <span class="cn-status cn-active"><span class="cn-icon"></span> ' . __( 'Active', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Autoblocking', 'cookie-notice' ) . '</span>: <span class="cn-status cn-active"><span class="cn-icon"></span> ' . __( 'Active', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Cookie Categories', 'cookie-notice' ) . '</span>: <span class="cn-status cn-active"><span class="cn-icon"></span> ' . __( 'Active', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Proof-of-Consent', 'cookie-notice' ) . '</span>: <span class="cn-status cn-active"><span class="cn-icon"></span> ' . __( 'Active', 'cookie-notice' ) . '</span></div>
				</div>
				<div id="cn_app_actions">
					<a href="' . esc_url( $cn->get_url( 'host', '?utm_campaign=configure&utm_source=wordpress&utm_medium=button#/en/cc/login' ) ) . '" class="button button-primary button-hero cn-button" target="_blank">' . __( 'Log in & Configure', 'cookie-notice' ) . '</a>
					<p class="description">' . __( 'Log into the Cookie Compliance&trade; web application and configure your Privacy Experience.', 'cookie-notice' ) . '</p>
				</div>';
				break;

			case 'pending':
				echo '
				<div id="cn_app_status">
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Notice', 'cookie-notice' ) . '</span>: <span class="cn-status cn-active"><span class="cn-icon"></span> ' . __( 'Active', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Autoblocking', 'cookie-notice' ) . '</span>: <span class="cn-status cn-pending"><span class="cn-icon"></span> ' . __( 'Pending', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Cookie Categories', 'cookie-notice' ) . '</span>: <span class="cn-status cn-pending"><span class="cn-icon"></span> ' . __( 'Pending', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Proof-of-Consent', 'cookie-notice' ) . '</span>: <span class="cn-status cn-pending"><span class="cn-icon"></span> ' . __( 'Pending', 'cookie-notice' ) . '</span></div>
				</div>
				<div id="cn_app_actions">
					<a href="' . esc_url( $cn->get_url( 'host', '?utm_campaign=configure&utm_source=wordpress&utm_medium=button#/en/cc/login' ) ) . '" class="button button-primary button-hero cn-button" target="_blank">' . __( 'Log in & configure', 'cookie-notice' ) . '</a>
					<p class="description">' . __( 'Log into the Cookie Compliance&trade; web application and complete the setup process.', 'cookie-notice' ) . '</p>
				</div>';
				break;

			default:
				if ( $cn->is_network_admin() )
					$url = network_admin_url( 'admin.php?page=cookie-notice' );
				else
					$url = admin_url( 'admin.php?page=cookie-notice' );

				echo '
				<div id="cn_app_status">
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Notice', 'cookie-notice' ) . '</span>: <span class="cn-status cn-active"><span class="cn-icon"></span> ' . __( 'Active', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Autoblocking', 'cookie-notice' ) . '</span>: <span class="cn-status cn-inactive"><span class="cn-icon"></span> ' . __( 'Inactive', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Cookie Categories', 'cookie-notice' ) . '</span>: <span class="cn-status cn-inactive"><span class="cn-icon"></span> ' . __( 'Inactive', 'cookie-notice' ) . '</span></div>
					<div class="cn_compliance_status"><span class="cn-status-label">' . __( 'Proof-of-Consent', 'cookie-notice' ) . '</span>: <span class="cn-status cn-inactive"><span class="cn-icon"></span> ' . __( 'Inactive', 'cookie-notice' ) . '</span></div>
				</div>
				<div id="cn_app_actions">
					<a href="' . esc_url( $url ) . '" class="button button-primary button-hero cn-button cn-run-welcome">' . __( 'Add Compliance features', 'cookie-notice' ) . '</a>
					<p class="description">' . sprintf( __( 'Sign up to <a href="%s" target="_blank">Cookie Compliance&trade;</a> and add GDPR, CCPA and other international data privacy laws compliance features.', 'cookie-notice' ), 'https://cookie-compliance.co/?utm_campaign=sign-up&utm_source=wordpress&utm_medium=textlink' ) . '</p>
				</div>';
				break;
		}
	}

	/**
	 * App ID option.
	 *
	 * @return void
	 */
	public function cn_app_id() {
		echo '
		<div id="cn_app_id">
			<input type="text" class="regular-text" name="cookie_notice_options[app_id]" value="' . esc_attr( Cookie_Notice()->options['general']['app_id'] ) . '" />
			<p class="description">' . __( 'Enter your Cookie Compliance&trade; application ID.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * App key option.
	 *
	 * @return void
	 */
	public function cn_app_key() {
		echo '
		<div id="cn_app_key">
			<input type="password" class="regular-text" name="cookie_notice_options[app_key]" value="' . esc_attr( Cookie_Notice()->options['general']['app_key'] ) . '" />
			<p class="description">' . __( 'Enter your Cookie Compliance&trade; application secret key.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * App autoblocking option.
	 *
	 * @return void
	 */
	public function cn_app_blocking() {
		echo '
		<div id="cn_app_blocking">
			<label><input type="checkbox" name="cookie_notice_options[app_blocking]" value="1" ' . checked( true, Cookie_Notice()->options['general']['app_blocking'], false ) . ' />' . __( 'Enable to automatically block 3rd party scripts before user consent.', 'cookie-notice' ) . '</label>
			<p class="description">' . __( "In case you're experiencing issues with your site disable that feature temporarily.", 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Purge cache option.
	 *
	 * @return void
	 */
	public function cn_app_purge_cache() {
		echo '
		<div id="cn_app_purge_cache">
			<div class="cn-button-container">
				<a href="#" class="button button-secondary">' . __( 'Purge Cache', 'cookie-notice' ) . '</a>
			</div>
			<p class="description">' . __( 'Click the Purge Cache button to refresh the app configuration.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Hide banner option.
	 *
	 * @return void
	 */
	public function cn_hide_banner() {
		echo '
		<div id="cn_hide_banner">
			<label><input type="checkbox" name="cookie_notice_options[hide_banner]" value="1" ' . checked( true, Cookie_Notice()->options['general']['hide_banner'], false ) . ' />' . __( 'Enable to hide the consent banner for logged in users.', 'cookie-notice' ) . '</label>
		</div>';
	}

	/**
	 * Debug mode option.
	 *
	 * @return void
	 */
	public function cn_debug_mode() {
		echo '
		<div id="cn_debug_mode">
			<label><input type="checkbox" name="cookie_notice_options[debug_mode]" value="1" ' . checked( true, Cookie_Notice()->options['general']['debug_mode'], false ) . ' />' . __( 'Enable to run the consent banner in debug mode.', 'cookie-notice' ) . '</label>
		</div>';
	}

	/**
	 * Cookie notice message option.
	 *
	 * @return void
	 */
	public function cn_message_text() {
		echo '
		<div id="cn_message_text">
			<textarea name="cookie_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea( Cookie_Notice()->options['general']['message_text'] ) . '</textarea>
			<p class="description">' . __( 'Enter the cookie notice message.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Accept cookie label option.
	 *
	 * @return void
	 */
	public function cn_accept_text() {
		echo '
		<div id="cn_accept_text">
			<input type="text" class="regular-text" name="cookie_notice_options[accept_text]" value="' . esc_attr( Cookie_Notice()->options['general']['accept_text'] ) . '" />
			<p class="description">' . __( 'The text of the option to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Toggle third party non functional cookies option.
	 *
	 * @return void
	 */
	public function cn_refuse_opt() {
		echo '
		<fieldset>
			<label><input id="cn_refuse_opt" type="checkbox" name="cookie_notice_options[refuse_opt]" value="1" ' . checked( true, Cookie_Notice()->options['general']['refuse_opt'], false ) . ' />' . __( 'Enable to give to the user the possibility to refuse third party non functional cookies.', 'cookie-notice' ) . '</label>
			<div id="cn_refuse_opt_container"' . ( Cookie_Notice()->options['general']['refuse_opt'] === false ? ' style="display: none;"' : '' ) . '>
				<div id="cn_refuse_text">
					<input type="text" class="regular-text" name="cookie_notice_options[refuse_text]" value="' . esc_attr( Cookie_Notice()->options['general']['refuse_text'] ) . '" />
					<p class="description">' . __( 'The text of the button to refuse the consent.', 'cookie-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
	}

	/**
	 * Non functional cookies code option.
	 *
	 * @return void
	 */
	public function cn_refuse_code() {
		$allowed_html = Cookie_Notice()->get_allowed_html();
		$active = ! empty( Cookie_Notice()->options['general']['refuse_code'] ) && empty( Cookie_Notice()->options['general']['refuse_code_head'] ) ? 'body' : 'head';

		echo '
		<div id="cn_refuse_code">
			<div id="cn_refuse_code_fields">
				<h2 class="nav-tab-wrapper">
					<a id="refuse_head-tab" class="nav-tab' . ( $active === 'head' ? ' nav-tab-active' : '' ) . '" href="#refuse_head">' . __( 'Head', 'cookie-notice' ) . '</a>
					<a id="refuse_body-tab" class="nav-tab' . ( $active === 'body' ? ' nav-tab-active' : '' ) . '" href="#refuse_body">' . __( 'Body', 'cookie-notice' ) . '</a>
				</h2>
				<div id="refuse_head" class="refuse-code-tab' . ( $active === 'head' ? ' active' : '' ) . '">
					<p class="description">' . __( 'The code to be used in your site header, before the closing head tag.', 'cookie-notice' ) . '</p>
					<textarea name="cookie_notice_options[refuse_code_head]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code_head'], $allowed_html ) ) ) . '</textarea>
				</div>
				<div id="refuse_body" class="refuse-code-tab' . ( $active === 'body' ? ' active' : '' ) . '">
					<p class="description">' . __( 'The code to be used in your site footer, before the closing body tag.', 'cookie-notice' ) . '</p>
					<textarea name="cookie_notice_options[refuse_code]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( Cookie_Notice()->options['general']['refuse_code'], $allowed_html ) ) ) . '</textarea>
				</div>
			</div>
			<p class="description">' . __( 'Enter non functional cookies Javascript code here (for e.g. Google Analitycs) to be used after the notice is accepted.', 'cookie-notice' ) . '</br>' . __( 'To get the user consent status use the <code>cn_cookies_accepted()</code> function.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Revoke cookies option.
	 *
	 * @return void
	 */
	public function cn_revoke_opt() {
		echo '
		<fieldset>
			<div id="cn_revoke_opt">
				<label><input id="cn_revoke_cookies" type="checkbox" name="cookie_notice_options[revoke_cookies]" value="1" ' . checked( true, Cookie_Notice()->options['general']['revoke_cookies'], false ) . ' />' . __( 'Enable to give to the user the possibility to revoke their consent <i>(requires "Refuse consent" option enabled)</i>.', 'cookie-notice' ) . '</label>
				<div id="cn_revoke_opt_container"' . ( Cookie_Notice()->options['general']['revoke_cookies'] ? '' : ' style="display: none;"' ) . '>
					<textarea name="cookie_notice_options[revoke_message_text]" class="large-text" cols="50" rows="2">' . esc_textarea( Cookie_Notice()->options['general']['revoke_message_text'] ) . '</textarea>
					<p class="description">' . __( 'Enter the revoke message.', 'cookie-notice' ) . '</p>
					<input type="text" class="regular-text" name="cookie_notice_options[revoke_text]" value="' . esc_attr( Cookie_Notice()->options['general']['revoke_text'] ) . '" />
					<p class="description">' . __( 'The text of the button to revoke the consent.', 'cookie-notice' ) . '</p>';

		foreach ( $this->revoke_opts as $value => $label ) {
			echo '
					<label><input id="cn_revoke_cookies-' . $value . '" type="radio" name="cookie_notice_options[revoke_cookies_opt]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['revoke_cookies_opt'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
					<p class="description">' . __( 'Select the method for displaying the revoke button - automatic (in the banner) or manual using <code>[cookies_revoke]</code> shortcode.', 'cookie-notice' ) . '</p>
				</div>
			</div>
		<fieldset>';
	}

	/**
	 * Redirection on cookie accept option.
	 *
	 * @return void
	 */
	public function cn_redirection() {
		echo '
		<div id="cn_redirection">
			<label><input type="checkbox" name="cookie_notice_options[redirection]" value="1" ' . checked( true, Cookie_Notice()->options['general']['redirection'], false ) . ' />' . __( 'Enable to reload the page after the notice is accepted.', 'cookie-notice' ) . '</label>
		</div>';
	}

	/**
	 * Privacy policy link option.
	 *
	 * @global string $wp_version
	 *
	 * @return void
	 */
	public function cn_see_more() {
		$pages = get_pages(
			[
				'sort_order'	=> 'ASC',
				'sort_column'	=> 'post_title',
				'hierarchical'	=> 0,
				'child_of'		=> 0,
				'parent'		=> -1,
				'offset'		=> 0,
				'post_type'		=> 'page',
				'post_status'	=> 'publish'
			]
		);

		echo '
		<fieldset>
			<label><input id="cn_see_more" type="checkbox" name="cookie_notice_options[see_more]" value="1" ' . checked( true, Cookie_Notice()->options['general']['see_more'], false ) . ' />' . __( 'Enable privacy policy link.', 'cookie-notice' ) . '</label>
			<div id="cn_see_more_opt"' . (Cookie_Notice()->options['general']['see_more'] === false ? ' style="display: none;"' : '') . '>
				<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][text]" value="' . esc_attr( Cookie_Notice()->options['general']['see_more_opt']['text'] ) . '" />
				<p class="description">' . __( 'The text of the privacy policy button.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_custom_link">';

		foreach ( $this->links as $value => $label ) {
			$value = esc_attr( $value );

			echo '
					<label><input id="cn_see_more_link-' . $value . '" type="radio" name="cookie_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['see_more_opt']['link_type'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select where to redirect user for more information.', 'cookie-notice' ) . '</p>
				<div id="cn_see_more_opt_page"' . (Cookie_Notice()->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
					<select name="cookie_notice_options[see_more_opt][id]">
						<option value="0" ' . selected( 0, Cookie_Notice()->options['general']['see_more_opt']['id'], false ) . '>' . __( '-- select page --', 'cookie-notice' ) . '</option>';

		if ( $pages ) {
			foreach ( $pages as $page ) {
				echo '
						<option value="' . $page->ID . '" ' . selected( $page->ID, Cookie_Notice()->options['general']['see_more_opt']['id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
			}
		}

		echo '
					</select>
					<p class="description">' . __( 'Select from one of your site\'s pages.', 'cookie-notice' ) . '</p>';

		global $wp_version;

		if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
			echo '
						<label><input id="cn_see_more_opt_sync" type="checkbox" name="cookie_notice_options[see_more_opt][sync]" value="1" ' . checked( true, Cookie_Notice()->options['general']['see_more_opt']['sync'], false ) . ' />' . __( 'Synchronize with WordPress Privacy Policy page.', 'cookie-notice' ) . '</label>';
		}

		echo '
				</div>
				<div id="cn_see_more_opt_link"' . (Cookie_Notice()->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
					<input type="text" class="regular-text" name="cookie_notice_options[see_more_opt][link]" value="' . esc_attr( Cookie_Notice()->options['general']['see_more_opt']['link'] ) . '" />
					<p class="description">' . __( 'Enter the full URL starting with http(s)://', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_target">';

		foreach ( $this->link_targets as $target ) {
			echo '
					<label><input id="cn_see_more_link_target-' . $target . '" type="radio" name="cookie_notice_options[link_target]" value="' . $target . '" ' . checked( $target, Cookie_Notice()->options['general']['link_target'], false ) . ' />' . $target . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link target.', 'cookie-notice' ) . '</p>
				</div>
				<div id="cn_see_more_link_position">';

		foreach ( $this->link_positions as $position => $label ) {
			echo '
					<label><input id="cn_see_more_link_position-' . $position . '" type="radio" name="cookie_notice_options[link_position]" value="' . $position . '" ' . checked( $position, Cookie_Notice()->options['general']['link_position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
					<p class="description">' . esc_html__( 'Select the privacy policy link position.', 'cookie-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
	}

	/**
	 * Expiration time option.
	 *
	 * @return void
	 */
	public function cn_time() {
		echo '
		<div id="cn_time">
			<select name="cookie_notice_options[time]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
				<option value="' . $time . '" ' . selected( $time, Cookie_Notice()->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
			</select>
			<p class="description">' . __( 'The amount of time that the cookie should be stored for when user accepts the notice.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Expiration time option.
	 *
	 * @return void
	 */
	public function cn_time_rejected() {
		echo '
		<div id="cn_time_rejected">
			<select name="cookie_notice_options[time_rejected]">';

		foreach ( $this->times as $time => $arr ) {
			$time = esc_attr( $time );

			echo '
				<option value="' . $time . '" ' . selected( $time, Cookie_Notice()->options['general']['time_rejected'] ) . '>' . esc_html( $arr[0] ) . '</option>';
		}

		echo '
			</select>
			<p class="description">' . __( 'The amount of time that the cookie should be stored for when the user doesn\'t accept the notice.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Script placement option.
	 *
	 * @return void
	 */
	public function cn_script_placement() {
		echo '
		<div id="cn_script_placement">';

		foreach ( $this->script_placements as $value => $label ) {
			echo '
			<label><input id="cn_script_placement-' . $value . '" type="radio" name="cookie_notice_options[script_placement]" value="' . esc_attr( $value ) . '" ' . checked( $value, Cookie_Notice()->options['general']['script_placement'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select where all the plugin scripts should be placed.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Position option.
	 *
	 * @return void
	 */
	public function cn_position() {
		echo '
		<div id="cn_position">';

		foreach ( $this->positions as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<label><input id="cn_position-' . $value . '" type="radio" name="cookie_notice_options[position]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['position'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select location for the notice.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Animation effect option.
	 *
	 * @return void
	 */
	public function cn_hide_effect() {
		echo '
		<div id="cn_hide_effect">';

		foreach ( $this->effects as $value => $label ) {
			$value = esc_attr( $value );

			echo '
			<label><input id="cn_hide_effect-' . $value . '" type="radio" name="cookie_notice_options[hide_effect]" value="' . $value . '" ' . checked( $value, Cookie_Notice()->options['general']['hide_effect'], false ) . ' />' . esc_html( $label ) . '</label>';
		}

		echo '
			<p class="description">' . __( 'Select the animation style.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * On scroll option.
	 *
	 * @return void
	 */
	public function cn_on_scroll() {
		echo '
		<fieldset>
			<label><input id="cn_on_scroll" type="checkbox" name="cookie_notice_options[on_scroll]" value="1" ' . checked( true, Cookie_Notice()->options['general']['on_scroll'], false ) . ' />' . __( 'Enable to accept the notice when user scrolls.', 'cookie-notice' ) . '</label>
			<div id="cn_on_scroll_offset"' . ( Cookie_Notice()->options['general']['on_scroll'] === false || Cookie_Notice()->options['general']['on_scroll'] == false ? ' style="display: none;"' : '' ) . '>
				<input type="text" class="text" name="cookie_notice_options[on_scroll_offset]" value="' . esc_attr( Cookie_Notice()->options['general']['on_scroll_offset'] ) . '" /> <span>px</span>
				<p class="description">' . __( 'Number of pixels user has to scroll to accept the notice and make it disappear.', 'cookie-notice' ) . '</p>
			</div>
		</fieldset>';
	}

	/**
	 * On click option.
	 *
	 * @return void
	 */
	public function cn_on_click() {
		echo '
		<div id="cn_on_click">
			<label><input type="checkbox" name="cookie_notice_options[on_click]" value="1" ' . checked( true, Cookie_Notice()->options['general']['on_click'], false ) . ' />' . __( 'Enable to accept the notice on any click on the page.', 'cookie-notice' ) . '</label>
		</div>';
	}

	/**
	 * Delete plugin data on deactivation option.
	 *
	 * @return void
	 */
	public function cn_deactivation_delete() {
		echo '
		<div id="cn_deactivation_delete">
			<label><input type="checkbox" name="cookie_notice_options[deactivation_delete]" value="1" ' . checked( true, Cookie_Notice()->options['general']['deactivation_delete'], false ) . '/>' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'cookie-notice' ) . '</label>
		</div>';
	}

	/**
	 * CSS style option.
	 *
	 * @return void
	 */
	public function cn_css_class() {
		echo '
		<div id="cn_css_class">
			<input type="text" class="regular-text" name="cookie_notice_options[css_class]" value="' . esc_attr( Cookie_Notice()->options['general']['css_class'] ) . '" />
			<p class="description">' . __( 'Enter additional button CSS classes separated by spaces.', 'cookie-notice' ) . '</p>
		</div>';
	}

	/**
	 * Colors option.
	 *
	 * @return void
	 */
	public function cn_colors() {
		echo '
		<fieldset>
			<div id="cn_colors">';

		foreach ( $this->colors as $value => $label ) {
			$value = esc_attr( $value );

			echo '
				<div id="cn_colors-' . $value . '"><label>' . esc_html( $label ) . '</label><br />
					<input class="cn_color" type="text" name="cookie_notice_options[colors][' . $value . ']" value="' . esc_attr( Cookie_Notice()->options['general']['colors'][$value] ) . '" />
				</div>';
		}

		$opacity = (int) Cookie_Notice()->options['general']['colors']['bar_opacity'];

		echo '
				<div id="cn_colors-bar_opacity"><label>' . __( 'Bar opacity', 'cookie-notice' ) . '</label><br />
					<div><input id="cn_colors_bar_opacity_range" class="cn_range" type="range" min="50" max="100" step="1" name="cookie_notice_options[colors][bar_opacity]" value="' . $opacity . '" onchange="cn_colors_bar_opacity_text.value = cn_colors_bar_opacity_range.value" /><input id="cn_colors_bar_opacity_text" class="small-text" type="number" onchange="cn_colors_bar_opacity_range.value = cn_colors_bar_opacity_text.value" min="50" max="100" value="' . $opacity . '" /></div>
				</div>';

		echo '
			</div>
		</fieldset>';
	}

	/**
	 * Validate options.
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_options( $input ) {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return $input;

		// get main instance
		$cn = Cookie_Notice();

		$is_network = $cn->is_network_admin();

		if ( isset( $_POST['save_cookie_notice_options'] ) ) {
			// app id
			$input['app_id'] = sanitize_text_field( isset( $input['app_id'] ) ? $input['app_id'] : $cn->defaults['general']['app_id'] );

			// app key
			$input['app_key'] = sanitize_text_field( isset( $input['app_key'] ) ? $input['app_key'] : $cn->defaults['general']['app_key'] );

			// set app status
			if ( ! empty( $input['app_id'] ) && ! empty( $input['app_key'] ) ) {
				$app_data = $cn->welcome_api->get_app_config( $input['app_id'], true );

				if ( $cn->check_status( $app_data['status'] ) === 'active' && $cn->options['general']['app_id'] !== $input['app_id'] && $cn->options['general']['app_key'] !== $input['app_key'] ) {
					// update analytics data
					$cn->welcome_api->get_app_analytics( true );
				}
			} else {
				if ( $is_network )
					update_site_option( 'cookie_notice_status', $cn->defaults['data'] );
				else
					update_option( 'cookie_notice_status', $cn->defaults['data'] );
			}

			// app blocking
			$input['app_blocking'] = isset( $input['app_blocking'] );

			// hide banner
			$input['hide_banner'] = isset( $input['hide_banner'] );

			// debug mode
			$input['debug_mode'] = isset( $input['debug_mode'] );

			// position
			$input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $cn->defaults['general']['position'] );

			// colors
			$input['colors']['text'] = sanitize_text_field( isset( $input['colors']['text'] ) && $input['colors']['text'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['text'] ) === 1 ? $input['colors']['text'] : $cn->defaults['general']['colors']['text'] );
			$input['colors']['button'] = sanitize_text_field( isset( $input['colors']['button'] ) && $input['colors']['button'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['button'] ) === 1 ? $input['colors']['button'] : $cn->defaults['general']['colors']['button'] );
			$input['colors']['bar'] = sanitize_text_field( isset( $input['colors']['bar'] ) && $input['colors']['bar'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['bar'] ) === 1 ? $input['colors']['bar'] : $cn->defaults['general']['colors']['bar'] );
			$input['colors']['bar_opacity'] = absint( isset( $input['colors']['bar_opacity'] ) && $input['colors']['bar_opacity'] >= 50 ? $input['colors']['bar_opacity'] : $cn->defaults['general']['colors']['bar_opacity'] );

			// texts
			$input['message_text'] = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $cn->defaults['general']['message_text'] );
			$input['accept_text'] = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $cn->defaults['general']['accept_text'] );
			$input['refuse_text'] = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $cn->defaults['general']['refuse_text'] );
			$input['revoke_message_text'] = wp_kses_post( isset( $input['revoke_message_text'] ) && $input['revoke_message_text'] !== '' ? $input['revoke_message_text'] : $cn->defaults['general']['revoke_message_text'] );
			$input['revoke_text'] = sanitize_text_field( isset( $input['revoke_text'] ) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $cn->defaults['general']['revoke_text'] );
			$input['refuse_opt'] = isset( $input['refuse_opt'] );
			$input['revoke_cookies'] = isset( $input['revoke_cookies'] );
			$input['revoke_cookies_opt'] = isset( $input['revoke_cookies_opt'] ) && array_key_exists( $input['revoke_cookies_opt'], $this->revoke_opts ) ? $input['revoke_cookies_opt'] : $cn->defaults['general']['revoke_cookies_opt'];

			// get allowed HTML
			$allowed_html = $cn->get_allowed_html();

			// body refuse code
			$input['refuse_code'] = wp_kses( isset( $input['refuse_code'] ) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $cn->defaults['general']['refuse_code'], $allowed_html );

			// head refuse code
			$input['refuse_code_head'] = wp_kses( isset( $input['refuse_code_head'] ) && $input['refuse_code_head'] !== '' ? $input['refuse_code_head'] : $cn->defaults['general']['refuse_code_head'], $allowed_html );

			// css button class
			$input['css_class'] = sanitize_text_field( isset( $input['css_class'] ) ? $input['css_class'] : $cn->defaults['general']['css_class'] );

			// link target
			$input['link_target'] = sanitize_text_field( isset( $input['link_target'] ) && in_array( $input['link_target'], array_keys( $this->link_targets ) ) ? $input['link_target'] : $cn->defaults['general']['link_target'] );

			// time
			$input['time'] = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $cn->defaults['general']['time'] );
			$input['time_rejected'] = sanitize_text_field( isset( $input['time_rejected'] ) && in_array( $input['time_rejected'], array_keys( $this->times ) ) ? $input['time_rejected'] : $cn->defaults['general']['time_rejected'] );

			// script placement
			$input['script_placement'] = sanitize_text_field( isset( $input['script_placement'] ) && in_array( $input['script_placement'], array_keys( $this->script_placements ) ) ? $input['script_placement'] : $cn->defaults['general']['script_placement'] );

			// hide effect
			$input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $cn->defaults['general']['hide_effect'] );

			// redirection
			$input['redirection'] = isset( $input['redirection'] );

			// on scroll
			$input['on_scroll'] = isset( $input['on_scroll'] );

			// on scroll offset
			$input['on_scroll_offset'] = absint( isset( $input['on_scroll_offset'] ) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $cn->defaults['general']['on_scroll_offset'] );

			// on click
			$input['on_click'] = isset( $input['on_click'] );

			// deactivation
			$input['deactivation_delete'] = isset( $input['deactivation_delete'] );

			// privacy policy
			$input['see_more'] = isset( $input['see_more'] );
			$input['see_more_opt']['text'] = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $cn->defaults['general']['see_more_opt']['text'] );
			$input['see_more_opt']['link_type'] = sanitize_text_field( isset( $input['see_more_opt']['link_type'] ) && in_array( $input['see_more_opt']['link_type'], array_keys( $this->links ) ) ? $input['see_more_opt']['link_type'] : $cn->defaults['general']['see_more_opt']['link_type'] );

			if ( $input['see_more_opt']['link_type'] === 'custom' )
				$input['see_more_opt']['link'] = ( $input['see_more'] === true ? esc_url( $input['see_more_opt']['link'] ) : '' );
			elseif ( $input['see_more_opt']['link_type'] === 'page' ) {
				$input['see_more_opt']['id'] = ( $input['see_more'] === true ? (int) $input['see_more_opt']['id'] : 0 );
				$input['see_more_opt']['sync'] = isset( $input['see_more_opt']['sync'] );

				if ( $input['see_more_opt']['sync'] )
					update_option( 'wp_page_for_privacy_policy', $input['see_more_opt']['id'] );
			}

			// policy link position
			$input['link_position'] = sanitize_text_field( isset( $input['link_position'] ) && in_array( $input['link_position'], array_keys( $this->link_positions ) ) ? $input['link_position'] : $cn->defaults['general']['link_position'] );

			// message link position?
			if ( $input['see_more'] === true && $input['link_position'] === 'message' && strpos( $input['message_text'], '[cookies_policy_link' ) === false )
				$input['message_text'] .= ' [cookies_policy_link]';

			$input['update_version'] = $cn->options['general']['update_version'];
			$input['update_notice'] = $cn->options['general']['update_notice'];

			$input['translate'] = false;

			// WPML >= 3.2
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Message in the notice', $input['message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Button text', $input['accept_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Refuse button text', $input['refuse_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Revoke message text', $input['revoke_message_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Revoke button text', $input['revoke_text'] );
				do_action( 'wpml_register_single_string', 'Cookie Notice', 'Privacy policy text', $input['see_more_opt']['text'] );

				if ( $input['see_more_opt']['link_type'] === 'custom' )
					do_action( 'wpml_register_single_string', 'Cookie Notice', 'Custom link', $input['see_more_opt']['link'] );
			}

			add_settings_error( 'cn_cookie_notice_options', 'save_cookie_notice_options', __( 'Settings saved.', 'cookie-notice' ), 'updated' );

			// purge cache on save
			if ( $is_network )
				delete_site_transient( 'cookie_notice_app_cache' );
			else
				delete_transient( 'cookie_notice_app_cache' );
		} elseif ( isset( $_POST['reset_cookie_notice_options'] ) ) {
			$input = $cn->defaults['general'];

			add_settings_error( 'cn_cookie_notice_options', 'reset_cookie_notice_options', __( 'Settings restored to defaults.', 'cookie-notice' ), 'updated' );

			// network area?
			if ( $is_network ) {
				// set app data
				update_site_option( 'cookie_notice_status', $cn->defaults['data'] );

				// purge cache on save
				delete_site_transient( 'cookie_notice_app_cache' );
			} else {
				// set app data
				update_option( 'cookie_notice_status', $cn->defaults['data'] );

				// purge cache on save
				delete_transient( 'cookie_notice_app_cache' );
			}
		}

		return $input;
	}

	/**
	 * Validate network options.
	 *
	 * @return void
	 */
	public function validate_network_options() {
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return;

		// get main instance
		$cn = Cookie_Notice();

		// global network page?
		if ( $cn->is_network_admin() && isset( $_POST['cn-network-settings'] ) ) {
			// network settings
			if ( ! empty( $_POST['cookie_notice_options'] ) && check_admin_referer( 'cookie_notice_options-options', '_wpnonce' ) !== false ) {
				if ( isset( $_POST['save_cookie_notice_options'] ) ) {
					// validate options
					$data = $this->validate_options( $_POST['cookie_notice_options'] );

					// check network settings
					$data['global_override'] = isset( $_POST['cookie_notice_options']['global_override'] );
					$data['global_cookie'] = isset( $_POST['cookie_notice_options']['global_cookie'] );
					$data['update_notice_diss'] = $cn->options['general']['update_notice_diss'];

					if ( $data['global_override'] && ! $cn->options['general']['update_notice_diss'] )
						$data['update_notice'] = true;
					else
						$data['update_notice'] = false;

					// update database
					update_site_option( 'cookie_notice_options', $data );

					// update settings
					$cn->options['general'] = $cn->multi_array_merge( $cn->defaults['general'], get_site_option( 'cookie_notice_options', $cn->defaults['general'] ) );
				} elseif ( isset( $_POST['reset_cookie_notice_options'] ) ) {
					$cn->defaults['general']['update_notice'] = false;
					$cn->defaults['general']['update_notice_diss'] = false;

					// silent options validation
					$this->validate_options( $cn->defaults['general'] );

					// update database
					update_site_option( 'cookie_notice_options', $cn->defaults['general'] );

					// update settings
					$cn->options['general'] = $cn->defaults['general'];
				}
			}

			// update status of cookie compliance
			$cn->set_status();
		}
	}

	/**
	 * Load scripts and styles - admin.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $page ) {
		if ( $page === 'toplevel_page_cookie-notice' ) {
			// get main instance
			$cn = Cookie_Notice();

			wp_enqueue_script( 'cookie-notice-admin', COOKIE_NOTICE_URL . '/js/admin' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.js', [ 'jquery', 'wp-color-picker' ], $cn->defaults['version'] );

			wp_localize_script(
				'cookie-notice-admin',
				'cnArgs',
				[
					'ajaxURL'			=> admin_url( 'admin-ajax.php' ),
					'nonce'				=> wp_create_nonce( 'cn-purge-cache' ),
					'network'			=> (int) $cn->is_network_admin(),
					'resetToDefaults'	=> __( 'Are you sure you want to reset these settings to defaults?', 'cookie-notice' )
				]
			);

			wp_enqueue_style( 'wp-color-picker' );
		}

		wp_enqueue_style( 'cookie-notice-admin', COOKIE_NOTICE_URL . '/css/admin' . ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '' ) . '.css' );
	}

	/**
	 * Load admin style inline, for menu icon only.
	 *
	 * @return void
	 */
	public function admin_print_styles() {
		echo '
		<style>
			a.toplevel_page_cookie-notice .wp-menu-image {
				background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAzMjEgMzIxIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zOnNlcmlmPSJodHRwOi8vd3d3LnNlcmlmLmNvbS8iIHN0eWxlPSJmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLW1pdGVybGltaXQ6MjsiPjxwYXRoIGQ9Ik0zMTcuMjc4LDEzMC40NTFjLTAuODEyLC00LjMwMiAtNC4zMDEsLTcuNTYyIC04LjY0MiwtOC4wODFjLTQuMzU0LC0wLjUyMiAtOC41MDYsMS44MjkgLTEwLjMwNyw1LjgyMmMtMy4xNyw3LjAwMyAtMTAuMTMzLDExLjg3MyAtMTguMjA1LDExLjg2NGMtOC45NTUsMC4wMjIgLTE2LjUxNywtNi4wMjEgLTE5LjAzOCwtMTQuMzE1Yy0xLjUyMSwtNS4wNjMgLTYuNzI0LC04LjA2NCAtMTEuODY1LC02Ljg2M2MtMy4xNjMsMC43NDEgLTYuMTU0LDEuMTcyIC05LjEyNSwxLjE3MmMtMjIuMDM5LC0wLjA0MyAtMzkuOTc2LC0xNy45NzkgLTQwLjAxNSwtNDAuMDE5Yy0wLC0yLjk3IDAuNDMsLTUuOTYyIDEuMTY5LC05LjExM2MxLjIxMiwtNS4xNDEgLTEuNzk5LC0xMC4zNTMgLTYuODYsLTExLjg3M2MtOC4yOTUsLTIuNTEzIC0xNC4zMzcsLTEwLjA3NSAtMTQuMzE5LC0xOS4wMjljLTAuMDA5LC04LjA4MiA0Ljg2NCwtMTUuMDM2IDExLjg2NywtMTguMjA4YzMuOTkxLC0xLjc5OCA2LjM0MSwtNS45NjMgNS44MjIsLTEwLjMwNGMtMC41MjIsLTQuMzUxIC0zLjc4MywtNy44NDMgLTguMDg0LC04LjY1MmMtOS41NDMsLTEuNzkyIC0xOS40MjYsLTIuODUyIC0yOS42MTEsLTIuODUyYy04OC4yOTUsMC4wMjIgLTE2MC4wNDMsNzEuNzcgLTE2MC4wNjUsMTYwLjA2NWMwLjAyMiw4OC4yOTUgNzEuNzcsMTYwLjA0MyAxNjAuMDY1LDE2MC4wNjVjODguMjk1LC0wLjAyMiAxNjAuMDQzLC03MS43NyAxNjAuMDY1LC0xNjAuMDY1Yy0wLC0xMC4xODQgLTEuMDYzLC0yMC4wNjcgLTIuODUyLC0yOS42MTRabS01OC4yMjMsMTI4LjYwNGMtMjUuNDAxLDI1LjM4IC02MC4zNTUsNDEuMDY2IC05OC45OSw0MS4wNjZjLTM4LjYzNSwwIC03My41ODgsLTE1LjY4NiAtOTguOTg5LC00MS4wNjZjLTI1LjM4LC0yNS40MDEgLTQxLjA2NiwtNjAuMzU1IC00MS4wNjYsLTk4Ljk5Yy0wLC0zOC42MzUgMTUuNjg2LC03My41ODggNDEuMDY2LC05OC45ODljMjUuNDAxLC0yNS4zOCA2MC4zNTQsLTQxLjA2NiA5OC45ODksLTQxLjA2NmMxLjgwMSwwIDMuNTYsMC4xODkgNS4zNTIsMC4yNjhjLTMuMzQzLDUuODIzIC01LjM0MywxMi41MjcgLTUuMzUyLDE5LjczOGMwLjAxOCwxNC45MzUgOC4zMDQsMjcuNzQyIDIwLjM3OSwzNC41NzVjLTAuMTkyLDEuNzggLTAuMzczLDMuNTYgLTAuMzczLDUuNDRjMC4wMjIsMzMuMTI1IDI2LjkwMyw2MC4wMDcgNjAuMDI1LDYwLjAyNWMxLjg4LDAgMy42NjQsLTAuMTggNS40NDMsLTAuMzY5YzYuODMzLDEyLjA2NSAxOS42MjgsMjAuMzU2IDM0LjU3MiwyMC4zNzhjNy4yMTUsLTAuMDA5IDEzLjkxNiwtMi4wMTEgMTkuNzQxLC01LjM1MmMwLjA4LDEuNzggMC4yNjksMy41NTEgMC4yNjksNS4zNTJjLTAsMzguNjM1IC0xNS42ODYsNzMuNTg5IC00MS4wNjYsOTguOTlabS01OC45NzQsLTE4Ljk1OWMtMCwxMS4wNTIgLTguOTU4LDIwLjAxIC0yMC4wMSwyMC4wMWMtMTEuMDQ4LC0wIC0yMC4wMDUsLTguOTU4IC0yMC4wMDUsLTIwLjAxYy0wLC0xMS4wNDkgOC45NTcsLTIwLjAwNiAyMC4wMDUsLTIwLjAwNmMxMS4wNTIsLTAgMjAuMDEsOC45NTcgMjAuMDEsMjAuMDA2Wm0tODAuMDMxLC0xMC4wMDVjMCw1LjUyNiAtNC40NzksMTAuMDA1IC0xMC4wMDUsMTAuMDA1Yy01LjUyNiwtMCAtMTAuMDA1LC00LjQ3OSAtMTAuMDA1LC0xMC4wMDVjMCwtNS41MjMgNC40NzksLTEwLjAwMSAxMC4wMDUsLTEwLjAwMWM1LjUyNiwtMCAxMC4wMDUsNC40NzggMTAuMDA1LDEwLjAwMVptMTQwLjA1NSwtMjAuMDA2YzAsNS41MjYgLTQuNDc5LDEwLjAwNSAtMTAuMDA1LDEwLjAwNWMtNS41MjUsMCAtMTAuMDA1LC00LjQ3OSAtMTAuMDA1LC0xMC4wMDVjMCwtNS41MjYgNC40OCwtMTAuMDA1IDEwLjAwNSwtMTAuMDA1YzUuNTI2LDAgMTAuMDA1LDQuNDc5IDEwLjAwNSwxMC4wMDVabS0xNjAuMDY0LC01MC4wMmMtMCwxMS4wNDggLTguOTU3LDIwLjAwNiAtMjAuMDEsMjAuMDA2Yy0xMS4wNDgsMCAtMjAuMDA1LC04Ljk1OCAtMjAuMDA1LC0yMC4wMDZjLTAsLTExLjA1MiA4Ljk1NywtMjAuMDEgMjAuMDA1LC0yMC4wMWMxMS4wNTMsMCAyMC4wMSw4Ljk1OCAyMC4wMSwyMC4wMVptODAuMDMsMTAuMDA1YzAsNS41MjMgLTQuNDc4LDEwLjAwMSAtMTAuMDAxLDEwLjAwMWMtNS41MjYsMCAtMTAuMDA1LC00LjQ3OCAtMTAuMDA1LC0xMC4wMDFjMCwtNS41MjYgNC40NzksLTEwLjAwNSAxMC4wMDUsLTEwLjAwNWM1LjUyMywwIDEwLjAwMSw0LjQ3OSAxMC4wMDEsMTAuMDA1Wm0xMTUuNDkzLC02OS40MDZjMCw1LjUyNiAtNC40NzksMTAuMDA1IC0xMC4wMDUsMTAuMDA1Yy01LjUyNiwtMCAtMTAuMDA1LC00LjQ3OSAtMTAuMDA1LC0xMC4wMDVjMCwtNS41MjYgNC40NzksLTEwLjAwNSAxMC4wMDUsLTEwLjAwNWM1LjUyNiwtMCAxMC4wMDUsNC40NzkgMTAuMDA1LDEwLjAwNVptLTM1LjUyMywtMTkuODc0Yy0wLDExLjUwMyAtOS4zMjUsMjAuODI4IC0yMC44MjgsMjAuODI4Yy0xMS41MDQsLTAgLTIwLjgyOSwtOS4zMjUgLTIwLjgyOSwtMjAuODI4Yy0wLC0xMS41MDMgOS4zMjUsLTIwLjgyOCAyMC44MjksLTIwLjgyOGMxMS41MDMsLTAgMjAuODI4LDkuMzI1IDIwLjgyOCwyMC44MjhabS0xMTkuOTg1LC0wLjc1OWMtMCwxMS4wNTIgLTguOTU3LDIwLjAxIC0yMC4wMDYsMjAuMDFjLTExLjA1MiwtMCAtMjAuMDA5LC04Ljk1OCAtMjAuMDA5LC0yMC4wMWMtMCwtMTEuMDQ4IDguOTU3LC0yMC4wMDYgMjAuMDA5LC0yMC4wMDZjMTEuMDQ5LC0wIDIwLjAwNiw4Ljk1OCAyMC4wMDYsMjAuMDA2WiIgc3R5bGU9ImZpbGw6I2ZmZjtmaWxsLXJ1bGU6bm9uemVybzsiLz48L3N2Zz4=);
				background-position: center center;
				background-repeat: no-repeat;
				background-size: 18px auto;
			}
		</style>
		';
	}

	/**
	 * Register WPML (>= 3.2) strings if needed.
	 *
	 * @global object $wpdb
	 *
	 * @return void
	 */
	private function register_wpml_strings() {
		// get main instance
		$cn = Cookie_Notice();

		global $wpdb;

		// prepare strings
		$strings = [
			'Message in the notice'	=> $cn->options['general']['message_text'],
			'Button text'			=> $cn->options['general']['accept_text'],
			'Refuse button text'	=> $cn->options['general']['refuse_text'],
			'Revoke message text'	=> $cn->options['general']['revoke_message_text'],
			'Revoke button text'	=> $cn->options['general']['revoke_text'],
			'Privacy policy text'	=> $cn->options['general']['see_more_opt']['text'],
			'Custom link'			=> $cn->options['general']['see_more_opt']['link']
		];

		// get query results
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "icl_strings WHERE context = %s", 'Cookie Notice' ) );

		// check results
		foreach( $strings as $string => $value ) {
			// string does not exist?
			if ( ! in_array( $string, $results, true ) ) {
				// register string
				do_action( 'wpml_register_single_string', 'Cookie Notice', $string, $value );
			}
		}
	}

	/**
	 * Display errors and notices.
	 *
	 * @global string $pagenow
	 *
	 * @return void
	 */
	public function settings_errors() {
		global $pagenow;

		// force display notices in top menu settings page
		if ( $pagenow == 'options-general.php' )
			return;

		settings_errors( 'cn_cookie_notice_options' );
	}

	/**
	 * Save compliance config caching.
	 *
	 * @return void
	 */
	public function ajax_purge_cache() {
		if ( ! check_ajax_referer( 'cn-purge-cache', 'nonce' ) )
			echo false;

		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			echo false;
		
		// get main instance
		$cn = Cookie_Notice();

		// delete cache
		if ( $cn->is_network_admin() )
			delete_site_transient( 'cookie_notice_app_cache' );
		else
			delete_transient( 'cookie_notice_app_cache' );
		
		// request for new config data too
		$cn->welcome_api->get_app_config( '', true );

		echo true;
		exit;
	}
}