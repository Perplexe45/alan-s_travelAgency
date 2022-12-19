<?php
$user      = wp_get_current_user();
$banner_1x = forminator_plugin_url() . 'assets/images/new-feature-20.png';
$banner_2x = forminator_plugin_url() . 'assets/images/new-feature-20@2x.png';
?>

<div class="sui-modal sui-modal-md">

	<div
		role="dialog"
		id="forminator-new-feature"
		class="sui-modal-content"
		aria-live="polite"
		aria-modal="true"
		aria-labelledby="forminator-new-feature__title"
	>

		<div class="sui-box forminator-feature-modal" data-prop="forminator_dismiss_feature_1200" data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_dismiss_notification' ) ); ?>">

			<div class="sui-box-header sui-flatten sui-content-center">

				<figure class="sui-box-banner" aria-hidden="true">
					<img
						src="<?php echo esc_url( $banner_1x ); ?>"
						srcset="<?php echo esc_url( $banner_1x ); ?> 1x, <?php echo esc_url( $banner_2x ); ?> 2x"
						alt=""
					/>
				</figure>

				<button class="sui-button-icon sui-button-white sui-button-float--right forminator-dismiss-new-feature" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog.', 'forminator' ); ?></span>
				</button>

				<h3 class="sui-box-title sui-lg" style="overflow: initial; white-space: initial; text-overflow: initial;"><?php esc_html_e( 'New! Forminator Scheduled Reports', 'forminator' ); ?></h3>

				<p class="sui-description">
					<?php esc_html_e( 'Put an end to wondering how well your forms, quizzes, 
					and polls are doing! We\'ve added a new scheduled report feature to Forminator 
					reports, so you can now get daily, weekly, or monthly report notifications sent 
					directly to your inbox.', 'forminator' ); ?>
				</p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<button class="sui-button forminator-dismiss-new-feature" data-modal-close><?php esc_html_e( 'Schedule Report', 'forminator' ); ?></button>

			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
	jQuery( '#forminator-new-feature .forminator-dismiss-new-feature' ).on( 'click', function( e ) {
		e.preventDefault();

		var $notice = jQuery( e.currentTarget ).closest( '.forminator-feature-modal' );
		var ajaxUrl = '<?php echo esc_url( forminator_ajax_url() ); ?>';

		jQuery.post(
			ajaxUrl,
			{
				action: 'forminator_dismiss_notification',
				prop: $notice.data('prop'),
				_ajax_nonce: $notice.data('nonce')
			}
		).always( function() {
			$notice.hide();
            location.href = '<?php echo admin_url( 'admin.php?page=forminator-reports&section=notification' ); ?>';
		});
	});
</script>
