<?php

/**
 * Register the rwstripe_stripe_product_ids post meta
 * so that it can be updated in the block editor.
 *
 * @since 1.0
 */
function rwstripe_register_post_meta() {
	register_meta( 
		'post', 
		'rwstripe_stripe_product_ids', 
		array(
 			'type'		=> 'array',
 			'single'	=> true,
 			'show_in_rest'	=> array(
				'schema' => array(
					'type' => 'array',
					'items' => array(
						'type' => 'string',
					),
				),
			),
 		)
	);
}
add_action( 'init', 'rwstripe_register_post_meta' );

/**
 * Enqueue the sidebar panel for restricting posts int he block editor.
 *
 * @since 1.0
 */
function rwstripe_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'rwstripe-sidebar',
		plugins_url( 'blocks/build/sidebar/index.js', RWSTRIPE_BASE_FILE ),
		array( 'wp-edit-post', 'wp-element', 'wp-components', 'wp-plugins', 'wp-data' )
	);
}
add_action( 'enqueue_block_editor_assets', 'rwstripe_enqueue_block_editor_assets' );