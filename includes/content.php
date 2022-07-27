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
	$stripe_product_ids = get_post_meta( $post->ID, 'rwstripe_stripe_product_ids', true );
	if ( empty( $stripe_product_ids ) || ! is_array( $stripe_product_ids ) || is_admin() ) {
		// The page/post is not restricted or we are in the admin, so we can return the content.
		return $content;
	}

	// Check if the current user has access to this restricted page/post.
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	if ( empty( $current_user->ID ) || ! $RWStripe_Stripe->customer_has_product( rwstripe_get_customer_id_for_user(), $stripe_product_ids ) ) {
		// The user does not have access. Check if they can purchase access.
		$price = $RWStripe_Stripe->get_default_price_for_product( $stripe_product_ids[0] );
		if ( empty( $price ) ) {
			return '<p>' . esc_html__( 'This product is not purchasable.', 'restrict-with-stripe') . '</p>';
		}

		ob_start();
		// Check if the current user is logged in. If not, we need to create an account for them.
		if ( empty( $current_user->ID ) ) {
			?>
			<p><?php printf( esc_html__( 'You must create an account or %s to purchase a product.', 'restrict-with-stripe' ), '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'log in', 'restrict-with-stripe' ) . '</a>' ); ?></a><br/>
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
			<button type="button" class="rwstripe-checkout-button" value="<?php esc_html_e( $price->id ) ?>"><?php printf( esc_html__( 'Buy Now for %s', 'restrict-with-stripe' ), esc_html( $price_text ) ); ?></button>
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
		'restUrl' => rest_url() . 'rwstripe/v1/',
		'nonce' => wp_create_nonce( 'wp_rest' ),
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
