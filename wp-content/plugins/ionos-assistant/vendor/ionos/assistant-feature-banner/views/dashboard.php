<?php
/**
 * @global array $args
 */
?>
<div id="assistant-help-panel" class="dashboard-column dashboard-column4 assistant-dashboard-panel">
	<div class="dashboard-row">
		<div class="dashboard-column dashboard-column1 branded-wordpress-column">
			<div class="inside">
				<div class="branded-wordpress-img">
					<img src="<?php echo $args['visual']; ?>" alt="WordPress">
				</div>
				<?php if ( $args['logo_src'] ): ?>
					<img src="<?php echo $args['logo_src']; ?>" alt="<?php echo $args['logo_alt']; ?>" class="logo">
				<?php endif; ?>
			</div>
		</div>
		<div class="dashboard-column dashboard-column1 assistant-wordpress-help">
			<div class="inside">
				<h2><?php _e( 'Your WordPress is now ready to get going.', 'ionos-assistant' ); ?></h2>
				<div class="assistant-links">
					<div class="assistant-links-start">
						<h3><?php _e( 'Next Steps', 'ionos-assistant' ); ?></h3>
						<ul>
							<?php if ( 'page' == get_option( 'show_on_front' ) && ! get_option( 'page_for_posts' ) ) : ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">'.__( 'Edit your front page', 'ionos-assistant' ).'</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">'.__( 'Add additional pages', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
							<?php elseif ( 'page' == get_option( 'show_on_front' ) ) : ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page">'.__( 'Edit your front page', 'ionos-assistant' ).'</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">'.__( 'Add additional pages', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog">'.__( 'Add a blog post', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php' ) ); ?></li>
							<?php else : ?>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog">'.__( 'Write your first blog post', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php' ) ); ?></li>
								<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page">'.__( 'Add an About page', 'ionos-assistant' ).'</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
							<?php endif; ?>
							<li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site">'.__( 'View your site', 'ionos-assistant' ).'</a>', home_url( '/' ) ); ?></li>
							<?php if ( '' !== $args['journey_link'] ) : ?>
								<li><?php printf( '<a href="%s" class="welcome-icon dashicons-before dashicons-controls-play">'.__( 'Take our WordPress Journey', 'ionos-assistant' ).'</a>', $args['journey_link'] ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
					<div class="assistant-links-advanced">
						<h3><?php _e( 'More Actions', 'ionos-assistant' ); ?></h3>
						<ul>
							<?php if ( $args['blog_url'] ): ?>
								<li>
									<a href="<?php echo $args['blog_url']; ?>" target="_blank" class="welcome-icon dashicons-before dashicons-welcome-learn-more"><?php esc_html_e( 'Getting started', 'ionos-assistant' ); ?></a>
								</li>
							<?php endif; ?>
							<li>
								<a href="<?php echo wp_customize_url(); ?>" class="welcome-icon dashicons-before dashicons-admin-appearance"><?php _e( 'Customize the design', 'ionos-assistant' ); ?></a>
							</li>
							<li>
								<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="welcome-icon dashicons-before dashicons-admin-plugins"><?php esc_html_e( 'Manage and add plugins', 'ionos-assistant' ); ?></a>
							</li>
							<?php if ( null !== $args['cp_emails_url'] ) : ?>
								<li><?php printf( '<a href="%s" target="_blank" class="welcome-icon dashicons-before dashicons-email">'.__( 'Create an e-mail address', 'ionos-assistant' ).'</a>', $args['cp_emails_url'] ); ?></li>
							<?php endif; ?>
							<?php if ( ! empty( $args['is_product_domain'] ) && $args['is_product_domain'] === true && ! empty( $args['cp_application_url'] ) ) : ?>
								<li><?php printf( '<a href="%s" target="_blank" class="welcome-icon dashicons-before dashicons-admin-links">'.__( 'Change domain', 'ionos-assistant' ).'</a>', $args['cp_application_url'] ); ?></li>
							<?php elseif ( ! is_ssl() && ! empty( $args['cp_application_url'] ) ) : ?>
								<li><?php printf( '<a href="%s" target="_blank" class="welcome-icon dashicons-before dashicons-lock">'.__( 'Activate SSL', 'ionos-assistant' ).'</a>', $args['cp_application_url'] ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>