<?php
/**
 * Add new block category for Restrict With Stripe blocks.
 *
 * @since 1.0
 *
 * @param array $categories Array of block categories.
 * @return array Array of block categories.
 */
function rwstripe_block_categories( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'rwstripe',
				'title' => __( 'Restrict With Stripe', 'restrict-with-stripe' ),
			),
		)
	);
}
add_filter( 'block_categories_all', 'rwstripe_block_categories' );

/**
 * Register block types for the block editor.
 */
function rwstripe_register_block_types() {
	register_block_type(
		RWSTRIPE_DIR . '/blocks/build/restricted-content',
		array(
			'render_callback' => 'rwstripe_handle_restricted_content_block',
		)
	);
	register_block_type(
		RWSTRIPE_DIR . '/blocks/build/customer-portal',
		array(
			'render_callback' => 'rwstripe_customer_portal_shortcode',
		)
	);
}
add_action( 'init', 'rwstripe_register_block_types' );

/**
 * Server rendering for restricted content block.
 *
 * @since 1.0
 *
 * @param array $attributes Contains product IDs to restrict by.
 * @param string $content Contains the inner content to be rendered.
 *
 * @return string
 **/
function rwstripe_handle_restricted_content_block( $attributes, $content ) {
	// Make sure this block is actually restricted.
	if ( array_key_exists( 'rwstripe_restricted_products', $attributes ) && is_array( $attributes['rwstripe_restricted_products'] ) && ! empty( $attributes['rwstripe_restricted_products'] ) ) {
		// Check if the current user has access to this restricted page/post.
		$RWStripe_Stripe = RWStripe_Stripe::get_instance();
		if ( ! is_user_logged_in() || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $attributes['rwstripe_restricted_products'] ) ) {
			ob_start();
			rwstripe_restricted_content_message( $attributes['rwstripe_restricted_products'] );
			return ob_get_clean();
		}
	}
	return do_blocks( $content );
}