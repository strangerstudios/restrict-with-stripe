<?php

/**
 * Set up shortcode to display link to Stripe Customer Portal.
 *
 * @since TBD
 *
 * @return string HTML for link to Stripe Customer Portal.
 */
function rwstripe_customer_portal_shortcode() {
	if ( ! is_user_logged_in() ) {
		return esc_html__( 'You must be logged in to manage your purchases..', 'restrict-with-stripe' );
	}
	return '<button type="button" class="rwstripe-customer-portal-button">' . esc_html__( 'Manage Purchases', 'restrict-with-stripe' ) . '</button>';
}
add_shortcode( 'rwstripe_customer_portal', 'rwstripe_customer_portal_shortcode' );