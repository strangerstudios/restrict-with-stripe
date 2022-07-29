<?php

/**
 * Get the customer ID for a user and creates a new customer if one does not exist.
 * 
 * @since TBD
 *
 * @param int $user_id User ID. Defaults to current user.
 *
 * @return string|null Customer ID or null if customer cannot be created.
 */
function rwstripe_get_customer_id_for_user( $user_id = null ) {
    // If no user ID is provided, use the current user.
    if ( empty( $user_id ) ) {
        global $current_user;
        $user_id = $current_user->ID;
    }

    // If we still don't have a user ID, bail.
    if ( empty( $user_id ) ) {
        return null;
    }

    // Get the customer ID for the user.
    $customer_id = get_user_meta( $user_id, 'rwstripe_customer_id', true );

    // If the user does not have a customer ID yet, create a new customer.
    if ( empty( $customer_id ) ) {
        $rwstripe = RWStripe_Stripe::get_instance();
        $user = get_userdata( $user_id );
        $new_customer = $rwstripe->create_customer_with_email( $user->user_email );
        if ( is_string( $new_customer ) ) {
            // If we cannot create a new customer, bail.
            return null;
        }
        $customer_id = $new_customer->id;
        update_user_meta( $user_id, 'rwstripe_customer_id', $customer_id );
    }
    return $customer_id;
}

/**
 * Register the rwstripe_stripe_product_ids post meta
 * so that it can be updated in the block editor.
 *
 * @since 1.0
 */
function rwstripe_register_post_meta() {
	register_meta( 
		'post', 
		'rwstripe_stripe_product_ids', 
		array(
 			'type'		=> 'array',
 			'single'	=> true,
 			'show_in_rest'	=> array(
				'schema' => array(
					'type' => 'array',
					'items' => array(
						'type' => 'string',
					),
				),
			),
 		)
	);
}
add_action( 'init', 'rwstripe_register_post_meta' );

/**
 * Get options for the restricted content message.
 *
 * @since 1.0
 */
function rwstripe_get_restricted_content_message_options() {
    $default_options = array(
        'logged_out_message' => __( 'You must create an account or <a href="!!login_url!!">log in</a> to purchase this content.', 'restrict-with-stripe' ),
		'logged_out_button_text' => __( 'Log In', 'restrict-with-stripe' ),
		'logged_in_message' => __( 'You do not have access to this content.', 'restrict-with-stripe' ),
		'logged_in_button_text' => __( 'Purchase Access', 'restrict-with-stripe' ),
		'not_purchasable_message' => __( 'This product is not purchasable.', 'restrict-with-stripe' ),
    );
    $options = get_option( 'rwstripe_restricted_content_message' );
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    return array_merge( $default_options, $options );
}