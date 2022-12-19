<?php

use Assistant\Wizard\Manager;
use Assistant\Wizard\View_Helper;

/**
 * @global $args
 */
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );
$input_name = Manager::STATE_INPUT_NAMES['use_case'];

View_Helper::print_hidden_fields( array( Manager::STATE_INPUT_NAMES['use_case'] ) );

?>
	<p><?php _e( 'In WordPress, Themes are design packages ready to be used on your site. Different themes come with different page layouts and give your website character. If none of the pre-selected themes fits your needs, you can install any other theme after finalizing this Assistant.', 'ionos-assistant' ); ?></p>

	<div class="themes">
<?php
foreach ( $args['themes'] as $theme ) {
	if ( isset( $theme['screenshot_url'] ) ) {
		printf(
			'<div class="theme">
                        <input class="themebtn" type="submit" name="%5$s" value="%1$s" id="%2$s" required>
                        <label class="case-label theme-label" for="%2$s" required>
                            <img class="theme-image" src="%3$s" alt="%4$s">
                            <h2 class="theme-name">%4$s</h2>
                            <div class="overlay">
                                <p class="details">Theme details</p>
                            </div>
                            <div class="selection"></div>
                        </label>
                    </div>',
			$theme['slug'],
			"ionos_assistant_wizard_theme_{$theme['slug']}",
			$theme['screenshot_url'],
			$theme['name'],
			Manager::STATE_INPUT_NAMES['theme']
		);
	}
}
?>
	</div>
    <button class="link-btn" type="submit" name="step" value="use-case-selection"><?php _e( 'Back', 'ionos-assistant' ); ?></button>

<?php
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
