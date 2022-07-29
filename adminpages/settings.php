<?php

/*
 * Add settings page for Restrict With Stripe.
 *
 * @since TBD
 */
function rwstripe_admin_menu() {
	add_options_page( esc_html__( 'Restrict With Stripe', 'restrict-with-stripe' ),  esc_html__( 'Restrict With Stripe', 'restrict-with-stripe' ), 'manage_options', 'rwstripe', 'rwstripe_render_settings_page');	
}
add_action('admin_menu', 'rwstripe_admin_menu');

/**
 * Set up Restrict With Stripe settings.
 *
 * @since TBD
 */
function rwstripe_settings_init() {
	// Register settings.
	register_setting( 'rwstripe_restricted_content_message', 'rwstripe_restricted_content_message', 'rwstripe_restricted_content_message_validate' );

	// Add settings sections.
	add_settings_section( 'rwstripe_connection_settings', esc_html__( 'Connection Settings', 'restrict-with-stripe' ), 'rwstripe_connection_settings_callback', 'rwstripe' );
	add_settings_section( 'rwstripe_restricted_content_message', esc_html__( 'Restricted Content Message', 'restrict-with-stripe' ), 'rwstripe_restricted_content_message_settings_callback', 'rwstripe' );

	// Add settings fields.
	add_settings_field( 'rwstripe_restricted_content_message_logged_out_message', esc_html__( 'Logged Out Message', 'restrict-with-stripe' ), 'rwstripe_restricted_content_message_logged_out_message_callback', 'rwstripe', 'rwstripe_restricted_content_message' );
	add_settings_field( 'rwstripe_restricted_content_message_logged_out_button_text', esc_html__( 'Logged Out Button Text', 'restrict-with-stripe' ), 'rwstripe_restricted_content_message_logged_out_button_text_callback', 'rwstripe', 'rwstripe_restricted_content_message' );
	add_settings_field( 'rwstripe_restricted_content_message_logged_in_message', esc_html__( 'Logged In Message', 'restrict-with-stripe' ), 'rwstripe_restricted_content_message_logged_in_message_callback', 'rwstripe', 'rwstripe_restricted_content_message' );
	add_settings_field( 'rwstripe_restricted_content_message_logged_in_button_text', esc_html__( 'Logged In Button Text', 'restrict-with-stripe' ), 'rwstripe_restricted_content_message_logged_in_button_text_callback', 'rwstripe', 'rwstripe_restricted_content_message' );
	add_settings_field( 'rwstripe_restricted_content_message_not_purchasable_message', esc_html__( 'Not Purchasable Message', 'restrict-with-stripe' ), 'rwstripe_restricted_content_message_not_purchasable_message_callback', 'rwstripe', 'rwstripe_restricted_content_message' );
}
add_action( 'admin_init', 'rwstripe_settings_init' );

/**
 * Populate the settings page.
 *
 * @since TBD
 */
function rwstripe_render_settings_page() {
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php  esc_html_e( 'Restrict With Stripe', 'restrict-with-stripe' ); ?></h2>
		<form action="options.php" method="post">
			<?php settings_fields('rwstripe_restricted_content_message'); ?>
			<?php do_settings_sections('rwstripe'); ?>

			<p><br/></p>

			<div class="bottom-buttons">
				<input type="hidden" name="pmpro_mailpoet_options[set]" value="1"/>
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e(__('Save Settings', 'pmpro-mailpoet')); ?>">
			</div>

		</form>
	</div>
	<?php
}

/**
 * Render the connection settings section.
 *
 * @since TBD
 */
function rwstripe_connection_settings_callback() {
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	$stripe_user_id = get_option( 'rwstripe_stripe_user_id', '' );
	$connect_url_base = apply_filters( 'rwstipe_stripe_connect_url', 'https://connect.paidmembershipspro.com' );
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
		<a href="<?php echo esc_url_raw( $connect_url ); ?>" class="rwstripe-stripe-connect"><span><?php esc_html_e( 'Disconnect From Stripe', 'restrict-with-stripe' ); ?></span></a>
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
		<a href="<?php echo esc_url_raw( $connect_url ); ?>" class="rwstripe-stripe-connect"><span><?php esc_html_e( 'Connect with Stripe', 'restrict-with-stripe' ); ?></span></a>
		<?php
	}
}

/**
 * Render the restricted content message settings section.
 *
 * @since TBD
 */
function rwstripe_restricted_content_message_settings_callback() {
	?>
	<p><?php esc_html_e( 'These settings control the message displayed to non-members when they try to access content that is restricted.', 'restrict-with-stripe' ); ?></p>
	<?php
}

/**
 * Render the "logged out message" setting.
 *
 * @since TBD
 */
function rwstripe_restricted_content_message_logged_out_message_callback() {
	$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
	?>
	<textarea name="rwstripe_restricted_content_message[logged_out_message]" rows="5" cols="50"><?php echo strip_tags( $restricted_content_message_options['logged_out_message'], '<a>' ); ?></textarea>
	<?php
}

/**
 * Render the "logged out button text" setting.
 *
 * @since TBD
 */
function rwstripe_restricted_content_message_logged_out_button_text_callback() {
	$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
	?>
	<input type="text" name="rwstripe_restricted_content_message[logged_out_button_text]" value="<?php echo esc_attr( $restricted_content_message_options['logged_out_button_text'] ); ?>" />
	<?php
}

/**
 * Render the "logged in message" setting.
 *
 * @since TBD
 */
function rwstripe_restricted_content_message_logged_in_message_callback() {
	$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
	?>
	<textarea name="rwstripe_restricted_content_message[logged_in_message]" rows="5" cols="50"><?php echo strip_tags( $restricted_content_message_options['logged_in_message'], '<a>' ); ?></textarea>
	<?php
}

/**
 * Render the "logged in button text" setting.
 *
 * @since TBD
 */
function rwstripe_restricted_content_message_logged_in_button_text_callback() {
	$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
	?>
	<input type="text" name="rwstripe_restricted_content_message[logged_in_button_text]" value="<?php echo esc_attr( $restricted_content_message_options['logged_in_button_text'] ); ?>" />
	<?php
}

/**
 * Render the "not purchasable message" setting.
 *
 * @since TBD
 */
function rwstripe_restricted_content_message_not_purchasable_message_callback() {
	$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
	?>
	<textarea name="rwstripe_restricted_content_message[not_purchasable_message]" rows="5" cols="50"><?php echo strip_tags( $restricted_content_message_options['not_purchasable_message'], '<a>' ); ?></textarea>
	<?php
}

/**
 * Validate the restricted content message settings.
 *
 * @since TBD
 *
 * @param array $input The input to validate.
 * @return array The validated input.
 */
function rwstripe_restricted_content_message_validate( $input ) {
	$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
	$input['logged_out_message'] = wp_kses_post( $input['logged_out_message'] );
	$input['logged_out_button_text'] = sanitize_text_field( $input['logged_out_button_text'] );
	$input['logged_in_message'] = wp_kses_post( $input['logged_in_message'] );
	$input['logged_in_button_text'] = sanitize_text_field( $input['logged_in_button_text'] );
	$input['not_purchasable_message'] = wp_kses_post( $input['not_purchasable_message'] );
	return $input;
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
			$error = esc_html__( 'Invalid response from the Stripe Connect server.', 'restrict-with-stripe' );
		} else {
			// Update keys.
			update_option( 'rwstripe_stripe_user_id', sanitize_text_field( $_REQUEST['pmpro_stripe_user_id'] ) );
			update_option( 'rwstripe_stripe_access_token', sanitize_text_field( $_REQUEST['pmpro_stripe_access_token'] ) );
			update_option( 'rwstripe_stripe_publishable_key', sanitize_text_field( $_REQUEST['pmpro_stripe_publishable_key'] ) );

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
