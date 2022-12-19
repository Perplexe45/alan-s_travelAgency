<?php

namespace Assistant\Descriptify;

use Ionos\Assistant\Config;
use Ionos\Assistant\Options;

class Manager {

	public static function init() {
		Options::set_tenant_and_plugin_name( 'ionos', 'assistant' );

		if ( ! Config::get( 'features.descriptify.enabled' ) ) {
            return;
        }

		add_action( 'admin_print_footer_scripts', function() {
			global $pagenow;

			$cp_application_link = Config::get( 'features.descriptify.links.control_panel_applications_' . Options::get_market() );
			if ( is_admin() && $pagenow == 'options-general.php' && $cp_application_link ) {

				$websiteUrlDescription = sprintf( /* translators: s=link to control panel */
					__( 'You can customize and manage your URL (domain) easily at <a href="%1$s" target="_blank">%2$s App Center</a>.', 'ionos-assistant' ),
					$cp_application_link,
					Config::get( 'branding.name' )
				);
				?>
				<style>
                    #home-description {
                        display: none;
                    }
				</style>
				<script>
                    ( function () {
                        var descriptionNode = document.createRange().createContextualFragment( '<p class="description"><?php echo addslashes( $websiteUrlDescription ); ?></p>' );
                        if ( document.getElementById( 'siteurl' ) ) {
                            document.getElementById( 'siteurl' ).parentNode.appendChild( descriptionNode.cloneNode( true ) );
                        }
                        if ( document.getElementById( 'home' ) ) {
                            document.getElementById( 'home' ).parentNode.appendChild( descriptionNode.cloneNode( true ) );
                        }
                    } )();
				</script>
				<?php
			}
		} );
	}
}
