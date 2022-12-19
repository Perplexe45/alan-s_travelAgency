<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie_Notice_Dashboard class.
 *
 * @class Cookie_Notice_Dashboard
 */
class Cookie_Notice_Dashboard {

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// actions
		add_action( 'wp_dashboard_setup', [ $this, 'wp_dashboard_setup' ], 11 );
		add_action( 'wp_network_dashboard_setup', [ $this, 'wp_dashboard_setup' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts_styles' ] );

		// site status
		add_filter( 'site_status_tests', [ $this, 'add_tests' ] );
	}

	/**
	 * Initialize widget.
	 *
	 * @global array $wp_meta_boxes
	 *
	 * @return void
	 */
	public function wp_dashboard_setup() {
		// filter user_can_see_stats
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return;

		// get main instance
		$cn = Cookie_Notice();

		// check when to hide widget
		if ( is_multisite() ) {
			// site dashboard
			if ( current_action() === 'wp_dashboard_setup' && $cn->is_plugin_network_active() && $cn->network_options['global_override'] )
				return;

			// network dashboard
			if ( current_action() === 'wp_network_dashboard_setup' ) {
				if ( $cn->is_plugin_network_active() ) {
					if ( ! $cn->network_options['global_override'] )
						return;
				} else
					return;
			}
		}

		// check is it network admin
		if ( $cn->is_network_admin() )
			$dashboard_key = 'dashboard-network';
		else
			$dashboard_key = 'dashboard';

		global $wp_meta_boxes;

		// set widget key
		$widget_key = 'cn_dashboard_stats';

		// add dashboard chart widget
		wp_add_dashboard_widget( $widget_key, __( 'Cookie Compliance', 'cookie-notice' ), [ $this, 'dashboard_widget' ] );

		// get widgets
		$normal_dashboard = $wp_meta_boxes[$dashboard_key]['normal']['core'];

		// attempt to place the widget at the top
		$widget_instance = [
			$widget_key	=> $normal_dashboard[ $widget_key ]
		];

		// remove new widget
		unset( $normal_dashboard[ $widget_key ] );

		// merge widgets
		$sorted_dashboard = array_merge( $widget_instance, $normal_dashboard );

		// update widgets
		$wp_meta_boxes[$dashboard_key]['normal']['core'] = $sorted_dashboard;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $pagenow
	 * @return void
	 */
	public function admin_scripts_styles( $pagenow ) {
		if ( $pagenow !== 'index.php' )
			return;

		// filter user_can_see_stats
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return;

		// get main instance
		$cn = Cookie_Notice();

		$date_format = get_option( 'date_format' );

		if ( is_multisite() && $cn->is_network_admin() && $cn->is_plugin_network_active() && $cn->network_options['global_override'] )
			$analytics = get_site_option( 'cookie_notice_app_analytics' );
		else
			$analytics = get_option( 'cookie_notice_app_analytics' );

		// styles
		wp_enqueue_style( 'cn-admin-dashboard', COOKIE_NOTICE_URL . '/css/admin-dashboard.css', [], $cn->defaults['version'] );
		wp_enqueue_style( 'cn-microtip', COOKIE_NOTICE_URL . '/assets/microtip/microtip.min.css', [], $cn->defaults['version'] );

		// bail if compliance is not active
		if ( $cn->get_status() !== 'active' )
			return;

		// scripts
		wp_enqueue_script( 'cn-admin-chartjs', COOKIE_NOTICE_URL . '/assets/chartjs/chart.min.js', [ 'jquery' ], $cn->defaults['version'], true );
		wp_enqueue_script( 'cn-admin-dashboard', COOKIE_NOTICE_URL . '/js/admin-dashboard.js', [ 'jquery', 'cn-admin-chartjs' ], $cn->defaults['version'], true );

		// cycle usage data
		$cycle_usage = [
			'threshold'	=> ! empty( $analytics['cycleUsage']->threshold ) ? (int) $analytics['cycleUsage']->threshold : 0,
			'visits'	=> ! empty( $analytics['cycleUsage']->visits ) ? (int) $analytics['cycleUsage']->visits : 0
		];

		// available visits, -1 for no pro plans
		$cycle_usage['visits_available'] = $cycle_usage['threshold'] ? $cycle_usage['threshold'] - $cycle_usage['visits'] : -1;
		$cycle_usage['threshold_used'] = $cycle_usage['threshold'] ? ( $cycle_usage['visits'] / $cycle_usage['threshold'] ) * 100 : 0;
		$cycle_usage['threshold_used'] = $cycle_usage['threshold_used'] > 100 ? 100 : $cycle_usage['threshold_used'];

		$chartdata = [
			'usage' => [
				'type'	=> 'doughnut',
				'data'	=> [
					'labels'	=> [
						_x( 'Used', 'threshold limit', 'cookie-notice' ),
						_x( 'Free', 'threshold limit', 'cookie-notice' )
					],
					'datasets'	=> [
						[
							'data'				=> [ $cycle_usage['visits'], $cycle_usage['visits_available'] ],
							// 'borderColor'		=> 'rgb(255, 255, 255)',
							'backgroundColor'	=> [
								'rgb(32, 193, 158)',
								'rgb(235, 233, 235)'
							]
						]
					]
				]
			],
			'consent-activity' => [
				'type'	=> 'line'
			]
		];

		// warning usage color
		if ( $cycle_usage['threshold_used'] > 80 && $cycle_usage['threshold_used'] < 100 )
			$chartdata['usage']['data']['datasets'][0]['backgroundColor'][0] = 'rgb(255, 193, 7)';
		// danger usage color
		elseif ( $cycle_usage['threshold_used'] == 100 )
			$chartdata['usage']['data']['datasets'][0]['backgroundColor'][0] = 'rgb(220, 53, 69)';

		$data = [
			'labels' => [],
			'datasets' => [
				0 => [
					'label'					=> sprintf( __( 'Level %s', 'cookie-notice' ), 1 ),
					'data'					=> [],
					'fill'					=> true,
					'backgroundColor'		=> 'rgba(196, 196, 196, 0.3)',
					'borderColor'			=> 'rgba(196, 196, 196, 1)',
					'borderWidth'			=> 1.2,
					'borderDash'			=> [],
					'pointBorderColor'		=> 'rgba(196, 196, 196, 1)',
					'pointBackgroundColor'	=> 'rgba(255, 255, 255, 1)',
					'pointBorderWidth'		=> 1.2
				],
				1 => [
					'label'					=> sprintf( __( 'Level %s', 'cookie-notice' ), 2 ),
					'data'					=> [],
					'fill'					=> true,
					'backgroundColor'		=> 'rgba(213, 181, 101, 0.3)',
					'borderColor'			=> 'rgba(213, 181, 101, 1)',
					'borderWidth'			=> 1.2,
					'borderDash'			=> [],
					'pointBorderColor'		=> 'rgba(213, 181, 101, 1)',
					'pointBackgroundColor'	=> 'rgba(255, 255, 255, 1)',
					'pointBorderWidth'		=> 1.2
				],
				2 => [
					'label'					=> sprintf( __( 'Level %s', 'cookie-notice' ), 3 ),
					'data'					=> [],
					'fill'					=> true,
					'backgroundColor'		=> 'rgba(152, 145, 177, 0.3)',
					'borderColor'			=> 'rgba(152, 145, 177, 1)',
					'borderWidth'			=> 1.2,
					'borderDash'			=> [],
					'pointBorderColor'		=> 'rgba(152, 145, 177, 1)',
					'pointBackgroundColor'	=> 'rgba(255, 255, 255, 1)',
					'pointBorderWidth'		=> 1.2
				]
			]
		];

		// generate chart days
		$chart_date_format = 'j/m';

		for ( $i = 30; $i >= 0; $i-- ) {
			// set label
			$data['labels'][] = date( $chart_date_format, strtotime( '-'. $i .' days' ) );

			// reset datasets
			$data['datasets'][0]['data'][] = 0;
			$data['datasets'][1]['data'][] = 0;
			$data['datasets'][2]['data'][] = 0;
		}

		if ( ! empty( $analytics['consentActivities'] ) && is_array( $analytics['consentActivities'] ) ) {
			// set consent records in charts days
			foreach ( $analytics['consentActivities'] as $index => $entry ) {
				$time = date_i18n( $chart_date_format, strtotime( $entry->eventdt ) );
				$records = $entry->totalrecd;
				$level = $entry->consentlevel;

				$i = array_search( $time, $data['labels'] );

				if ( $i )
					$data['datasets'][$level - 1]['data'][$i] = $records;
			}
		}

		$chartdata['consent-activity']['data'] = $data;

		wp_localize_script(
			'cn-admin-dashboard',
			'cnDashboardArgs',
			[
				'ajaxURL'	=> admin_url( 'admin-ajax.php' ),
				'nonce'		=> wp_create_nonce( 'cn-dashboard-widget' ),
				'nonceUser'	=> wp_create_nonce( 'cn-dashboard-user-options' ),
				'charts'	=> $chartdata
			]
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		// get main instance
		$cn = Cookie_Notice();

		if ( $cn->is_network_admin() )
			$upgrade_url = network_admin_url( 'admin.php?page=cookie-notice&welcome=1' );
		else
			$upgrade_url = admin_url( 'admin.php?page=cookie-notice&welcome=1' );

		$html = '';

		// compliance active, display chart
		if ( $cn->get_status() === 'active' ) {
			// get user options
			$user_options = get_user_meta( get_current_user_id(), 'pvc_dashboard', true );

			// empty options?
			if ( empty( $user_options ) || ! is_array( $user_options ) )
				$user_options = [];

			// sanitize options
			$user_options = map_deep( $user_options, 'sanitize_text_field' );

			// get menu items
			$menu_items = ! empty( $user_options['menu_items'] ) ? $user_options['menu_items'] : [];

			$items = [
				[
					'id'			=> 'visits',
					'title'			=> __( 'Traffic Overview', 'cookie-notice' ),
					'description'	=> __( 'Displays the general visits information for your domain.', 'cookie-notice' )
				],
				[
					'id'			=> 'consent-activity',
					'title'			=> __( 'Consent Activity', 'cookie-notice' ),
					'description'	=> __( 'Displays the chart of the domain consent activity in the last 30 days.', 'cookie-notice' )
				]
			];

			$html .= '
			<div id="cn-dashboard-accordion" class="cn-accordion">';

			foreach ( $items as $item ) {
				$html .= $this->widget_item( $item, $menu_items );
			}

			$html .= '
			</div>';
		// compliance inactive, display image
		} else {
			$html .= '
			<div id="cn-dashboard-accordion" class="cn-accordion cn-widget-block">
				<img src="' . COOKIE_NOTICE_URL . '/img/cookie-compliance-widget.png" alt="Cookie Compliance widget" />
				<div id="cn-dashboard-upgrade">
					<div id="cn-dashboard-modal">
						<h2>' . __( 'View consent activity inside WordPress Dashboard', 'cookie-notice' ) . '</h2>
						<p>' . __( 'Display information about the visits.', 'cookie-notice' ) . '</p>
						<p>' . __( 'Get Consent logs data for the last 30 days.', 'cookie-notice' ) . '</p>
						<p>' . __( 'Enable consent purpose categories, automatic cookie blocking and more.', 'cookie-notice' ) . '</p>
						<p><a href="' . $upgrade_url . '" class="button button-primary button-hero cn-button">' . __( 'Upgrade to Cookie Compliance', 'cookie-notice' ) . '</a></p>
					</div>
				</div>
			</div>';
		}

		echo $html;
	}

	/**
	 * Generate dashboard widget item HTML.
	 *
	 * @param array $item
	 * @param array $menu_items
	 * @return string
	 */
	public function widget_item( $item, $menu_items ) {
		// allows a list of html entities such as
		$allowed_html = wp_kses_allowed_html( 'post' );
		$allowed_html['canvas'] = [
			'id'		=> [],
			'height'	=> []
		];

		return '
		<div id="cn-' . esc_attr( $item['id'] ) . '" class="cn-accordion-item' . ( in_array( $item['id'], $menu_items, true ) ? ' cn-collapsed' : '' ) . '">
			<div class="cn-accordion-header">
				<div class="cn-accordion-toggle"><span class="cn-accordion-title">' . esc_html( $item['title'] ) . '</span><span class="cn-tooltip" aria-label="' . esc_html( $item['description'] ) . '" data-microtip-position="top" data-microtip-size="large" role="tooltip"><span class="cn-tooltip-icon"></span></span></div>
			</div>
			<div class="cn-accordion-content">
				<div class="cn-dashboard-container">
					<div class="cn-data-container">
						' . wp_kses( $this->widget_item_content( $item['id'] ), $allowed_html ) . '
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>';
	}

	/**
	 * Generate dashboard widget item content HTML.
	 *
	 * @param array $item
	 * @return string
	 */
	public function widget_item_content( $item ) {
		$html = '';

		switch ( $item ) {
			case 'visits':
				// get main instance
				$cn = Cookie_Notice();

				$date_format = get_option( 'date_format' );

				// get analytics data options
				if ( is_multisite() && $cn->is_network_admin() && $cn->is_plugin_network_active() && $cn->network_options['global_override'] )
					$analytics = get_site_option( 'cookie_notice_app_analytics' );
				else
					$analytics = get_option( 'cookie_notice_app_analytics' );

				// thirty days data
				$thirty_days_usage = [
					'visits'			=> ! empty( $analytics['thirtyDaysUsage']->visits ) ? (int) $analytics['thirtyDaysUsage']->visits : 0,
					// 'visits_updated'	=> ! empty( $analytics['thirtyDaysUsage']->fetchTime ) ? date_create_from_format( 'Y-m-d H:i:s', $analytics['thirtyDaysUsage']->fetchTime ) : date_create_from_format( 'Y-m-d H:i:s', current_time( 'mysql', true ) ),
					'consents'			=> 0,
					'consents_updated'	=> ! empty( $analytics['lastUpdated'] ) ? date_create_from_format( 'Y-m-d H:i:s', $analytics['lastUpdated'] ) : date_create_from_format( 'Y-m-d H:i:s', current_time( 'mysql', true ) )
				];

				if ( ! empty( $analytics['consentActivities'] ) ) {
					foreach ( $analytics['consentActivities'] as $index => $entry ) {
						$thirty_days_usage['consents'] += (int) $entry->totalrecd;
					}
				}

				// cycle usage data
				$cycle_usage = [
					'threshold'		=> ! empty( $analytics['cycleUsage']->threshold ) ? (int) $analytics['cycleUsage']->threshold : 0,
					'visits'		=> ! empty( $analytics['cycleUsage']->visits ) ? (int) $analytics['cycleUsage']->visits : 0,
					'days_to_go'	=> ! empty( $analytics['cycleUsage']->daysToGo ) ? (int) $analytics['cycleUsage']->daysToGo : 0,
					'start_date'	=> ! empty( $analytics['cycleUsage']->startDate ) ? date_create_from_format( '!Y-m-d', $analytics['cycleUsage']->startDate ) : '',
					// 'end_date'		=> ! empty( $analytics['cycleUsage']->endDate ) ? date_create_from_format( '!Y-m-d', $analytics['cycleUsage']->endDate ) : ''
				];

				// set current timezone
				$current_timezone = new DateTimeZone( $cn->timezone_string() );

				// update dates
				// $thirty_days_usage['visits_updated']->setTimezone( $current_timezone );
				$thirty_days_usage['consents_updated']->setTimezone( $current_timezone );

				// available visits, -1 for no pro plans
				$cycle_usage['visits_available'] = $cycle_usage['threshold'] ? $cycle_usage['threshold'] - $cycle_usage['visits'] : -1;
				$cycle_usage['threshold_used'] = $cycle_usage['threshold'] ? ( $cycle_usage['visits'] / $cycle_usage['threshold'] ) * 100 : 0;
				$cycle_usage['threshold_used'] = $cycle_usage['threshold_used'] > 100 ? 100 : $cycle_usage['threshold_used'];

				$html .= '
					<div id="cn-dashboard-' . $item . '">
						<div id="cn-' . $item . '-infobox-traffic-overview" class="cn-infobox-container">
							<div id="cn-' . $item . '-infobox-visits" class="cn-infobox">
								<div class="cn-infobox-title">' . __( 'Total Visits', 'cookie-notice' ) . '</div>
								<div class="cn-infobox-number">' . number_format_i18n( $thirty_days_usage['visits'], 0 ) . '</div>
								<div class="cn-infobox-subtitle">' . __( 'Last 30 days', 'cookie-notice' ) . '</div>
							</div>
							<div id="cn-' . $item . '-infobox-consents" class="cn-infobox">
								<div class="cn-infobox-title">' . __( 'Consent Logs', 'cookie-notice' ) . '</div>
								<div class="cn-infobox-number">' . number_format_i18n( $thirty_days_usage['consents'], 0 ) . '</div>
								<div class="cn-infobox-subtitle">' . sprintf( __( 'Updated %s', 'cookie-notice' ), date_i18n( $date_format, $thirty_days_usage['consents_updated']->getTimestamp() ) ) . '</div>
							</div>
						</div>';

				if ( $cycle_usage['threshold'] ) {
					$usage_class = 'success';

					// warning usage color
					if ( $cycle_usage['threshold_used'] > 80 && $cycle_usage['threshold_used'] < 100 )
						$usage_class = 'warning';
					// danger usage color
					elseif ( $cycle_usage['threshold_used'] == 100 )
						$usage_class = 'danger';

					$html .= '
						<div id="cn-' . $item . '-infobox-traffic-usage" class="cn-infobox-container">
							<div id="cn-' . $item . '-infobox-limits" class="cn-infobox">
								<div class="cn-infobox-title">' . __( 'Traffic Usage', 'cookie-notice' ) . '</div>
								<div class="cn-infobox-number cn-text-' . $usage_class . '">' . number_format_i18n( $cycle_usage['threshold_used'], 1 ) . ' %</div>
								<div class="cn-infobox-subtitle">
									<p>' . sprintf( __( 'Visits usage: %1$s / %2$s', 'cookie-notice' ), $cycle_usage['visits'], $cycle_usage['threshold'] ) . '</p>
									<p>' . sprintf( __( 'Cycle started: %s', 'cookie-notice' ), date_i18n( $date_format, $cycle_usage['start_date']->getTimestamp() ) ) . '</p>
									<p>' . sprintf( __( 'Days to go: %s', 'cookie-notice' ), $cycle_usage['days_to_go'] ) . '</p>
								</div>
							</div>
							<div id="cn-' . $item . '-chart-limits" class="cn-infobox cn-chart-container">
								<canvas id="cn-usage-chart" style="height: 100px;"></canvas>
							</div>';

					/*
							<div id="cn-' . $item . '-traffic-notice" class="cn-infobox-notice cn-traffic-' . $usage_class . '">
								<p><b>' . __( 'Your domain has exceeded 90% of the usage limit.', 'cookie-notice' ) . '</b></p>
								<p>' . sprintf(__( 'The banner will still display properly and consent record will be set in the browser. However the Autoblocking will be disabled and Consent Records will not be stored in the application until the current visits cycle resets (in %s days).', 'cookie-notice' ), $cycle_usage['days_to_go'] ) . '</p>
							</div>
					 */

					$html .= '
						</div>';
				}

				$html .= '
					</div>';
				break;

			case 'consent-activity':
				$html .= '
					<div id="cn-dashboard-' . $item . '">
						<div id="cn-' . $item . '-chart-container cn-chart-container">
							<canvas id="cn-' . $item . '-chart" style="height: 300px;"></canvas>
						</div>
					</div>';
				break;
		}

		return $html;
	}

	/**
	 * Add site test.
	 *
	 * @param array $tests
	 * @return array
	 */
	public function add_tests( $tests ) {
		$tests['direct']['cookie_compliance_status'] = [
			'label'	=> __( 'Cookie Compliance Status', 'cookie-notice' ),
			'test'	=> [ $this, 'test_cookie_compliance' ]
		];

		return $tests;
	}

	/**
	 * Test for Cookie Compliance.
	 *
	 * @return array|void
	 */
	public function test_cookie_compliance() {
		if ( Cookie_Notice()->get_status() !== 'active' ) {
			return [
				'label'			=> __( 'Your site does not have Cookie Compliance', 'cookie-notice' ),
				'status'		=> 'recommended',
				'badge'			=> [
					'label'	=> __( 'Cookie Notice', 'cookie-notice' ),
					'color'	=> 'blue'
				],
				'description'	=> __( "Run Compliance Check to determine your site's compliance with updated data processing and consent rules under GDPR, CCPA and other international data privacy laws.", 'cookie-notice' ),
				'actions'		=> sprintf( '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>', admin_url( 'admin.php?page=cookie-notice&welcome=1' ), __( 'Run Compliance Check', 'cookie-notice' ) ),
				'test'			=> 'cookie_compliance_status'
			];
		}
	}
}