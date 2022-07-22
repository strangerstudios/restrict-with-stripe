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
		return esc_html__( 'Your WordPress account is not linked to a Stripe customer.', 'restrict-with-stripe' );
	}
	$rwstripe_stripe = new RWStripe_Stripe();
	$customer_portal = $rwstripe_stripe->get_customer_portal_url( $customer_id );
	return esc_html__( 'Manage Purchases', 'restrict-with-stripe' ) . ': <a href="' . esc_url( $customer_portal ) . '">' . esc_url( $customer_portal ) . '</a>';
}
add_shortcode( 'rwstripe_customer_portal', 'rwstripe_customer_portal_shortcode' );