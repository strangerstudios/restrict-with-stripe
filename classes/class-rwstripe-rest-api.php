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
			 * Example: https://example.com/wp-json/rwstripe/v1/get_all_products
			 */
			register_rest_route( $rwstripe_namespace, '/get_all_products',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $this, 'get_all_products'),
						'permission_callback' => array( $this, 'permissions_check_admin' ),
					)
				)
			);
		}

		/**
		 * Get all Stripe products.
		 * @since 1.0
		 * Example: https://example.com/wp-json/rwstripe/v1/get_all_products
		 */
		public function get_all_products( $request ) {
			$rwstripe = RWStripe_Stripe::get_instance();
			$products = $rwstripe->get_all_products();
			if ( ! empty( $products ) ) {
				$products = $products->data;
			}
			return new WP_REST_Response( $products, 200 );
		}

		/**
		 * Check if the current user has admin permissions.
		 * @since 1.0
		 */
		public function permissions_check_admin( $request ) {
			return current_user_can( 'manage_options' );
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
