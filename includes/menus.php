<?php

/**
 * Add RWStripe nav menu meta box.
 *
 * @since 1.0
 */
function rwstripe_add_nav_menu_meta_box() {
    add_meta_box(
        'rwstripe_nav_menu_meta_box',
        esc_html__( 'Restrict With Stripe', 'restrict-with-stripe' ),
        'rwstripe_nav_menu_meta_box_callback',
        'nav-menus',
        'side',
        'low'
    );
}
add_action( 'admin_head-nav-menus.php', 'rwstripe_add_nav_menu_meta_box' );

/**
 * Display RWStripe nav menu meta box.
 *
 * @since 1.0
 */
function rwstripe_nav_menu_meta_box_callback() {
    global $nav_menu_selected_id;
    ?>
	<div id="rwstripe-page-items" class="posttypediv">
		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Stripe Customer Portal', 'restrict-with-stripe'); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-type-name" name="menu-item[-1][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php esc_attr_e( 'Manage Purchases', 'restrict-with-stripe'); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="#">
					<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="rwstripe-customer-portal-button">
				</li>
			</ul>
		</div>
		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-rwstripe-page-items" id="submit-rwstripe-page-items" />
				<span class="spinner"></span>
			</span>
		</p>
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