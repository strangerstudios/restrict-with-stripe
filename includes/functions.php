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
	$errors = array();
	$RWStripe_Stripe = RWStripe_Stripe::get_instance();
	foreach ( $product_ids as $product_id ) {
		$product = $RWStripe_Stripe->get_product( $product_id );
		if ( ! empty( $product->default_price ) ) {
			$purchasable_products[] = $product;
		} elseif ( is_string( $product ) ) {
			$errors[] = $product;
		} else {
			$errors[] = sprintf( __( 'Product %s does not have a default price.', 'restrict-with-stripe' ), $product_id );
		}
	}

	// If the user is an admin and there are errors, show them.
	if ( current_user_can( 'manage_options' ) && ! empty( $errors ) ) {
		echo '<div>';
		echo '<h3>' . __( 'Admins Only', 'restrict-with-stripe' ) . '</h3>';
		echo '<p>' . esc_html__( 'The following errors occured while building the restricted content message:', 'restrict-with-stripe' ) . '</p>';
		echo '<ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}

	// Build restricted content message.
	?>
	<div class="rwstripe-checkout">
		<?php
		if ( empty( $purchasable_products ) ) {
			// No products available for purchase.
			esc_html_e( 'This product is not purchasable.', 'restrict-with-stripe' );
		} elseif ( ! is_user_logged_in() ) {
			// User not logged in. Show form to create account and purchase product.
			?>
			<div class="rwstripe-checkout-heading"><?php esc_html_e( 'Purchase Access', 'restrict-with-stripe' ); ?></div>

			<?php
				// Show a message if on a term archive.
				if ( is_category() || is_tag() ) { ?>
					<p><?php echo sprintf( __( 'Complete checkout now to access everything in <em>%s</em>.', 'restrict-with-stripe' ), get_the_archive_title() ); ?></p>
					<?php
				}

				// Show price if only one product is available.
				if ( count( $purchasable_products ) == 1 ) {
					$price = $RWStripe_Stripe->get_price( $purchasable_products[0]->default_price );
					echo rwstripe_format_price( $price );
				}

			?>
			<p><?php echo strip_tags( sprintf( __( 'Create a new account or <a href="%s">log in</a> to purchase access.', 'restrict-with-stripe' ), wp_login_url( get_permalink() ) ), '<a>' ); ?></p>
			<div class="rwstripe-error"></div>
			<form class="rwstripe-register">
				<input type="email" name="rwstripe-email" placeholder="<?php echo esc_attr( __( 'Email Address', 'restrict_with_stripe' ) ); ?>" />
				<?php
				// Maybe collect a password.
				if ( get_option( 'rwstripe_collect_password', true ) ) {
					?>
					<input type="password" name="rwstripe-password" placeholder="<?php echo esc_attr( __( 'Password', 'restrict_with_stripe' ) ); ?>" autocomplete="on" />
					<?php
				}

				// Show dropdown of products to purchase.
				rwstripe_restricted_content_message_render_product_dropdown( $purchasable_products );

				// Build text for submit button.
				$submit_text = __('Create Account &amp; Checkout', 'restrict-with-stripe' );
				?>
				<button type="submit" class="rwstripe-checkout-button"><?php echo esc_html( $submit_text ); ?></button>
			</form>
			<?php
		} else {
			// User is logged in. Show form to purchase product.
			?>
			<div class="rwstripe-checkout-heading"><?php esc_html_e( 'Purchase Access', 'restrict-with-stripe' ); ?></div>
			<?php
				// Show a message if on a term archive.
				if ( is_category() || is_tag() ) { ?>
					<p><?php echo sprintf( __( 'Complete checkout now to access everything in <em>%s</em>.', 'restrict-with-stripe' ), get_the_archive_title() ); ?></p>
					<?php
				}

				// Show price if only one product is available.
				if ( count( $purchasable_products ) == 1 ) {
					$price = $RWStripe_Stripe->get_price( $purchasable_products[0]->default_price );
					echo rwstripe_format_price( $price );
				}
			?>
			<div class="rwstripe-error"></div>
			<form class="rwstripe-register">
				<?php
				// Show dropdown of products to purchase.
				rwstripe_restricted_content_message_render_product_dropdown( $purchasable_products );

				// Build text for submit button.
				$submit_text = __('Checkout Now', 'restrict-with-stripe' );
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
			<option value="">-- <?php echo esc_html( __( 'Choose one', 'restrict_with_stripe' ) ); ?> --</option>
			<?php
			foreach ( $purchasable_products as $product ) {
				$price = $RWStripe_Stripe->get_price( $product->default_price );
				?>
				<option value="<?php echo esc_attr( $product->default_price ); ?>"><?php echo esc_html( $product->name ) . ' (' . rwstripe_format_price( $price, true ) . ')'; ?></option>
				<?php
			}
			?>
		</select>
		<?php
	} else {
		?>
		<input type="hidden" name="rwstripe-product-id" value="<?php echo esc_attr( $purchasable_products[0]->default_price ); ?>" />
		<?php
	}
}
