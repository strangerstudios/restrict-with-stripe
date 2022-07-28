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
		$new_content_pre = '<div>';
		$new_content_post = '</div>';
		ob_start();

		// The user does not have access. Check if they can purchase access.
		$price = $RWStripe_Stripe->get_default_price_for_product( $stripe_product_ids[0] );
		if ( empty( $price ) ) {
			esc_html_e( 'This product is not purchasable.', 'restrict-with-stripe');
		} elseif(empty( $current_user->ID )) {
			?>
			<?php printf( esc_html__( 'You must create an account or %s to purchase this content.', 'restrict-with-stripe' ), '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'log in', 'restrict-with-stripe' ) . '</a>' ); ?>
			<br/>
			<input name="rwstripe-email" class="rwstripe-email" placeholder="<?php echo esc_attr( __( 'Email Adress', 'restrict_with_stripe' ) ); ?>" /><br/>
			<button type="button" class="rwstripe-checkout-button" value="<?php esc_html_e( $price->id ) ?>"><?php esc_html_e( 'Create Acount and Check Out', 'restrict-with-stripe' ); ?></button>
			<?php
		} else {
			?>
			<?php esc_html_e( 'You do not have access to this content.', 'restrict-with-stripe' ) ?>
			<br/>
			<button type="button" class="rwstripe-checkout-button" value="<?php esc_html_e( $price->id ) ?>"><?php esc_html_e( 'Purchase Access', 'restrict-with-stripe' ); ?></button>
			<?php
		}
		$content = $new_content_pre . ob_get_clean() . $new_content_post;
	}

	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content' );

