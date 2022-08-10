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
	$restricted_product_ids = rwstripe_get_restricted_products_for_post( $post->ID );

	// Make sure that we have at least one product restriction.
	if ( empty( $restricted_product_ids ) ) {
		// The page/post is not restricted, so we can return the content.
		return $content;
	}

	// Check if the current user has access to this restricted page/post.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $restricted_product_ids ) ) {
		// User doesn't have access.
		// Check if we should still show an excerpt.
		if ( get_option( 'rwstripe_show_excerpts', true ) ) {
			// Show excerpt.
			global $post;
			if( $post->post_excerpt ) {
				// Defined exerpt.
				$content = wpautop( $post->post_excerpt );
			} elseif(strpos($content, "<span id=\"more-" . $post->ID . "\"></span>") !== false) {
				// More tag.
				$pos = strpos($content, "<span id=\"more-" . $post->ID . "\"></span>");
				$content = substr($content, 0, $pos);
			} elseif(strpos($content, 'class="more-link">') !== false) {
				// More link.
				$content = preg_replace("/\<a.*class\=\"more\-link\".*\>.*\<\/a\>/", "", $content);
			} elseif(strpos($content, "<!-- wp:more -->") !== false) {
				// More block.
				$pos = strpos($content, "<!-- wp:more -->");
				$content = substr($content, 0, $pos);
			} elseif(strpos($content, "<!--more-->") !== false) {
				// More tag.
				$pos = strpos($content, "<!--more-->");
				$content = substr($content, 0, $pos);
			} else {
				//auto generated excerpt. pulled from wp_trim_excerpt
				$content = strip_shortcodes( $content );
				$content = str_replace(']]>', ']]&gt;', $content);
				$content = wp_strip_all_tags( $content );
				$excerpt_length = apply_filters('excerpt_length', 55);
				$words = preg_split("/[\n\r\t ]+/", $content, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
				if ( count($words) > $excerpt_length ) {
					array_pop($words);
					$content = implode(' ', $words);
					$content = $content . "... ";
				} else {
					$content = implode(' ', $words) . "... ";
				}

				$content = wpautop($content);
			}
		} else {
			// Not showing an excerpt. Wipe the content.
			$content = '';
		}
		ob_start();
		rwstripe_restricted_content_message( $restricted_product_ids );
		$content .= ob_get_clean();
	}
	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content', 5 );
add_filter( 'the_content_rss', 'rwstripe_the_content', 5 );
add_filter( 'comment_text_rss', 'rwstripe_the_content', 5 );

/**
 * Do not restrict the excerpt.
 *
 * @since 1.0
 *
 * @param string $content The content to potentially replace.
 * @return string $content The new content to show.
 */
function rwstripe_get_the_excerpt_start( $content ) {	
	remove_filter('the_content', 'rwstripe_the_content', 5);		
	return $content;
}
add_filter('get_the_excerpt', 'rwstripe_get_the_excerpt_start', 1);

/**
 * Continue restricting after the excerpt is generated.
 *
 * @since 1.0
 *
 * @param string $content The content to potentially replace.
 * @return string $content The new content to show.
 */
function rwstripe_get_the_excerpt_end($content, $skipcheck = false) {	
	add_filter('the_content', 'rwstripe_the_content', 5);		
	return $content;
}
add_filter('get_the_excerpt', 'rwstripe_get_the_excerpt_end', 100);

/**
 * Restrict comments if the user does not have access to the page/post.
 *
 * @since 1.0
 *
 * @param array|bool $comments The comments to potentially filter or boolean if filtering comments_open
 * @param array $post_id|null The post ID to check for access or null if filtering comments_open
 * @return array|bool $comments The filtered comments or boolean if filtering comments_open
 */
function rwstripe_comments_filter($comments, $post_id = NULL) {
	global $current_user;

	if ( empty( $comments ) ) {
		return $comments;	// If comments are empty or closed, then we don't need to check.
	}

	// Check if this post is restricted.
	$restricted_product_ids = rwstripe_get_restricted_products_for_post( $post_id );
	if ( empty( $restricted_product_ids ) ) {
		return $comments;	// The post is not restricted, so we don't need to restrict comments.
	}

	// Check if the current user has access to this restricted post.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $restricted_product_ids ) ) {
		// The user does not have access to this restricted post, so we need to restrict comments.
		if ( is_array( $comments ) ) {
			return array(); // In comments_array filter.
		} else {
			return false; // In comments_open filter.
		}
	}

	// The user has access to this restricted post, so we don't need to restrict comments.
	return $comments;
}
add_filter("comments_array", "rwstripe_comments_filter", 10, 2);
add_filter("comments_open", "rwstripe_comments_filter", 10, 2);

/**
 * Add purchase option to restricted categories and tags.
 *
 * @since 1.0
 */
function rwstripe_add_term_purchase_option() {
	global $current_user;

	// Make sure we only add the purchase option once.
	static $tried_to_add_purchase_option = false;
	if ( $tried_to_add_purchase_option ) {
		return;
	}

	// Make sure we are on the archive page for a category or a tag.
	if ( ! is_category() && ! is_tag() ) {
		return;
	}

	// We are going to try to add the purchase option. We only want to do this once.
	$tried_to_add_purchase_option = true;

	// Get the term ID.
	$term_id = get_queried_object_id();

	// Get restricted products for this term.
	$restricted_products = get_term_meta( $term_id, 'rwstripe_stripe_product_ids', true );
	if ( ! is_array( $restricted_products ) || empty( $restricted_products ) ) {
		// No restrictions. No need to add the purchase option.
		return;
	}

	// Check if the current user has any of the restricted products.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $restricted_products ) ) {
		// The user does not have access to any of the restricted products. Show the restriction message.
		?><div class="rwstripe-term-restriction-message"><?php
		if ( is_category() ) {
			?><h3><?php esc_html_e( 'This category is restricted.', 'restrict-content-pro' ); ?></h3><?php
		} else {
			?><h3><?php esc_html_e( 'This tag is restricted.', 'restrict-content-pro' ); ?></h3><?php
		}
		rwstripe_restricted_content_message( $restricted_products );
		?></div><?php
	}
}
add_filter( 'the_post', 'rwstripe_add_term_purchase_option', 10 );


/**
 * Get the restricted products for a given post ID.
 *
 * @since 1.0
 *
 * @param int $post_id The post ID to check for access.
 * @return array $restricted_product_ids The list of restricted product IDs.
 */
function rwstripe_get_restricted_products_for_post( $post_id ) {
	// Build list of products restricing this content.
	$restricted_product_ids = array();

	// Consider post restrictions.
	$post_restrictions = get_post_meta( $post_id, 'rwstripe_stripe_product_ids', true );
	if ( is_array( $post_restrictions ) ) {
		$restricted_product_ids = array_merge( $restricted_product_ids, $post_restrictions );
	}

	// Consider category restrictions.
	$post_categories = wp_get_post_categories( $post_id );
	if ( is_array( $post_categories ) ) {
		foreach ( $post_categories as $category_id ) {
			$category_restrictions = get_term_meta( $category_id, 'rwstripe_stripe_product_ids', true );
			if ( is_array( $category_restrictions ) ) {
				$restricted_product_ids = array_merge( $restricted_product_ids, $category_restrictions );
			}
		}
	}

	// Consider tag restrictions.
	$post_tags = wp_get_post_tags( $post_id );
	if ( is_array( $post_tags ) ) {
		foreach ( $post_tags as $tag ) {
			$tag_restrictions = get_term_meta( $tag->term_id, 'rwstripe_stripe_product_ids', true );
			if ( is_array( $tag_restrictions ) ) {
				$restricted_product_ids = array_merge( $restricted_product_ids, $tag_restrictions );
			}
		}
	}

	// Clean up product IDs and remove duplicates.
	$restricted_product_ids = array_map( 'trim', $restricted_product_ids );
	$restricted_product_ids = array_filter( $restricted_product_ids );
	$restricted_product_ids = array_unique( $restricted_product_ids );

	return $restricted_product_ids;
}
