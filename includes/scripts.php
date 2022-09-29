<?php

/**
 * Set up JavaScript to handle frontend actions and css.
 *
 * @since 1.0
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
		'processing_message' => __( 'Processing...', 'restrict-with-stripe' ),
	);
	wp_localize_script( 'rwstripe', 'rwstripe', $localize_vars );
	wp_enqueue_script( 'rwstripe' );

	wp_register_style( 'rwstripe',
		plugins_url( 'css/rwstripe.css', RWSTRIPE_BASE_FILE ),
		array(),
		RWSTRIPE_VERSION
	);
	wp_enqueue_style( 'rwstripe' );

	// Load Dashicons for frontend.
	wp_enqueue_style( 'dashicons' );
}
add_action( 'wp_enqueue_scripts', 'rwstripe_enqueue_scripts' );

/**
 * Set up JavaScript to handle backend buttons and css.
 *
 * @since 1.0
 */
function rwstripe_enqueue_admin_scripts() {
	global $rwstripe_connection_error;

	// Enqueue script for settings page.
	wp_enqueue_script(
		'rwstripe-settings',
		plugins_url( 'blocks/build/settings/admin.js', RWSTRIPE_BASE_FILE ),
		array( 'wp-edit-post', 'wp-element', 'wp-components', 'wp-plugins', 'wp-data' )
	);
	wp_set_script_translations( 'rwstripe-settings', 'restrict-with-stripe' );

	// Localize data for connecting to Stripe.
	$stripe_account_id = get_option( 'rwstripe_stripe_account_id' );
	$stripe_connected_environment = get_option( 'rwstripe_stripe_environment', 'live' );
	$connect_url_base = apply_filters( 'rwstipe_stripe_connect_url', 'https://connect.restrictwithstripe.com' );
	$connect_in_test_mode = apply_filters( 'rwstripe_connect_in_test_mode', false );

	if ( empty( $stripe_account_id ) ) {
		// Need to connect to Stripe.
		$stripe_connect_url = add_query_arg(
			array(
				'action' => 'authorize',
				'gateway_environment' => $connect_in_test_mode ? 'sandbox' : 'live',
				'return_url' => rawurlencode( admin_url( 'options-general.php?page=rwstripe' ) ),
			),
			$connect_url_base
		);
	} else {
		// Already connected to Stripe.
		$stripe_connect_url = add_query_arg(
			array(
				'action' => 'disconnect',
				'gateway_environment' => $stripe_connected_environment === 'live' ? 'live' : 'sandbox',
				'stripe_user_id' => $stripe_account_id,
				'return_url' => rawurlencode( admin_url( 'options-general.php?page=rwstripe' ) ),
			),
			$connect_url_base
		);
	}

	// Localize the settings.
	wp_localize_script( 'rwstripe-settings', 'rwstripe', array(
		'stripe_account_id' => $stripe_account_id,
		'stripe_environment' => $stripe_connected_environment,
		'stripe_connect_url' => $stripe_connect_url,
		'stripe_dashboard_url' => rwstripe_get_dashboard_link(),
		'connect_in_test_mode' => $connect_in_test_mode,
		'admin_url' => admin_url(),
		'connection_error' => $rwstripe_connection_error ?: '',
	) );

	// Enqueue style for settings page.
	wp_enqueue_style(
		'rwstripe-settings',
		plugins_url( 'blocks/build/settings/style-admin.css', RWSTRIPE_BASE_FILE ),
		array( 'wp-components' ),
	);

	// Enqueue style for admin pages.
	wp_enqueue_style(
		'rwstripe-admin',
		plugins_url( 'css/rwstripe-admin.css', RWSTRIPE_BASE_FILE ),
		array(),
		RWSTRIPE_VERSION
	);
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
	wp_set_script_translations( 'rwstripe-sidebar', 'restrict-with-stripe' );
	wp_localize_script( 'rwstripe-sidebar', 'rwstripeSidebar', array(
		'restricted_product_ids_meta_key' => rwstripe_get_meta_key( 'restricted_product_ids' ),
		'stripe_products_url' => rwstripe_get_dashboard_link() . 'products?active=true',
	) );
}
add_action( 'enqueue_block_editor_assets', 'rwstripe_enqueue_block_editor_assets' );
