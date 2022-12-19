<?php
/**
 * Plugin Name: 1&1 Product Subdomain
 * Plugin URI: http://www.1and1.com/
 * Description: Handles product subdomain installs in accordance with search engines best practices.
 * Version: 1.1.0
 * Author: 1&1
 * Author URI: http://www.1and1.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Product_Subdomains {

	private $system_domains = array(
		'.apps-1and1.net',
		'.apps-1and1.com',
		'.online.de',
		'.live-website.com'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( $this->is_system_domain() ) {
			add_action( 'admin_init', array( $this, 'set_blog_to_private' ), 1000, 0 );
			if ( $this->is_options_reading_page() ) {
				add_action( 'admin_footer', array( $this, 'disable_checkbox' ), 1000, 0 );
			}
		}
	}

	/**
	 * Checks whether the home_url contains a system domain.
	 *
	 * @return bool
	 */
	private function is_system_domain() {
		$host = parse_url( get_home_url(), PHP_URL_HOST );

		foreach ( $this->system_domains as $sys_domain ) {
			if ( stripos( $host, $sys_domain ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Disables the checkbox on options-reading.php
	 */
	public function disable_checkbox() {
		echo '<script>document.querySelector("#blog_public").disabled = true</script>';
	}

	/**
	 * Sets the blog to private (prevent crawling)
	 */
	public function set_blog_to_private() {
		if ( current_user_can( 'manage_options' ) ) {
			update_option( 'blog_public', 0 );
		}
	}


	private function is_options_reading_page() {
		return stripos( admin_url( 'options-reading.php' ), $_SERVER['SCRIPT_NAME'] );
	}

}

new Product_Subdomains;