<?php

/**
 * Show settings to restrict a post/page.
 *
 * @since TBD
 */
function rwstripe_page_meta() {
	global $post;
	$rwstripe = RWStripe_Stripe::get_instance();
    $products = $rwstripe->get_all_products();

	$current_stripe_product_ids = get_post_meta( $post->ID, 'rwstripe_stripe_product_ids', true );
	if ( ! is_array( $current_stripe_product_ids ) ) {
		$current_stripe_product_ids = array();
	}
	?>
	<input type="hidden" name="rwstripe_noncename" id="rwstripe_noncename" value="<?php echo esc_attr( wp_create_nonce( plugin_basename(__FILE__) ) )?>" />
	<select name="rwstripe_stripe_product_ids[]">
		<option value=''>-- <?php esc_html_e( 'None', 'restrict-with-stripe' ); ?> --</option>
		<?php
		foreach ( $products as $product ) {
			$selected_modifier = in_array( $product->id, $current_stripe_product_ids ) ? ' selected' : '';
			?>
			<option value='<?php echo esc_attr( $product->id ); ?>' <?php echo $selected_modifier ?>><?php echo esc_html( $product->name ); ?></option>
			<?php
		}
		?>
	</select>
	<?php
}

/**
 * Save page/post restriction settings.
 *
 * @since TBD
 *
 * @param int $post_id The ID of the post being saved.
 */
function rwstripe_page_save( $post_id ) {
	global $wpdb;

	if( empty( $post_id ) ) {
		return false;
	}

	// Post is saving somehow with our meta box not shown.
	if ( ! isset( $_POST['rwstripe_noncename'] ) ) {
		return $post_id;
	}

	// Verify the nonce.
	if ( ! wp_verify_nonce( $_POST['rwstripe_noncename'], plugin_basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// Don't try to update meta fields on AUTOSAVE.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// Check permissions.
	if( ! empty( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	}

	// OK, we're authenticated. We need to find and save the data.
	$restricted_product_ids = array();
	if( ! empty( $_POST['rwstripe_stripe_product_ids'] && is_array( $_POST['rwstripe_stripe_product_ids'] ) ) ) {
		foreach ( $_POST['rwstripe_stripe_product_ids'] as $product_id ) {
			if ( ! empty( $product_id ) ) {
				$restricted_product_ids[] = sanitize_text_field( $product_id );
			}
		}
	}

	// Update the post meta.
	update_post_meta( $post_id, 'rwstripe_stripe_product_ids', $restricted_product_ids );

	return $restricted_product_ids;
}

/**
 * Enqueue fields to restrict a post/page.
 *
 * @since TBD
 */
function rwstripe_page_meta_wrapper() {
	add_meta_box( 'rwstripe_page_meta', esc_html__( 'Require Stripe Product', 'restrict-with-stripe' ), 'rwstripe_page_meta', 'page', 'side', 'high' );
	add_meta_box( 'rwstripe_page_meta', esc_html__( 'Require Stripe Product', 'restrict-with-stripe' ), 'rwstripe_page_meta', 'post', 'side', 'high' );
}
if ( is_admin() ) {
	add_action( 'admin_menu', 'rwstripe_page_meta_wrapper' );
	add_action( 'save_post', 'rwstripe_page_save' );
}