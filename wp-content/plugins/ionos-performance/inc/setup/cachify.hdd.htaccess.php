<?php
/**
 * Setup for HDD on Apache server.
 *
 * @package Ionos-Performance
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$beginning = '# BEGIN IONOS_PERFORMANCE
&lt;IfModule mod_rewrite.c&gt;
  # ENGINE ON
  RewriteEngine on

  # set hostname directory
  RewriteCond %{HTTPS} on
  RewriteRule .* - [E=IONOS_PERFORMANCE_HOST:https-%{HTTP_HOST}]
  RewriteCond %{HTTPS} off
  RewriteRule .* - [E=IONOS_PERFORMANCE_HOST:%{HTTP_HOST}]

  # set subdirectory
  RewriteCond %{REQUEST_URI} /$
  RewriteRule .* - [E=IONOS_PERFORMANCE_DIR:%{REQUEST_URI}]
  RewriteCond %{REQUEST_URI} ^$
  RewriteRule .* - [E=IONOS_PERFORMANCE_DIR:/]

  # gzip
  RewriteRule .* - [E=IONOS_PERFORMANCE_SUFFIX:]
  &lt;IfModule mod_mime.c&gt;
    RewriteCond %{HTTP:Accept-Encoding} gzip
    RewriteRule .* - [E=IONOS_PERFORMANCE_SUFFIX:.gz]
    AddType text/html .gz
    AddEncoding gzip .gz
  &lt;/IfModule&gt;

  # Main Rules
  RewriteCond %{REQUEST_METHOD} !=POST
  RewriteCond %{QUERY_STRING} =""
  RewriteCond %{REQUEST_URI} !^/(wp-admin|wp-content/cache)/.*
  RewriteCond %{HTTP_COOKIE} !(wp-postpass|wordpress_logged_in|comment_author)_
  RewriteCond ';

$middle = '/cache/ionos-performance/%{ENV:IONOS_PERFORMANCE_HOST}%{ENV:IONOS_PERFORMANCE_DIR}index.html -f
  RewriteRule ^(.*) ';

$ending = '/cache/ionos-performance/%{ENV:IONOS_PERFORMANCE_HOST}%{ENV:IONOS_PERFORMANCE_DIR}index.html%{ENV:IONOS_PERFORMANCE_SUFFIX} [L]
&lt;/IfModule&gt;
# END IONOS_PERFORMANCE';
?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( '.htaccess HDD setup', 'ionos-performance' ); ?>
			</th>
			<td>
				<?php esc_html_e( 'Please add the following lines to your .htaccess file', 'ionos-performance' ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php esc_html_e( 'Notes', 'ionos-performance' ); ?>
			</th>
			<td>
				<ul style="list-style-type:circle">
					<li>
						<?php esc_html_e( 'Within .htaccess, the extension has a higher priority and must be placed above the WordPress Rewrite rules (marked mostly by # BEGIN WordPress … # END WordPress).', 'ionos-performance' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Changes to the .htaccess file can not be made if PHP is run as fcgi.', 'ionos-performance' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'If there are partial errors in the redirects within the blog, the shutdown of the Apache Content Cache can help:', 'ionos-performance' ); ?><br />
						<pre>&lt;IfModule mod_cache.c&gt;
  CacheDisable /
&lt;/IfModule&gt;</pre>
					</li>
					<li>
						<?php esc_html_e( 'In case of special character errors, you can add the following to the .htaccess file:', 'ionos-performance' ); ?><br />
						<pre>AddDefaultCharset UTF-8</pre>
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px">
		<pre style="white-space: pre-wrap">
			<?php
			printf(
				'%s%s%s%s%s',
				esc_html( $beginning ),
				esc_html( WP_CONTENT_DIR ),
				esc_html( $middle ),
				esc_html( wp_make_link_relative( content_url() ) ),
				esc_html( $ending )
			);
			?>
		</pre>
	</div>
