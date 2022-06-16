<?php

/**
 * Allow admins to set Stripe Customer IDs for users and
 * access their Stripe Customer Portal.
 *
 * @since TBD
 *
 * @param WP_User $user User being viewed.
 */
function rwstripe_edit_user_profile( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$customer_id = rwstripe_get_customer_id_for_user( $user->ID );
	$rwstripe_stripe = new RWStripe_Stripe();
	$customer_portal = $rwstripe_stripe->get_customer_portal_url( $customer_id );
	?>
	<h2>Restrict With Stripe</h2>
	<table>
		<tr>
			<th>Customer ID</th>
			<td><input type='text' name='rwstripe_customer_id' value='<?php esc_html_e( $customer_id ); ?>'></td>
		</tr>
		<tr>
			<th>Customer Portal</th>
			<td><a href="<?php echo $customer_portal; ?>"><?php echo $customer_portal; ?></a></td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'rwstripe_edit_user_profile' );
add_action( 'edit_user_profile', 'rwstripe_edit_user_profile' );

/**
 * Save the Stripe Customer ID for a user.
 *
 * @since TBD
 */
function rwstripe_user_profile_update() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! empty( $_REQUEST['user_id'] ) ) {
		$user_id = $_REQUEST['user_id'];
	} else {
		return;
	}

	if ( isset( $_REQUEST['rwstripe_customer_id'] ) ) {
		update_user_meta( $user_id, 'rwstripe_customer_id', sanitize_text_field( $_REQUEST['rwstripe_customer_id'] ) );
	}
}
add_action( 'personal_options_update', 'rwstripe_user_profile_update' );
add_action( 'edit_user_profile_update', 'rwstripe_user_profile_update' );
