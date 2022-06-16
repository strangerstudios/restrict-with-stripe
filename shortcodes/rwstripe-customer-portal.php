<?php

/**
 * Set up shortcode to display link to Stripe Customer Portal.
 *
 * @since TBD
 *
 * @return string HTML for link to Stripe Customer Portal.
 */
function rwstripe_customer_portal_shortcode() {
	$customer_id = rwstripe_get_customer_id_for_user();
	if ( empty( $customer_id ) ) {
		return 'Your WordPress account is not linked to a Stripe customer.';
	}
	$rwstripe_stripe = new RWStripe_Stripe();
	$customer_portal = $rwstripe_stripe->get_customer_portal_url( $customer_id );
	return 'Manage Purchases: <a href="' . $customer_portal . '">' . $customer_portal . '</a>';
}
add_shortcode( 'rwstripe_customer_portal', 'rwstripe_customer_portal_shortcode' );