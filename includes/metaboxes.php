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
	
	$current_stripe_product_id = get_post_meta( $post->ID, 'rwstripe_stripe_product_id', true );
	?>
	<input type="hidden" name="rwstripe_noncename" id="rwstripe_noncename" value="<?php echo esc_attr( wp_create_nonce( plugin_basename(__FILE__) ) )?>" />
	<select name="rwstripe_stripe_product_id">
		<option value=''>-- None --</option>
		<?php
		foreach ( $products as $product ) {
			$selected_modifier = $current_stripe_product_id === $product->id ? ' selected' : '';
			?>
			<option value='<?php esc_html_e( $product->id ); ?>' <?php echo $selected_modifier ?>><?php esc_html_e( $product->name ); ?></option>
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
	if( ! empty( $_POST['rwstripe_stripe_product_id'] ) ) {
		$mydata = $_POST['rwstripe_stripe_product_id'];
	} else {
		$mydata = NULL;
	}

	// Update the post meta.
	update_post_meta( $post_id, 'rwstripe_stripe_product_id', $mydata );

	return $mydata;
}

/**
 * Enqueue fields to restrict a post/page.
 *
 * @since TBD
 */
function rwstripe_page_meta_wrapper() {
	add_meta_box( 'rwstripe_page_meta', __( 'Require Stripe Product', 'paid-memberships-pro' ), 'rwstripe_page_meta', 'page', 'side', 'high' );
	add_meta_box( 'rwstripe_page_meta', __( 'Require Stripe Product', 'paid-memberships-pro' ), 'rwstripe_page_meta', 'post', 'side', 'high' );
}
if ( is_admin() ) {
	add_action( 'admin_menu', 'rwstripe_page_meta_wrapper' );
	add_action( 'save_post', 'rwstripe_page_save' );
}