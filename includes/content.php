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
		ob_start();
		rwstripe_restricted_content_message( array( 'rwstripe_product_ids' => $stripe_product_ids ) );
		$content = ob_get_clean();
	}
	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content' );

/**
 * Output the "restricted content" message.
 *
 * @since TBD
 *
 * @param array $atts The message attributes.
 */
function rwstripe_restricted_content_message( $atts = array() ) {
	$default_atts = array(
		'rwstripe_logged_out_message' => get_option( 'rwstripe_logged_out_message', __( 'You must create an account or <a href="!!login_url!!">log in</a> to purchase this content.', 'restrict-with-stripe' ) ),
		'rwstripe_logged_out_button_text' => get_option( 'rwstripe_logged_out_button_text', __( 'Log In', 'restrict-with-stripe' ) ),
		'rwstripe_logged_in_message' => get_option( 'rwstripe_logged_in_message', __( 'You do not have access to this content.', 'restrict-with-stripe' ) ),
		'rwstripe_logged_in_button_text' => get_option( 'rwstripe_logged_in_button_text', __( 'Purchase Access', 'restrict-with-stripe' ) ),
		'rwstripe_not_purchasable_message' => get_option( 'rwstripe_not_purchasable_message', __( 'This product is not purchasable.', 'restrict-with-stripe' ) ),
		'rwstripe_product_ids' => array(),
	);
	$atts = wp_parse_args( $atts, $default_atts );

	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	$price = $RWStripe_Stripe->get_default_price_for_product( $atts['rwstripe_product_ids'][0] );
	?>
	<div>
		<?php
		if ( empty( $price ) || is_string( $price ) ) {
			echo esc_html( $atts['rwstripe_not_purchasable_message'] );
		} elseif ( ! is_user_logged_in() ) {
			// We want to allow a link to log in if the user is not logged in, so we need to allow <a> tags in the message.
			echo strip_tags( str_replace( '!!login_url!!', wp_login_url( get_permalink() ), $atts['rwstripe_logged_out_message'] ), '<a>' );
			?>
			<br/>
			<div class="rwstripe-checkout-error-message"></div>
			<input name="rwstripe-email" class="rwstripe-email" placeholder="<?php echo esc_attr( __( 'Email Adress', 'restrict_with_stripe' ) ); ?>" /><br/>
			<button type="button" class="rwstripe-checkout-button" value="<?php esc_html_e( $price->id ) ?>"><?php echo esc_html( $atts['rwstripe_logged_in_button_text'], 'restrict-with-stripe' ); ?></button>
			<?php
		} else {
			?>
			<?php echo esc_html( $atts['rwstripe_logged_in_message'] ) ?>
			<br/>
			<div class="rwstripe-checkout-error-message"></div>
			<button type="button" class="rwstripe-checkout-button" value="<?php echo esc_attr( $price->id ) ?>"><?php echo esc_html( $atts['rwstripe_logged_in_button_text'], 'restrict-with-stripe' ); ?></button>
			<?php
		}
		?>
	</div>
	<?php
}