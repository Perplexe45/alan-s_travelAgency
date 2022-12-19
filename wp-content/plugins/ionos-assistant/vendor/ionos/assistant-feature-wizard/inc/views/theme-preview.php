<?php
/**
 * @global $args
 */

use Assistant\Wizard\Manager;
use Ionos\Assistant\Config;
use Assistant\Wizard\View_Helper;

$use_case       = $args[ Manager::STATE_INPUT_NAMES['use_case'] ];
$theme          = $args[ Manager::STATE_INPUT_NAMES['theme'] ];
$preview_link   = $args[ Manager::STATE_INPUT_NAMES['preview_link'] ];
$screenshot_url = $args['info']['screenshot_url'];
$description    = Config::get( "features.wizard.usecases.$use_case.themes.$theme.description" );

if ( ! $description ) {
	if ( isset( $args['info']['sections'] ) && isset( $args['info']['sections']['description'] ) ) {
		$description = $args['info']['sections']['description'];
	}
}

load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );

View_Helper::print_hidden_fields(
	array(
		Manager::STATE_INPUT_NAMES['use_case'],
		Manager::STATE_INPUT_NAMES['theme'],
	)
);

?>
	<img class="preview-img" src="<?php echo $screenshot_url; ?>" alt="<?php echo $theme; ?>">
	<div class="preview-text">
		<a class="link-btn" target="_blank" href="<?php echo $preview_link; ?>"><span class="dashicons dashicons-search"></span><?php _e( 'Theme Preview', 'ionos-assistant' ); ?></a>
		<?php
		if ( $description ) {
			echo "<p class='theme-description'>$description</p>";
		}
		?>
	</div>

	<div class="buttons">
		<button class="btn primary-btn" type="submit"><?php _e( 'Next Step', 'ionos-assistant' ); ?></button>
		<button class="link-btn" type="submit" name="step" value="theme-selection"><?php _e( 'Back', 'ionos-assistant' ); ?></button>
	</div>
<?php
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
