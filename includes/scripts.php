<?php

/**
 * Set up JavaScript to handle frontend actions and css.
 *
 * @since TBD
 */
function rwstripe_enqueue_scripts() {
	wp_register_script( 'rwstripe',
		plugins_url( 'js/rwstripe.js', RWSTRIPE_BASE_FILE ),
		array( 'jquery' ),
		RWSTRIPE_VERSION
	);
	$localize_vars = array(
		'restUrl' => rest_url() . 'rwstripe/v1/',
		'nonce' => wp_create_nonce( 'wp_rest' ),
	);
	wp_localize_script( 'rwstripe', 'rwstripe', $localize_vars );
	wp_enqueue_script( 'rwstripe' );

	wp_register_style( 'rwstripe',
		plugins_url( 'css/rwstripe.css', RWSTRIPE_BASE_FILE ),
		array(),
		RWSTRIPE_VERSION
	);
	wp_enqueue_style( 'rwstripe' );
}
add_action( 'wp_enqueue_scripts', 'rwstripe_enqueue_scripts' );

/**
 * Set up JavaScript to handle backend buttons and css.
 *
 * @since TBD
 */
function rwstripe_enqueue_admin_scripts() {
	wp_register_script( 'rwstripe-admin',
		plugins_url( 'js/rwstripe-admin.js', RWSTRIPE_BASE_FILE ),
		array( 'jquery' ),
		RWSTRIPE_VERSION
	);
	$localize_vars = array(
		'restUrl' => rest_url() . 'rwstripe/v1/',
		'nonce' => wp_create_nonce( 'wp_rest' ),
	);
	wp_localize_script( 'rwstripe-admin', 'rwstripe', $localize_vars );
	wp_enqueue_script( 'rwstripe-admin' );

	wp_register_style( 'rwstripe-admin',
		plugins_url( 'css/rwstripe-admin.css', RWSTRIPE_BASE_FILE ),
		array(),
		RWSTRIPE_VERSION
	);
	wp_enqueue_style( 'rwstripe-admin' );
}
add_action( 'admin_enqueue_scripts', 'rwstripe_enqueue_admin_scripts' );

/**
 * Enqueue the sidebar panel for restricting posts in the block editor.
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
