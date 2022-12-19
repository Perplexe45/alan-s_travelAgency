<?php
/**
 * @global $args
 */

use Assistant\Wizard\Controllers\Summary;
use Assistant\Wizard\Manager;
use Assistant\Wizard\View_Helper;

load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );

$use_case       = $args[ Manager::STATE_INPUT_NAMES['use_case'] ];
$theme          = $args[ Manager::STATE_INPUT_NAMES['theme'] ];
$preview_link   = $args[ Manager::STATE_INPUT_NAMES['preview_link'] ];
$plugins        = $args[ Manager::STATE_INPUT_NAMES['plugins'] ];
$screenshot_url = $args['info']['screenshot_url'];

View_Helper::print_hidden_fields(
	array(
		Manager::STATE_INPUT_NAMES['use_case'],
		Manager::STATE_INPUT_NAMES['theme'],
		Manager::STATE_INPUT_NAMES['plugins'],
		Manager::STATE_INPUT_NAMES['install_promoted'],
	)
);
?>
	<img class="summary-img" src="<?php echo( $screenshot_url ); ?>" alt="<?php echo( $theme ); ?>">
	<div class="preview-text">
		<h2><?php _e( 'Theme selected', 'ionos-assistant' ); ?></h2>
		<p><?php echo $args['info']['name']; ?></p>
		<h2><?php _e( 'Plugins to be installed', 'ionos-assistant' ); ?></h2>

		<ul class="plugins-list">
			<?php
			if ( ! empty( $args['plugins'] ) ) {
				foreach ( $args['plugins'] as $key ) {
					?>
					<li class="text"><?php echo Summary::get_plugin_name( $key ); ?></li>
					<?php
				}
			}

			foreach ( $args['required_plugins'] as $key => $info ) {
				if ( ! is_array( Summary::get_plugin_name( $key ) ) ) {
					?>
					<li class="text"><?php echo Summary::get_plugin_name( $key ); ?></li>
					<?php
				}
			}
			?>
		</ul>
		<button class="link-btn" type="submit" name="step" value="plugin-selection"><span class="dashicons dashicons-edit"></span><?php _e( 'Edit plugins selection', 'ionos-assistant' ); ?></button>
    </div>

    <div class="buttons">
        <button class="btn primary-btn" type="submit"><?php _e( 'Install', 'ionos-assistant' ); ?></button>
        <a class="link-btn" href="<?php echo add_query_arg( array( 'page=ionos-assistant' => '' ), get_admin_url() . 'admin.php' ); ?>"><?php _e( 'Reset', 'ionos-assistant' ); ?></a>
    </div>
<?php
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
