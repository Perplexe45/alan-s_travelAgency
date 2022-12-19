<?php
// Todo: Use class attributes instead of using a global var
/**
 * @global $args
 */
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );
?>

	<p><?php _e( 'Our Assistant will help you to get started with WordPress. You can select a Theme and pick from a list of suitable Plugins for your use case. After completing the following steps, you can start creating content and put some finishing touches to your website!', 'ionos-assistant' ); ?></p>
	<button class="btn primary-btn" type="submit"><?php _e( 'Let‘s go!', 'ionos-assistant' ); ?></button>
	<button class="link-btn" type="submit" name="step" value="abort-plugin-selection"><?php _e( 'I want to configure WordPress installation myself', 'ionos-assistant' ); ?></button>

<?php
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
