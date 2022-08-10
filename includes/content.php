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

	// Make sure that we are on the frontend and have a post ID.
	if ( is_admin() || empty( $post->ID ) ) {
		return $content;
	}

	// Build list of products restricing this content.
	$restricted_product_ids = array();

	// Consider post restrictions.
	$post_restrictions = get_post_meta( $post->ID, 'rwstripe_stripe_product_ids', true );
	if ( is_array( $post_restrictions ) ) {
		$restricted_product_ids = array_merge( $restricted_product_ids, $post_restrictions );
	}

	// Consider category restrictions.
	$post_categories = wp_get_post_categories( $post->ID );
	if ( is_array( $post_categories ) ) {
		foreach ( $post_categories as $category_id ) {
			$category_restrictions = get_term_meta( $category_id, 'rwstripe_stripe_product_ids', true );
			if ( is_array( $category_restrictions ) ) {
				$restricted_product_ids = array_merge( $restricted_product_ids, $category_restrictions );
			}
		}
	}

	// Consider tag restrictions.
	$post_tags = wp_get_post_tags( $post->ID );
	if ( is_array( $post_tags ) ) {
		foreach ( $post_tags as $tag ) {
			$tag_restrictions = get_term_meta( $tag->term_id, 'rwstripe_stripe_product_ids', true );
			if ( is_array( $tag_restrictions ) ) {
				$restricted_product_ids = array_merge( $restricted_product_ids, $tag_restrictions );
			}
		}
	}

	// Make sure that we have at least one product restriction.
	if ( empty( $restricted_product_ids ) ) {
		// The page/post is not restricted, so we can return the content.
		return $content;
	}


	// Clean up product IDs and remove duplicates.
	$restricted_product_ids = array_map( 'trim', $restricted_product_ids );
	$restricted_product_ids = array_filter( $restricted_product_ids );
	$restricted_product_ids = array_unique( $restricted_product_ids );

	// Check if the current user has access to this restricted page/post.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $restricted_product_ids ) ) {
		ob_start();
		rwstripe_restricted_content_message( $restricted_product_ids );
		$content = ob_get_clean();
	}
	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content' );
