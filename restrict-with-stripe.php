<?php
/**
 * Plugin Name: Restrict With Stripe
 * Description: Monetize your content using Stripe.
 * Version: 1.0.9
 * Author: Stranger Studios
 * Text Domain: restrict-with-stripe
 * License: GPLv3
 */
define( 'RWSTRIPE_VERSION', '1.0.9' );
define( 'RWSTRIPE_DIR', dirname( __FILE__ ) );
define( 'RWSTRIPE_BASE_FILE', __FILE__ );
define( 'RWSTRIPE_BASENAME', plugin_basename( __FILE__ ) );

function rwstripe_load_textdomain() {
	load_plugin_textdomain( 'restrict-with-stripe', false, plugin_basename( RWSTRIPE_DIR ) . '/languages' );
}
add_action( 'plugins_loaded', 'rwstripe_load_textdomain' );

require_once( RWSTRIPE_DIR . '/adminpages/settings.php' ); // Set up settings page in admin.
require_once( RWSTRIPE_DIR . '/adminpages/profile.php' );  // Add settings for admin profile page.
require_once( RWSTRIPE_DIR . '/adminpages/terms.php' ); // Allow restricting terms.
require_once( RWSTRIPE_DIR . '/adminpages/plugins.php' ); // Add links to settings on plugins page.
require_once( RWSTRIPE_DIR . '/adminpages/meta-boxes.php' ); // Add meta boxes to posts and pages.

require_once( RWSTRIPE_DIR . '/includes/functions.php' ); // Declare common functions.
require_once( RWSTRIPE_DIR . '/includes/content.php' );   // Filter content to be shown in frontend
require_once( RWSTRIPE_DIR . '/includes/scripts.php' );   // Enqueue all JavaScript.
require_once( RWSTRIPE_DIR . '/includes/blocks.php' );    // Register blocks.
require_once( RWSTRIPE_DIR . '/includes/currencies.php' ); // Handle currency formatting.
require_once( RWSTRIPE_DIR . '/includes/menus.php' ); // Set up custom menu items.

require_once( RWSTRIPE_DIR . '/classes/class-rwstripe-stripe.php' );   // Facilitate interactions with Stripe.
require_once( RWSTRIPE_DIR . '/classes/class-rwstripe-rest-api.php' ); // Set up REST API endpoints.
