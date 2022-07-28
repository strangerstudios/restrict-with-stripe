<?php

/**
 * Register block types for the block editor.
 */
function rwstripe_register_block_types() {	
	register_block_type(
		RWSTRIPE_DIR . '/blocks/build/customer-portal',
		array(
			'render_callback' => 'rwstripe_customer_portal_shortcode',
		)
	);
}
add_action( 'init', 'rwstripe_register_block_types' );

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