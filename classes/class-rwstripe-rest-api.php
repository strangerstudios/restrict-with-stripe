<?php

if ( class_exists( 'WP_REST_Controller' ) ) {
	class RWStripe_REST_API extends WP_REST_Controller {

		/**
		 * Register REST API rotes for the plugin.
		 *
		 * @since 1.0
		 */
		public function register_routes() {
			$rwstripe_namespace = 'rwstripe/v1';

			/**
			 * Get all Stripe products.
			 * @since 1.0
			 * Example: https://example.com/wp-json/rwstripe/v1/products
			 */
			register_rest_route( $rwstripe_namespace, '/products',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $this, 'get_products'),
						'permission_callback' => array( $this, 'permissions_check_admin' ),
					)
				)
			);

			/**
			 * Get link to checkout.
			 * @since 1.0
			 * Example: https://example.com/wp-json/rwstripe/v1/checkout
			 */
			register_rest_route( $rwstripe_namespace, '/checkout',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $this, 'create_checkout_session'),
						'args' => array(
							'price_id' => array(
								'required' => true,
							),
							'redirect_url' => array(
								'required' => true,
							),
							'email' => array(
								'default' => null,
							),
							'password' => array(
								'default' => null,
							),
						),
						'permission_callback' => '__return_true',
					)
				)
			);

			/**
			 * Get customer portal url.
			 * @since 1.0
			 * Example: https://example.com/wp-json/rwstripe/v1/customer_portal_url
			 */
			register_rest_route( $rwstripe_namespace, '/customer_portal_url',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $this, 'get_customer_portal_url'),
						'permission_callback' => array( $this, 'permissions_check_is_logged_in' ),
					)
				)
			);
		}

		/**
		 * Get all Stripe products.
		 * @since 1.0
		 * Example: https://example.com/wp-json/rwstripe/v1/products
		 */
		public function get_products( $request ) {
			$rwstripe = RWStripe_Stripe::get_instance();
			$products = $rwstripe->get_all_products();
			if ( is_string( $products ) ) {
				return new WP_Error( 'rwstripe_error', $products, array( 'status' => 400 ) );
			}
			return new WP_REST_Response( $products->data, 200 );
		}

		/**
		 * Get link to checkout.
		 * @since 1.0
		 * Example: https://example.com/wp-json/rwstripe/v1/checkout
		 */
		public function create_checkout_session( $request ) {
			$params = $request->get_params();

			$price_id    = sanitize_text_field( $params['price_id'] );
			if ( empty( $price_id ) ) {
				return new WP_Error( 'rwstripe_error', __( 'Please select a product.', 'rwstripe' ), array( 'status' => 400 ) );
			}

			$redirect_url = esc_url( $params['redirect_url'] );
		
			$current_user_id = get_current_user_id();
			if ( empty( $current_user_id ) ) {
				// Check if email is valid.
				if ( ! is_email( $params['email'] ) ) {
					return new WP_Error( 'rwstripe_error', __( 'Email is invalid.', 'rwstripe' ), array( 'status' => 400 ) );
				}

				// Check if user should have sent a password:
				$restricted_content_message_options = rwstripe_get_restricted_content_message_options();
				if ( $restricted_content_message_options['logged_out_collect_password'] ) {
					// We should have a password. Check if we do.
					$password = sanitize_text_field( $params['password'] );
				} else {
					// We should not have a password. Let's generate a random one.
					$password = wp_generate_password();
				}

				// Make sure that we have a password.
				if ( empty( $password ) ) {
					return new WP_Error( 'rwstripe_error', __( 'Password is required.', 'rwstripe' ), array( 'status' => 400 ) );
				}

				// Create a new user with the email address.
				$current_user_id = wp_create_user( sanitize_email( $_REQUEST['email'] ), $password, sanitize_email( $params['email'] ) );
	
				// Check that user was created successfully.
				if ( is_wp_error( $current_user_id ) ) {
					return new WP_Error( 'rwstripe_error', __( 'Error creating user.', 'rwstripe' ), array( 'status' => 400 ) );
				}
	
				// Log the user into this new account.
				wp_set_current_user( $current_user_id );
				wp_set_auth_cookie( $current_user_id, true );
			}
		
			$customer_id = rwstripe_get_customer_id_for_user( $current_user_id );
			if ( empty( $customer_id ) ) {
				return new WP_Error( 'rwstripe_error', __( 'Error retrieving customer.', 'rwstripe' ), array( 'status' => 400 ) );
			}

			$rwstripe = RWStripe_Stripe::get_instance();
			$checkout_session = $rwstripe->create_checkout_session( $price_id, $customer_id, $redirect_url );
			if ( is_string( $checkout_session ) ) {
				return new WP_Error( 'rwstripe_error', __( 'Error creating checkout session.', 'rwstripe' ) . ' ' . $checkout_session, array( 'status' => 400 ) );
			}

			return new WP_REST_Response( $checkout_session->url, 200 );
		}

		/**
		 * Get customer portal url.
		 * @since 1.0
		 * Example: https://example.com/wp-json/rwstripe/v1/customer_portal_url
		 */
		public function get_customer_portal_url( $request ) {
			$customer_id = rwstripe_get_customer_id_for_user();
			if ( empty( $customer_id ) ) {
				return new WP_REST_Response( array( 'error' => 'Could not get customer ID.' ), 500 );
			}

			$rwstripe = RWStripe_Stripe::get_instance();
			$get_customer_portal_session = $rwstripe->get_customer_portal_session( $customer_id );
			if ( is_string( $get_customer_portal_session ) ) {
				return new WP_REST_Response( array( 'error' => 'Could not get customer portal link. ' . $get_customer_portal_session ), 500 );
			}
			return new WP_REST_Response( $get_customer_portal_session->url, 200 );
		}

		/**
		 * Check if the current user has admin permissions.
		 * @since 1.0
		 */
		public function permissions_check_admin( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Check if the current user is logged in.
		 * @since 1.0
		 */
		public function permissions_check_is_logged_in( $request ) {
			return is_user_logged_in();
		}
	} // End of class

	/**
	 * Initialize the Restrict With Stripe API.
	 * @since 1.0
	 */
	function rwstripe_rest_api_register_custom_routes() {
		$rwstripe_rest_api = new RWStripe_REST_API;
		$rwstripe_rest_api->register_routes();
	}

	add_action( 'rest_api_init', 'rwstripe_rest_api_register_custom_routes', 5 );
}
