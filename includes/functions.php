<?php

/**
 * Get the customer ID for a user and creates a new customer if one does not exist.
 * 
 * @since 1.0
 *
 * @param int $user_id User ID. Defaults to current user.
 *
 * @return string|null Customer ID or null if customer cannot be created.
 */
function rwstripe_get_customer_id_for_user( $user_id = null ) {
    // If no user ID is provided, use the current user.
    if ( empty( $user_id ) ) {
        global $current_user;
        $user_id = $current_user->ID;
    }

    // If we still don't have a user ID, bail.
    if ( empty( $user_id ) ) {
        return null;
    }

    // Get the customer ID for the user.
    $customer_id = get_user_meta( $user_id, 'rwstripe_customer_id', true );

    // If the user does not have a customer ID yet, create a new customer.
    if ( empty( $customer_id ) ) {
        $rwstripe = RWStripe_Stripe::get_instance();
        $user = get_userdata( $user_id );
        $new_customer = $rwstripe->create_customer_with_email( $user->user_email );
        if ( is_string( $new_customer ) ) {
            // If we cannot create a new customer, bail.
            return null;
        }
        $customer_id = $new_customer->id;
        update_user_meta( $user_id, 'rwstripe_customer_id', $customer_id );
    }
    return $customer_id;
}

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
			esc_html_e( 'This product is not purchasable.', 'restrict-with-stripe' );
		} elseif ( ! is_user_logged_in() ) {
			// User not logged in. Show form to create account and purchase product.
			echo strip_tags( sprintf( __( 'You must create an account or <a href="%s">log in</a> to purchase this content.', 'restrict-with-stripe' ), wp_login_url( get_permalink() ) ), '<a>' );
			?>
			<br/>
			<div class="rwstripe-checkout-error-message"></div>
			<form class="rwstripe-restricted-content-message-register">
				<input type="email" name="rwstripe-email" placeholder="<?php echo esc_attr( __( 'Email Adress', 'restrict_with_stripe' ) ); ?>" /><br/>
				<?php
				// Maybe collect a password.
				if ( get_option( 'rwstripe_collect_password', true ) ) {
					?>
					<input type="password" name="rwstripe-password" placeholder="<?php echo esc_attr( __( 'Password', 'restrict_with_stripe' ) ); ?>" autocomplete="on" /><br/>
					<?php
				}

				// Show dropdown of products to purchase.
				rwstripe_restricted_content_message_render_product_dropdown( $purchasable_products );

				// Build text for submit button.
				$submit_text = __('Create Account and Check Out', 'restrict-with-stripe' );

				// Show price if only one product is available.
				if ( count( $purchasable_products ) == 1 ) {
					$price = $RWStripe_Stripe->get_price( $purchasable_products[0]->default_price );
					$submit_text .= ' (' . rwstripe_format_price( $price ) . ')';
				}

				?>
				<button type="submit" class="rwstripe-checkout-button"><?php echo esc_html( $submit_text ); ?></button>
			</form>
			<?php
		} else {
			// User is logged in. Show form to purchase product.
			?>
			<?php esc_html_e( 'You do not have access to this content.', 'restrict-with-stripe' ); ?>
			<br/>
			<div class="rwstripe-checkout-error-message"></div>
			<form class="rwstripe-restricted-content-message-register">
				<?php
				// Show dropdown of products to purchase.
				rwstripe_restricted_content_message_render_product_dropdown( $purchasable_products );

				// Build text for submit button.
				$submit_text = __('Purchase', 'restrict-with-stripe' );

				// Show price if only one product is available.
				if ( count( $purchasable_products ) == 1 ) {
					$price = $RWStripe_Stripe->get_price( $purchasable_products[0]->default_price );
					$submit_text .= ' (' . rwstripe_format_price( $price ) . ')';
				}

				?>
				<button type="submit" class="rwstripe-checkout-button"><?php echo esc_html( $submit_text ); ?></button>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Helper function for rendering the product dropdown in the restricted content message.
 *
 * @since 1.0
 *
 * @param array $purchasable_products The products to render in the dropdown.
 */
function rwstripe_restricted_content_message_render_product_dropdown( $purchasable_products ) {
	// If there are multiple purchasable products, show a dropdown of products.
	if ( count( $purchasable_products ) > 1 ) {
		$RWStripe_Stripe = RWStripe_Stripe::get_instance();
		?>
		<select name="rwstripe-product-id">
			<option value="">-- <?php echo esc_html( __( 'Select a product', 'restrict_with_stripe' ) ); ?> --</option>
			<?php
			foreach ( $purchasable_products as $product ) {
				$price = $RWStripe_Stripe->get_price( $product->default_price );
				?>
				<option value="<?php echo esc_attr( $product->default_price ); ?>"><?php echo esc_html( $product->name ) . ' ( ' . rwstripe_format_price( $price ) . ' )'; ?></option>
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
}
