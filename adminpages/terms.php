<?php

/**
 * Show checkboxes to restrict content when creating a new term.
 *
 * @since 1.0
 */
function rwstripe_term_add_form_fields() {
	// Render form field div.
	?>
	<div class="form-field">
		<label><?php _e( 'Restrict with Stripe', 'restrict-with-stripe' ); ?></label>
		<?php

		// Get all products from Stripe.
		$RWStripe_Stripe = RWStripe_Stripe::get_instance();
		$products = $RWStripe_Stripe->get_all_products();
		$meta_key = rwstripe_get_meta_key( 'restricted_product_ids' );
		if ( empty( $meta_key ) ) {
			// Not connected to Stripe.
			echo '<p>' . __( 'You must connect to Stripe to restrict content.', 'restrict-content-pro' ) . '</p>';
		} elseif ( is_string( $products ) ) {
			echo esc_html( __( 'Error getting products.', 'restrict-with-stripe' ) . ' ' . $products );
		} else {
			// If we have lots of products, put checkboxes in a scrollable div.
			if ( count( $products ) > 10 ) {
				?>
				<div class="rwstripe-scrollable-div">
				<?php
			}

			// Render checkboxes for each product.
			foreach ( $products as $product ) {
				?>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $meta_key ); ?>[]" value="<?php echo esc_attr( $product->id ); ?>" >
					<?php 
						echo esc_html( $product->name );
						if ( empty( $product->default_price ) ) {
							echo ' (' . esc_html__( 'no default price set', 'restrict-with-stripe' ) . ')';
						}
					?>
				</label>
				<?php
			}

			// Close scrollable div.
			if ( count( $products ) > 10 ) {
				?>
				</div>
				<?php
			}
		}

		// Close form field div.
		?>
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'rwstripe_term_add_form_fields' );
add_action( 'post_tag_add_form_fields', 'rwstripe_term_add_form_fields' );

/**
 * Show checkboxes to restrict content when editing a term.
 *
 * @since 1.0
 *
 * @param WP_Term $term The term object.
 */
function rwstripe_term_edit_form_fields( $term ) {
	// Render table row.
	?>
	<tr class="form-field">
		<th scope="row"><label><?php _e( 'Restrict with Stripe', 'restrict-with-stripe' ); ?></label></th>
		<td>
		<?php

		// Get all products from Stripe.
		$RWStripe_Stripe = RWStripe_Stripe::get_instance();
		$products = $RWStripe_Stripe->get_all_products();
		$meta_key = rwstripe_get_meta_key( 'restricted_product_ids' );
		if ( empty( $meta_key ) ) {
			// Not connected to Stripe.
			echo '<p>' . __( 'You must connect to Stripe to restrict content.', 'restrict-content-pro' ) . '</p>';
		} elseif ( is_string( $products ) ) {
			echo esc_html( __( 'Error getting products.', 'restrict-with-stripe' ) . ' ' . $products );
		} else {
			// Get products that are already restricted.
			$restiction_meta = get_term_meta( $term->term_id, $meta_key, true );
			if ( ! is_array( $restiction_meta ) ) {
				$restiction_meta = array();
			}

			// If we have lots of products, put checkboxes in a scrollable div.
			if ( count( $products ) > 10 ) {
				?>
				<div class="rwstripe-scrollable-div">
				<?php
			}

			// Render checkboxes for each product.
			foreach ( $products as $product ) {
				?>
					<input type="checkbox" name="<?php echo esc_attr( $meta_key ); ?>[]" value="<?php echo esc_attr( $product->id ); ?>" <?php checked( in_array( $product->id, $restiction_meta ) ); ?> >
					<label>
					<?php
						echo esc_html( $product->name );
						if ( empty( $product->default_price ) ) {
							echo ' (' . esc_html__( 'no default price set', 'restrict-with-stripe' ) . ')';
						}
					?>
					</label>
					<br/>
				<?php
			}

			// Close scrollable div.
			if ( count( $products ) > 10 ) {
				?>
				</div>
				<?php
			}
		}
		?>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'rwstripe_term_edit_form_fields', 10, 2 );
add_action( 'post_tag_edit_form_fields', 'rwstripe_term_edit_form_fields', 10, 2 );

/**
 * Save checkboxes to restrict categories and tags when saving a term.
 *
 * @since 1.0
 *
 * @param int $term_id The ID of the term being saved.
 */
function rwstripe_term_saved( $term_id ) {
	// Get the meta key for restricted products.
	$meta_key = rwstripe_get_meta_key( 'restricted_product_ids' );
	if ( empty( $meta_key ) ) {
		// Not connected to Stripe.
		return;
	}

	// Get products that are checked.
	$product_ids = isset( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : array();
	$product_ids = array_map( 'sanitize_text_field', $product_ids );
	$product_ids = array_map( 'trim', $product_ids );
	$product_ids = array_filter( $product_ids );
	$product_ids = array_unique( $product_ids );

	// Save products to term meta.
	update_term_meta( $term_id, $meta_key , $product_ids );
}
 add_action( 'saved_category', 'rwstripe_term_saved' );
 add_action( 'saved_post_tag', 'rwstripe_term_saved' );
 