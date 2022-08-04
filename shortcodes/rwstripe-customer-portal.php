<?php

/**
 * Set up shortcode to display link to Stripe Customer Portal.
 *
 * @since 1.0
 *
 * @return string HTML for link to Stripe Customer Portal.
 */
function rwstripe_customer_portal_shortcode() {
	$content_pre = '<div class="rwstripe_customer_portal_shortcode">';
	$content_post = '</div>';

	if ( is_user_logged_in() ) {
		$content = '<button type="button" class="rwstripe-customer-portal-button">' . esc_html__( 'Manage Purchases', 'restrict-with-stripe' ) . '</button>';
	} else {
		$content = '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Please log in to manage your purchases.', 'restrict-with-stripe' ) . '</a>';
	}

	return $content_pre . $content . $content_post;
}
add_shortcode( 'rwstripe_customer_portal', 'rwstripe_customer_portal_shortcode' );