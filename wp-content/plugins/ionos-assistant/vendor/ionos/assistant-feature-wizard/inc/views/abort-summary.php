<?php
/**
 * @global $args
 */

use Assistant\Wizard\Controllers\Summary;
use Assistant\Wizard\Manager;
use Assistant\Wizard\View_Helper;

load_template( ASSISTANT_WIZARD_VIEWS_DIR . '/parts/header.php', true, $args );

$plugins = $args[ Manager::STATE_INPUT_NAMES['plugins'] ];

View_Helper::print_hidden_fields(
    array(
        Manager::STATE_INPUT_NAMES['plugins'],
    )
);
?>
    <img class="abort-summary-img" src="https://s.w.org/style/images/about/WordPress-logotype-simplified.png" alt="Wordpress Logo">
    <div class="preview-text">
        <h4>WordPress Vanilla</h4>
        <h4><?php _e('Plugins to be installed', 'ionos-assistant'); ?></h4>

        <ul class="plugins-list">
            <?php
            if (! empty($args['plugins']) ) {
                foreach ( $args['plugins'] as $key ) {
                    ?>
                    <li class="text"><?php echo Summary::get_plugin_name($key); ?></li>
                    <?php
                }
            }
            ?>
        </ul>
        <button class="link-btn" type="submit" name="step" value="abort-plugin-selection"><span class="dashicons dashicons-edit"></span><?php _e('Edit plugins selection', 'ionos-assistant'); ?></button>
    </div>
    <div class="buttons">
        <button class="btn primary-btn" type="submit"><?php _e('Install', 'ionos-assistant'); ?></button>
        <a class="link-btn" href="<?php echo add_query_arg(array( 'page=ionos-assistant' => '' ), get_admin_url() . 'admin.php'); ?>"><?php _e('Reset', 'ionos-assistant'); ?></a>
    </div>
<?php
load_template(ASSISTANT_WIZARD_VIEWS_DIR . '/parts/footer.php', true, $args);
