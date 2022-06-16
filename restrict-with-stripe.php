<?php
/**
 * Plugin Name: Restrict With Stripe
 * Version: 0.0
 * Author: Stranger Studios
 */
define( 'RWSTRIPE_VERSION', '0.0' );
define( 'RWSTRIPE_DIR', dirname( __FILE__ ) );
define( 'RWSTRIPE_BASE_FILE', __FILE__ );

require_once( RWSTRIPE_DIR . '/adminpages/settings.php' ); // Set up settings page in admin.

require_once( RWSTRIPE_DIR . '/includes/functions.php' ); // Declare common functions.
require_once( RWSTRIPE_DIR . '/includes/content.php' ); // Restrict page/post content on frontend.
require_once( RWSTRIPE_DIR . '/includes/metaboxes.php' ); // Set page/post restrictions in admin.
require_once( RWSTRIPE_DIR . '/includes/profile.php' ); // Make Stripe Customer ID editable and link to Stripe Customer Portal.

require_once( RWSTRIPE_DIR . '/shortcodes/rwstripe-customer-portal.php' ); // Shortcode to display link to Stripe Customer Portal.

require_once( RWSTRIPE_DIR . '/classes/class-rwstripe-stripe.php' );   // Facilitate interactions with Stripe.