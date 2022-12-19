<?php

namespace Ionos\Performance;

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Menu class
 * Provides an unified way to add multiple submenu items for a toplevel menu point
 */
class Menu {
	/**
	 * Adds a submenu page. If the toplevel menu pages doesn’t exist, it will added too.
	 *
	 * @param $page_title
	 * @param $menu_title
	 * @param $capability
	 * @param $menu_slug
	 * @param $function
	 * @param $position
	 */
	public static function add_submenu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $position ) {
		if ( empty( menu_page_url( self::get_slug(), false ) ) ) {
			self::add_menu_page();
		}

		add_submenu_page(
			sanitize_title( self::get_tenant() ),
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$function,
			$position
		);
	}

	/**
	 * Removes the unwanted submenu item named like the tenant
	 *
	 * After adding a toplevel and submenu page there will be a submenu item with the tenant name.
	 * This method removes this item because we don’t want it to be there.
	 */
	public static function remove_unwanted_submenu_item() {
		remove_submenu_page( Menu::get_slug(), Menu::get_slug() );
	}

	private static function add_menu_page() {
		add_menu_page(
			self::get_tenant(),
			self::get_tenant(),
			'manage_options',
			self::get_slug()
		);
	}

	/**
	 * Returns the tenant name as slugified lowercase version
	 *
	 * @return string
	 */
	private static function get_slug() {
		return strtolower( sanitize_title( self::get_tenant() ) );
	}

	/**
	 * Returns the tenant name, fetches it via Meta class if necessary
	 *
	 * @return string
	 */
	private static function get_tenant() {
		return Meta::get_meta( 'AuthorName' );
	}
}

add_action( 'admin_menu', array( Menu::class, 'remove_unwanted_submenu_item' ), 999 );