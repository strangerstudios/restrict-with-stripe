<?php

/**
 * Add a settings link to the plugin actions.
 *
 * @since 1.0
 *
 * @param array $links Array of links for the plugin action links.
 * @return array $links Array of links for the plugin action links.
 */
function rwstripe_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=rwstripe' ) . '">' . esc_html__( 'Settings', 'restrict-with-stripe' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . RWSTRIPE_BASENAME, 'rwstripe_plugin_action_links' );

/**
 * Runs only when the plugin is activated.
 *
 * @since 1.0
 */
function rwstripe_admin_notice_activation_hook() {
	// Create transient data.
	set_transient( 'rwstripe-admin-notice', true, 5 );
}
register_activation_hook( RWSTRIPE_BASENAME, 'rwstripe_admin_notice_activation_hook' );

/**
 * Add a notice on activation.
 *
 * @since 1.0
 */
function rwstripe_admin_notice() {
    // Check transient, if available display
    // the notice and clear the transient
    if ( get_transient( 'rwstripe-admin-notice' ) ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Thank you for activating Restrict with Stripe!', 'restrict-with-stripe' ); ?></p>
            <p><a href="<?php echo admin_url( 'options-general.php?page=rwstripe' ); ?>"><?php esc_html_e( 'Click here to begin restricting content.', 'restrict-with-stripe' ); ?></a></p>
        </div>
        <?php
        delete_transient( 'rwstripe-admin-notice' );
    }
}
add_action( 'admin_notices', 'rwstripe_admin_notice' );

