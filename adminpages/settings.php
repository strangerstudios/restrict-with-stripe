<?php

/*
 * Add settings page for Restrict With Stripe.
 *
 * @since 1.0
 */
function rwstripe_admin_menu() {
	add_options_page( esc_html__( 'Restrict With Stripe', 'restrict-with-stripe' ),  esc_html__( 'Restrict With Stripe', 'restrict-with-stripe' ), 'manage_options', 'rwstripe', 'rwstripe_render_settings_page');	
}
add_action('admin_menu', 'rwstripe_admin_menu');

/**
 * Populate the settings page.
 *
 * @since 1.0
 */
function rwstripe_render_settings_page() {
	?>
        <div id="rwstripe-settings"></div>
    <?php
}

/**
 * Register RWS settings with REST API so that they can be edited in JS.
 *
 * @since 1.0
 */
function rwstripe_settings_init() {
	register_setting(
		'rwstripe_restricted_content_message',
		'rwstripe_restricted_content_message',
		array(
			'sanitize_callback' => 'rwstripe_restricted_content_message_validate',
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'object',
					'properties' => array(
						'logged_out_message' => array(
							'type' => 'string',
						),
						'logged_out_collect_password' => array(
							'type' => 'boolean',
						),
						'logged_out_button_text' => array(
							'type' => 'string',
						),
						'logged_in_message' => array(
							'type' => 'string',
						),
						'logged_in_button_text' => array(
							'type' => 'string',
						),
						'not_purchasable_message' => array(
							'type' => 'string',
						),
					),
				),
			),
			'default' => rwstripe_get_restricted_content_message_options(),
		)
	);
}
add_action( 'rest_api_init', 'rwstripe_settings_init' );

/**
 * Validate the restricted content message settings.
 *
 * @since 1.0
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
 * @since 1.0
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
