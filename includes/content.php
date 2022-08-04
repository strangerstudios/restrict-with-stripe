<?php

/**
 * Swap out page/post content with a prompt to purchase access
 * if the page/post is restricted and the user does not already have access.
 *
 * @since 1.0
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
		rwstripe_restricted_content_message( $stripe_product_ids );
		$content = ob_get_clean();
	}
	return $content;
}
add_filter( 'the_content', 'rwstripe_the_content' );

/**
 * Output the "restricted content" message.
 *
 * @since 1.0
 *
 * @param array|string $product_ids The product IDs that restrict the content.
 */
function rwstripe_restricted_content_message( $product_ids ) {
	if ( ! is_array( $product_ids ) ) {
		$product_ids = array( $product_ids );
	}

	$restriced_content_message_options = rwstripe_get_restricted_content_message_options();

	// Build an array of purchasable products.
	$purchasable_products = array();
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	foreach ( $product_ids as $product_id ) {
		$product = $RWStripe_Stripe->get_product( $product_id );
		if ( ! empty( $product->default_price ) ) {
			$purchasable_products[] = $product;
		}
	}

	// Build restricted content message.
	?>
	<div>
		<?php
		if ( empty( $purchasable_products ) ) {
			// No products available for purchase.
			echo esc_html( $restriced_content_message_options['not_purchasable_message'] );
		} elseif ( ! is_user_logged_in() ) {
			// User not logged in. Show form to create account and purchase product.
			echo strip_tags( str_replace( '!!login_url!!', wp_login_url( get_permalink() ), $restriced_content_message_options['logged_out_message'] ), '<a>' );
			?>
			<br/>
			<div class="rwstripe-checkout-error-message"></div>
			<form class="rwstripe-restricted-content-message-register">
				<input type="email" name="rwstripe-email" placeholder="<?php echo esc_attr( __( 'Email Adress', 'restrict_with_stripe' ) ); ?>" /><br/>
				<?php
				// Maybe collect a password.
				if ( $restriced_content_message_options['logged_out_collect_password'] ) {
					?>
					<input type="password" name="rwstripe-password" placeholder="<?php echo esc_attr( __( 'Password', 'restrict_with_stripe' ) ); ?>" autocomplete="on" /><br/>
					<?php
				}

				// If there are multiple purchasable products, show a dropdown of products.
				if ( count( $purchasable_products ) > 1 ) {
					?>
					<select name="rwstripe-product-id">
						<option value="">-- <?php echo esc_html( __( 'Select a product', 'restrict_with_stripe' ) ); ?> --</option>
						<?php
						foreach ( $purchasable_products as $product ) {
							?>
							<option value="<?php echo esc_attr( $product->default_price ); ?>"><?php echo esc_html( $product->name ); ?></option>
							<?php
						}
						?>
					</select>
					<br/>
					<?php
				} else {
					?>
					<input type="hidden" name="rwstripe-product-id" value="<?php echo esc_attr( $purchasable_products[0]->default_price ); ?>" />
					<?php
				}
				?>
				<button type="submit" class="rwstripe-checkout-button"><?php echo esc_html( $restriced_content_message_options['logged_out_button_text'], 'restrict-with-stripe' ); ?></button>
			</form>
			<?php
		} else {
			// User is logged in. Show form to purchase product.
			?>
			<?php echo esc_html( $restriced_content_message_options['logged_in_message'] ) ?>
			<br/>
			<div class="rwstripe-checkout-error-message"></div>
			<form class="rwstripe-restricted-content-message-register">
				<?php
				// If there are multiple purchasable products, show a dropdown of products.
				if ( count( $purchasable_products ) > 1 ) {
					?>
					<select name="rwstripe-product-id">
						<option value="">-- <?php echo esc_html( __( 'Select a product', 'restrict_with_stripe' ) ); ?> --</option>
						<?php
						foreach ( $purchasable_products as $product ) {
							?>
							<option value="<?php echo esc_attr( $product->default_price ); ?>"><?php echo esc_html( $product->name ); ?></option>
							<?php
						}
						?>
					</select>
					<br/>
					<?php
				} else {
					?>
					<input type="hidden" name="rwstripe-product-id" value="<?php echo esc_attr( $purchasable_products[0]->default_price ); ?>" />
					<?php
				}
				?>
				<button type="submit" class="rwstripe-checkout-button"><?php echo esc_html( $restriced_content_message_options['logged_in_button_text'], 'restrict-with-stripe' ); ?></button>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * If user is not logged in, hide menu items attempting to send users to customer portal.
 *
 * @since 1.0
 *
 * @param array $items The menu items to potentially hide.
 * @return array $items The menu items to show.
 */
function rwstripe_hide_customer_portal_menu_items( $items ) {
	if ( ! is_user_logged_in() ) {
		foreach ( $items as $key => $item ) {
			if ( in_array( 'rwstripe-customer-portal-button', $item->classes ) ) {
				unset( $items[$key] );
			}
		}
	}
	return $items;
}
add_action( 'wp_nav_menu_objects', 'rwstripe_hide_customer_portal_menu_items' );