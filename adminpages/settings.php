<?php

/*
 * Add settings page for Restrict With Stripe.
 *
 * @since TBD
 */
function rwstripe_admin_menu() {
	add_options_page('Restrict With Stripe', 'Restrict With Stripe', 'manage_options', 'rwstripe', 'rwstripe_settings_page');	
}
add_action('admin_menu', 'rwstripe_admin_menu');

/**
 * Populate the settings page.
 *
 * @since TBD
 */
function rwstripe_settings_page() {
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	$products    = $RWStripe_Stripe->get_all_products();
	$stripe_user_id = get_option( 'rwstripe_stripe_user_id', '' );
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2>Restrict With Stripe</h2>

		<?php
		// Determine if the gateway is connected in live mode and set var.
		$connect_url_base = apply_filters( 'pmpro_stripe_connect_url', 'https://connect.paidmembershipspro.com' );
		if ( ! empty( $stripe_user_id ) ) {
			$connect_url = add_query_arg(
				array(
					'action' => 'disconnect',
					'gateway_environment' => 'sandbox',
					'stripe_user_id' => $stripe_user_id,
					'return_url' => rawurlencode( admin_url( 'options-general.php?page=rwstripe' ) ),
				),
				$connect_url_base
			);
			?>
			<a href="<?php echo esc_url_raw( $connect_url ); ?>" class="pmpro-stripe-connect"><span><?php esc_html_e( 'Disconnect From Stripe', 'paid-memberships-pro' ); ?></span></a>
			<?php
		} else {
			$connect_url = add_query_arg(
				array(
					'action' => 'authorize',
					'gateway_environment' => 'sandbox',
					'return_url' => rawurlencode( admin_url( 'options-general.php?page=rwstripe' ) ),
				),
				$connect_url_base
			);
			?>
			<a href="<?php echo esc_url_raw( $connect_url ); ?>" class="pmpro-stripe-connect"><span><?php esc_html_e( 'Connect with Stripe', 'paid-memberships-pro' ); ?></span></a>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Handle responses from the Stripe Connect server.
 *
 * @since TBD
 */
function rwstripe_handle_connect_to_stripe_response() {
	// Is user have permission to edit give setting.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_REQUEST['pmpro_stripe_connected'] )  ) {
		if ( 'false' === $_REQUEST['pmpro_stripe_connected'] && isset( $_REQUEST['error_message'] ) ) {
			$error = $_REQUEST['error_message'];
		} elseif (
			'false' === $_REQUEST['pmpro_stripe_connected']
			|| ! isset( $_REQUEST['pmpro_stripe_publishable_key'] )
			|| ! isset( $_REQUEST['pmpro_stripe_user_id'] )
			|| ! isset( $_REQUEST['pmpro_stripe_access_token'] )
		) {
			$error = __( 'Invalid response from the Stripe Connect server.', 'paid-memberships-pro' );
		} else {
			// Update keys.
			update_option( 'rwstripe_stripe_user_id', $_REQUEST['pmpro_stripe_user_id'] );
			update_option( 'rwstripe_stripe_access_token', $_REQUEST['pmpro_stripe_access_token'] );
			update_option( 'rwstripe_stripe_publishable_key', $_REQUEST['pmpro_stripe_publishable_key'] );

			wp_redirect( admin_url( 'options-general.php?page=rwstripe' ) );
			exit;
		}
	} elseif ( isset( $_REQUEST['pmpro_stripe_disconnected'] ) ) {
		delete_option( 'rwstripe_stripe_user_id' );
		delete_option( 'rwstripe_stripe_access_token' );
		delete_option( 'rwstripe_stripe_publishable_key' );
	}

	// TODO: Show error messages from failed connection.
}
add_action( 'admin_init', 'rwstripe_handle_connect_to_stripe_response' );
