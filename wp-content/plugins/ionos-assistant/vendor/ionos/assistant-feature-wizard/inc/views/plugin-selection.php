<?php
/**
 * @global $args
 */

use Assistant\Wizard\Controllers\Plugin_Selection;
use Assistant\Wizard\Manager;
use Assistant\Wizard\View_Helper;

load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header-bigcontent.php', true, $args );

View_Helper::print_hidden_fields(
	array(
		Manager::STATE_INPUT_NAMES['use_case'],
		Manager::STATE_INPUT_NAMES['theme'],
	)
);
?>
	<p><?php _e( 'WordPress Plugins extend WordPress functionality in almost any form imaginable. We picked a few useful plugins from the thousands of available plugins to make starting your website more manageable. If you want to add more plugins, you can do so after finishing this Assistant.', 'ionos-assistant' ); ?></p>

	<?php if ( ! empty( $args['optional_plugins'] ) ) : ?>
	<h2>Plugins we recommend</h2>
	<div class="plugins-grid">
		<?php foreach ( $args['optional_plugins'] as $key => $info ) : ?>
		<div class="plugin-toggle">
			<?php $id = "assistant_wizard_plugin_$key"; ?>
			<input
				type="checkbox"
				name="plugins[]"
				value="<?php echo esc_attr( $key ); ?>"
				id="<?php echo esc_attr( $id ); ?>"
				class="toggle-input"
				<?php checked( in_array( $key, $args['selected_plugins'] ), true ); ?>
			>
			<label for="<?php echo esc_attr( $id ); ?>" class="toggle">
				<img class="plugin-img" alt="<?php echo $key; ?>" src="<?php echo Plugin_Selection::get_plugin_icon_url( $key ); ?>">
				<div class="plugin-text">
					<h3><?php echo Plugin_Selection::get_plugin_name( $key ); ?></h3>
					<p><?php echo Plugin_Selection::get_plugin_description( $key ); ?></p>
				</div>
			</label>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<?php if ( ! empty( $args['required_plugins'] ) ) : ?>
	<h2>Plugins included with your selection</h2>
	<div class="plugins-grid">
		<?php foreach ( $args['required_plugins'] as $key => $info ) : ?>
		<div class="plugin">
			<img class="plugin-img" src="<?php echo Plugin_Selection::get_plugin_icon_url( $key ); ?>">
			<div class="plugin-text">
				<h3><?php echo Plugin_Selection::get_plugin_name( $key ); ?></h3>
				<p><?php echo Plugin_Selection::get_plugin_description( $key ); ?></p>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<div class="buttons">
		<button class="btn primary-btn" type="submit"><?php _e( 'Next Step', 'ionos-assistant' ); ?></button>
		<button class="link-btn" type="submit" name="step" value="theme-preview"><?php _e( 'Back', 'ionos-assistant' ); ?></button>
	</div>
<?php
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
