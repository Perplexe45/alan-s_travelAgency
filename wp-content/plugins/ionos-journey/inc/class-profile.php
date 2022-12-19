<?php

namespace Ionos\Journey;

class Profile {

	const FALLBACK_LANG = 'en';
	private $translations;

	public function __construct() {
		$language      = strtolower( explode( '_', \get_locale() )[0] );
		$this->translations = json_decode( Config::get( 'data.' . $language . '_interface' ), true ) ??
		                      json_decode( Config::get( 'data.' . self::FALLBACK_LANG . '_interface' ), true );

		\add_action( 'show_user_profile', array($this, 'add_profile_option'));
		\add_action( 'edit_user_profile', array($this, 'add_profile_option'));

		\add_action( 'personal_options_update', array($this, 'save_profile_option'));
		\add_action( 'edit_user_profile_update', array($this, 'save_profile_option'));
	}

	function add_profile_option($user){
		echo "<h3>".$this->translations['name']."</h3>";
		echo '<table class="form-table">';

		echo $this->create_checkbox('journey_show',
			$this->translations['settings']['visibility'], $this->translations['settings']['visibility_description'],
			$this->isEnabled($user->ID, 'journey_show'));

		echo '</table>';
	}

	private function create_checkbox($id, $name, $description, $checked = false): string {
		$template = '<tr><th><label for="%s">%s</label></th><td><label for="%s"><input type="checkbox" name="%s" id="%s" %s>%s</label></td></tr>';
		$checked_str = "";
		if($checked) $checked_str = "checked";
		return sprintf($template,
			$id, $name,
			$id, $id, $id, $checked_str,
			$description
		);
	}

	function save_profile_option( $user_id ) {
		if (empty($_POST['_wpnonce'] ) || !\wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
			return;
		}

		if ( !\current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		\update_user_meta($user_id, 'journey_show', isset($_POST['journey_show']) ? 1 : 0);
	}

	public static function isEnabled($user_id, $option): bool {
		if(strlen(\get_user_meta($user_id, $option, true)) <= 0){
			return true;
		}
		return \get_user_meta($user_id, $option, true) == 1;
	}
}