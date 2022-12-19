<?php

use Assistant\Wizard\Manager;

/**
 * @global $args
 */
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );
?>
	<p><?php _e( 'Tell us what you plan to do with your new website. The Assistant will use this info to suggest fitting Designs and useful Plugins. But don’t worry, this is not a final decision. You can change every aspect of your site later.', 'ionos-assistant' ); ?></p>

	<div class="usecases">
<?php
foreach ( $args['use_cases'] as $key => $value ) {
	if ( ! isset( $value['headline'] ) || ! isset( $value['icon'] ) ) {
		continue;
	}

	$icon = "<img src=\"data:image/svg+xml;base64,{$value['icon']}\">";
	if ( strpos( $value['icon'], 'dashicons' ) !== false ) {
		$icon = "<span class='dashicons {$value['icon']}'></span>";
	}

	$use_case_headline = $value['headline'];
	printf(
		'<div class="usecase"><input class="casebtn" type="submit" name="%5$s" value="%1$s" id="%2$s" required><label class="case-label" for="%2$s">%4$s%3$s</label></div>',
		$key,
		"ionos_assistant_wizard_use_case_$key",
		__( $use_case_headline, 'ionos-assistant' ),
		$icon,
		Manager::STATE_INPUT_NAMES['use_case']
	);
}
?>
	</div>

<?php
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
