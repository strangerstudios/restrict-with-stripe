<?php

/**
 * Swap out page/post content with a prompt to purchase access
 * if the page/post is restricted and the user does not already have access.
 *
 * @since TBD
 *
 * @param string $content The content to potentially replace.
 *
 * @return string $content The new content to show.
 */
function rwstripe_the_content( $content ) {
	global $post, $current_user;

	// Get the product that restricts this page/post if there is one.
	$stripe_product_id = get_post_meta( $post->ID, 'rwstripe_stripe_product_id', true );
	if ( empty( $stripe_product_id ) || is_admin() ) {
		// The page/post is not restricted or we are in the admin, so we can return the content.
		return $content;
	}

	// Check if the current user has access to this restricted page/post.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $stripe_product_id ) ) {
		// The user does not have access. Check if they can purchase access.
		$price = $RWStripe_Stripe->get_default_price_for_product( $stripe_product_id );
		if ( empty( $price ) ) {
			return '<p>This product is not purchasable.</a>';
		}

		ob_start();
		// Check if the current user is logged in. If not, we need to create an account for them.
		if ( empty( $current_user->ID ) ) {
			?>
			<p>You must be create an account to purchase a product.</a><br/>
			<label for="rwstripe-email">Email:</label>
			<input name="rwstripe-email" class="rwstripe-email" /><br/>
			<?php
		}

		// Show button to purchase access.
		// TODO: Improve formatting of prices.
		if ( empty( $price->recurring ) ) {
			$price_text =  $price->unit_amount_decimal/100 . ' ' . $price->currency;
		} else {
			$price_text = $price->unit_amount_decimal/100 . ' ' . $price->currency . ' / ' . $price->recurring->interval_count . ' ' . $price->recurring->interval;
		}
		?>
			<button type="button" class="rwstripe-checkout-button" value="<?php esc_html_e( $price->id ) ?>">Buy Now for <?php esc_html_e( $price_text ) ?></button>
		<?php
		$content = ob_get_clean();
	}

	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content' );

/**
 * Set up JavaScript to handle the purchase button.
 *
 * @since TBD
 */
function rwstripe_enqueue_scripts() {
    wp_enqueue_script( "stripe", "https://js.stripe.com/v3/", array(), null );
    $localize_vars = array(
        'ajaxUrl' => admin_url( "admin-ajax.php" ),
    );

    wp_register_script( 'rwstripe_stripe',
        plugins_url( 'js/rwstripe-stripe.js', RWSTRIPE_BASE_FILE ),
        array( 'jquery' ),
        RWSTRIPE_VERSION
    );
    wp_localize_script( 'rwstripe_stripe', 'rwstripeStripe', $localize_vars );
    wp_enqueue_script( 'rwstripe_stripe' );
}
add_action( 'wp_enqueue_scripts', 'rwstripe_enqueue_scripts' );

/**
 * Handle the AJAX request to send a user to Stripe after optionally
 * creating a new WP_User.
 *
 * TODO: Add error handling.
 *
 * @since TBD
 */
function rwstripe_create_checkout_session_ajax() {
    if ( empty( $_REQUEST['price_id'] ) ) {
        exit;
    }
    $price_id    = $_REQUEST['price_id'];

	if ( empty( $_REQUEST['redirect_url'] ) ) {
        exit;
    }
    $redirect_url = $_REQUEST['redirect_url'];

    $current_user_id = get_current_user_id();
    if ( empty( $current_user_id ) ) {
        if ( ! empty( $_REQUEST['email'] ) ) {
            // Create a new user with the email address.
            $current_user_id = wp_create_user( $_REQUEST['email'], wp_generate_password(), $_REQUEST['email'] );

            // Check that user was created successfully.
            if ( is_wp_error( $current_user_id ) ) {
                exit;
            }

            // Log the user into this new account.
            wp_set_current_user( $current_user_id );
            wp_set_auth_cookie( $current_user_id, true );
        }
    }

    $customer_id = rwstripe_get_customer_id_for_user( $current_user_id );

    $rwstripe_stripe  =  RWStripe_Stripe::get_instance();
    $checkout_session = $rwstripe_stripe->create_checkout_session( $price_id, $customer_id, $redirect_url );
    echo json_encode( array( 'checkout_session_url' => $checkout_session->url ) );
    exit;
}
add_action( 'wp_ajax_rwstripe_create_checkout_session', 'rwstripe_create_checkout_session_ajax' );
add_action( 'wp_ajax_nopriv_rwstripe_create_checkout_session', 'rwstripe_create_checkout_session_ajax' );
