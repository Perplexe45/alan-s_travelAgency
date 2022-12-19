<?php
/**
 * @global $args
 */

use Assistant\Wizard\Controllers\Install;

$total            = $args['install_data']['total'];
$pending_installs = count( $args['install_data']['plugins'] );

load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );

if ( $args['is_pre_install_view'] ) { ?>
	<p><?php _e( 'We’re starting to install your selected components in a moment.', 'ionos-assistant' ); ?></p>
<?php } else { ?>
	<div class="loading"></div>
	<p><?php Install::get_text( $pending_installs ); ?></p>
<?php }
load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args );
?>
