<?php
/**
 * Runs only when the plugin is activated.
 *
 * @since 0.1.0
 */
function rwstripe_admin_notice_activation_hook() {
	// Create transient data.
	set_transient( 'rwstripe-admin-notice', true, 5 );
	
	// Trigger a rewrite rule flush in case the default module is on.
	set_transient( 'rwstripe_flush_rewrite_rules', 1 );
}
register_activation_hook( plugin_basename( RWSTRIPE_BASE_FILE ), 'rwstripe_admin_notice_activation_hook' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */
function rwstripe_admin_notice() {
	// Check transient, if available display notice.
	if ( get_transient( 'rwstripe-admin-notice' ) ) { ?>
		<div class="updated notice is-dismissible">
			<p>
			<?php 
				esc_html_e( 'Thank you for activating.', 'restrict-with-stripe' );
				echo ' <a href="' . esc_url( get_admin_url( null, 'admin.php?page=rwstripe' ) ) . '">';
				esc_html_e( 'Click here to manage settings.', 'restrict-with-stripe' );
				echo '</a>';
			?>
			</p>
		</div>
		<?php
		// Delete transient, only display this notice once.
		delete_transient( 'rwstripe-admin-notice' );
	}
}
add_action( 'admin_notices', 'rwstripe_admin_notice' );

/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function rwstripe_plugin_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		$new_links = array(
			'<a href="' . esc_url( get_admin_url( null, 'admin.php?page=rwstripe' ) ) . '">' . esc_html__( 'Settings', 'restrict-with-stripe' ) . '</a>',
		);

		$links = array_merge( $new_links, $links );
	}
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( RWSTRIPE_BASE_FILE ), 'rwstripe_plugin_action_links' );

/**
 * Function to add links to the plugin row meta
 *
 * @param array  $links Array of links to be shown in plugin meta.
 * @param string $file Filename of the plugin meta is being shown for.
 */
function rwstripe_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'restrict-with-stripe.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.strangerstudios.com/wordpress-plugins/restrict-with-stripe/' ) . '" title="' . esc_attr__( 'View Documentation', 'restrict-with-stripe' ) . '">' . esc_html__( 'Docs', 'restrict-with-stripe' ) . '</a>',
			'<a href="' . esc_url( 'https://www.strangerstudios.com/wordpress-plugins/restrict-with-stripe/' ) . '" title="' . esc_attr__( 'Visit Customer Support Forum', 'restrict-with-stripe' ) . '">' . esc_html__( 'Support', 'restrict-with-stripe' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'rwstripe_plugin_row_meta', 10, 2 );