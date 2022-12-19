<?php
load_template( ASSISTANT_JETPACK_BACKUP_FLOW_VIEWS_DIR . '/parts/header.php', true );
?>
<h1 class="screen-reader-text"><?php _e( 'Installing Jetpack Backup', 'ionos-assistant' ); ?></h1>
<img src="<?php echo plugins_url( '/img/jetpack-logo.svg', ASSISTANT_JETPACK_BACKUP_FLOW_FILE ); ?>" alt="" class="jetpack-logo">
<p><?php _e( 'Please wait a moment while we are installing Jetpack Backup for you.', 'ionos-assistant' ); ?></p>
<?php
load_template( ASSISTANT_JETPACK_BACKUP_FLOW_VIEWS_DIR . '/parts/footer.php', true );