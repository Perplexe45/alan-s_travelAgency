<?php
/**
 * Settings page.
 *
 * @package Ionos-Performance
 */

/* Quit */

use Ionos\Performance\Helper;

defined( 'ABSPATH' ) || exit;
?>

<form method="post" action="options.php">
	<?php settings_fields( 'ionos-performance' ); ?>
	<table class="form-table">

		<tr>
			<th scope="row">
				<label for="ionos_performance_cache_expires"><?php esc_html_e( 'Cache', 'ionos-performance' ); ?></label>
			</th>
			<td>
				<fieldset>
					<label for="ionos_performancecaching_enabled">
						<input type="checkbox" name="ionos-performance[caching_enabled]" id="ionos_performancecaching_enabled" value="1" 
						<?php
						checked( '1', $options['caching_enabled'] );
						echo Helper::has_conflicting_caching_plugins() ? ' disabled ' : '';
						?>
						 />
						<?php esc_html_e( 'Enable caching feature', 'ionos-performance' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="ionos_performancecache_expires"><?php esc_html_e( 'Cache expiration', 'ionos-performance' ); ?></label>
			</th>
			<td>
				<input type="number" min="0" step="1" name="ionos-performance[cache_expires]" id="ionos_performancecache_expires" value="<?php echo esc_attr( $options['cache_expires'] ); ?>" class="small-text"/>
				<?php esc_html_e( 'Hours', 'ionos-performance' ); ?>

				<p class="description">
					<?php
					printf(
					/* translators: Placeholder is the trash icon itself as dashicon */
						esc_html__( 'Flush the cache by clicking the button below or the %1$s icon in the admin bar.', 'ionos-performance' ),
						'<span class="dashicons dashicons-trash" aria-hidden="true"></span><span class="screen-reader-text">"' . esc_html__( 'Flush the ionos-performance cache', 'ionos-performance' ) . '"</span>'
					);
					?>
				</p>

				<?php
				$flush_cache_url = wp_nonce_url( add_query_arg( '_ionos-performance', 'flush' ), '_ionos_performance_flush_nonce' );
				?>

				<p>
					<a class="button button-secondary" href="<?php echo esc_url( $flush_cache_url ); ?>">
						<?php esc_html_e( 'Flush cache now', 'ionos-performance' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache exceptions', 'ionos-performance' ); ?>
			</th>
			<td>
				<fieldset>
					<label for="ionos_performance_without_ids">
						<input type="text" name="ionos-performance[without_ids]" id="ionos_performance_without_ids" placeholder="<?php esc_attr_e( 'e.g. 1, 2, 3', 'ionos-performance' ); ?>"
							   value="<?php echo esc_attr( $options['without_ids'] ); ?>"/>
						<?php esc_html_e( 'Post/Page IDs', 'ionos-performance' ); ?>
					</label>

					<br/>

					<label for="ionos_performance_without_agents">
						<input type="text" name="ionos-performance[without_agents]" id="ionos_performance_without_agents" placeholder="<?php esc_attr_e( 'e.g. MSIE 6, Opera', 'ionos-performance' ); ?>"
							   value="<?php echo esc_attr( $options['without_agents'] ); ?>"/>
						<?php esc_html_e( 'Browser User Agents', 'ionos-performance' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
