<?php

/**
 * Swap out page/post content with a prompt to purchase access
 * if the page/post is restricted and the user does not already have access.
 *
 * @since 1.0
 *
 * @param string $content The content to potentially replace.
 *
 * @return string $content The new content to show.
 */
function rwstripe_the_content( $content ) {
	global $post, $current_user;

	// Get the product that restricts this page/post if there is one.
	$stripe_product_ids = get_post_meta( $post->ID, 'rwstripe_stripe_product_ids', true );
	if ( empty( $stripe_product_ids ) || ! is_array( $stripe_product_ids ) || is_admin() ) {
		// The page/post is not restricted or we are in the admin, so we can return the content.
		return $content;
	}

	// Check if the current user has access to this restricted page/post.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $stripe_product_ids ) ) {
		ob_start();
		rwstripe_restricted_content_message( $stripe_product_ids );
		$content = ob_get_clean();
	}
	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content' );

/**
 * If user is not logged in, hide menu items attempting to send users to customer portal.
 *
 * @since 1.0
 *
 * @param array $items The menu items to potentially hide.
 * @return array $items The menu items to show.
 */
function rwstripe_hide_customer_portal_menu_items( $items ) {
	if ( ! is_user_logged_in() ) {
		foreach ( $items as $key => $item ) {
			if ( in_array( 'rwstripe-customer-portal-button', $item->classes ) ) {
				unset( $items[$key] );
			}
		}
	}
	return $items;
}
add_action( 'wp_nav_menu_objects', 'rwstripe_hide_customer_portal_menu_items' );