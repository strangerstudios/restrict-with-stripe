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
        if ( empty( $new_customer->id ) ) {
            // If we cannot create a new customer, bail.
            return null;
        }
        $customer_id = $new_customer->id;
        update_user_meta( $user_id, 'rwstripe_customer_id', $customer_id );
    }
    return $customer_id;
}