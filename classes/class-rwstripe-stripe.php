<?php

define( "RWSTRIPE_STRIPE_API_VERSION", "2022-11-15" );

class RWStripe_Stripe {

	// Singlton class.
	private static $instance = null;

	/**
	 * Connect to Stripe.
	 *
	 * @since 1.0
	 */
	private function __construct() {
		$modules = array( 'curl', 'mbstring', 'json' );

		foreach ( $modules as $module ) {
			if ( ! extension_loaded( $module ) ) {
				// Missing a dependency.
				return;
			}
		}

        if ( ! class_exists( "Stripe\Stripe" ) ) {
			require_once( RWSTRIPE_DIR . "/includes/lib/Stripe/init.php" );
		}

		Stripe\Stripe::setApiKey( self::get_secret_key() );
		Stripe\Stripe::setAPIVersion( RWSTRIPE_STRIPE_API_VERSION );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0
	 *
	 * @return RWStripe_Stripe
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new RWStripe_Stripe();
		}
		return self::$instance;
    }

	/**
	 * Get the type of Stripe credential the site is using.
	 *
	 * Stripe Connect and the site's own restricted key are mutually exclusive.
	 *
	 * @since TBD
	 *
	 * @return string 'connect', 'restricted_key', or an empty string if no credential is saved.
	 */
	public static function get_credential_type() {
		if ( '' !== (string) get_option( 'rwstripe_stripe_access_token', '' ) ) {
			return 'connect';
		}
		if ( '' !== (string) get_option( 'rwstripe_restricted_key', '' ) ) {
			return 'restricted_key';
		}
		return '';
	}

	/**
	 * Check whether the site is using its own restricted (or secret) key instead of Stripe Connect.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public static function is_using_restricted_key() {
		return 'restricted_key' === self::get_credential_type();
	}

	/**
	 * Get the secret credential used for all Stripe API requests.
	 *
	 * @since TBD
	 *
	 * @return string The Stripe Connect access token or the site's own key.
	 */
	public static function get_secret_key() {
		switch ( self::get_credential_type() ) {
			case 'connect':
				return (string) get_option( 'rwstripe_stripe_access_token', '' );
			case 'restricted_key':
				return (string) get_option( 'rwstripe_restricted_key', '' );
		}
		return '';
	}

	/**
	 * Get the Stripe environment for the active credential.
	 *
	 * @since TBD
	 *
	 * @return string 'live', 'test', or an empty string if no credential is saved.
	 */
	public static function get_environment() {
		switch ( self::get_credential_type() ) {
			case 'connect':
				return 'live' === get_option( 'rwstripe_stripe_environment' ) ? 'live' : 'test';
			case 'restricted_key':
				return self::get_key_environment( self::get_secret_key() );
		}
		return '';
	}

	/**
	 * Get the Stripe account ID for the active credential.
	 *
	 * Empty when using a restricted key that cannot read the account.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_account_id() {
		switch ( self::get_credential_type() ) {
			case 'connect':
				return (string) get_option( 'rwstripe_stripe_account_id', '' );
			case 'restricted_key':
				return (string) get_option( 'rwstripe_restricted_key_account_id', '' );
		}
		return '';
	}

	/**
	 * Infer the environment from a Stripe key prefix.
	 *
	 * @since TBD
	 *
	 * @param string $key Stripe key.
	 * @return string 'live' or 'test'.
	 */
	public static function get_key_environment( $key ) {
		return (bool) preg_match( '/^(rk|sk)_live_/', (string) $key ) ? 'live' : 'test';
	}

	/**
	 * Describe a Stripe key without revealing it.
	 *
	 * @since TBD
	 *
	 * @param string $key Stripe key.
	 * @return string For example "rk_test_…abcd".
	 */
	public static function mask_key( $key ) {
		$key = (string) $key;
		if ( ! preg_match( '/^((?:rk|sk)_(?:live|test)_)/', $key, $matches ) ) {
			return '…' . substr( $key, -4 );
		}
		return $matches[1] . '…' . substr( $key, -4 );
	}

	/**
	 * Save the site's own Stripe key, replacing any previous key.
	 *
	 * Runs the permission check right after saving.
	 *
	 * @since TBD
	 *
	 * @param string $key Restricted key (rk_live_/rk_test_) or secret key (sk_live_/sk_test_).
	 * @return array|WP_Error The permission report, or an error if the key was not saved.
	 */
	public static function save_restricted_key( $key ) {
		$key = trim( (string) $key );

		if ( 'connect' === self::get_credential_type() ) {
			return new WP_Error( 'rwstripe_connect_active', __( 'This site is connected through Stripe Connect. Disconnect from Stripe before saving your own restricted key.', 'restrict-with-stripe' ), array( 'status' => 409 ) );
		}
		if ( 0 === strpos( $key, 'pk_' ) ) {
			return new WP_Error( 'rwstripe_invalid_key', __( 'That is a publishable key. Enter a restricted key that starts with rk_live_ or rk_test_.', 'restrict-with-stripe' ), array( 'status' => 400 ) );
		}
		if ( ! preg_match( '/^(rk|sk)_(live|test)_[A-Za-z0-9_]+$/', $key ) ) {
			return new WP_Error( 'rwstripe_invalid_key', __( 'Enter a restricted key that starts with rk_live_ or rk_test_.', 'restrict-with-stripe' ), array( 'status' => 400 ) );
		}

		// Look up the account ID once so customer and restriction data stay tied to the Stripe account.
		$account_id = '';
		if ( self::load_library() ) {
			try {
				$account    = Stripe\Account::retrieve( null, array( 'api_key' => $key ) );
				$account_id = sanitize_text_field( $account->id );
			} catch ( Exception $e ) {
				$account_id = '';
			}
		}

		update_option( 'rwstripe_restricted_key', $key, false );
		update_option( 'rwstripe_restricted_key_account_id', $account_id, false );
		delete_option( 'rwstripe_restricted_key_error' );
		delete_option( 'rwstripe_restricted_key_permissions' );

		// Requests later in this page load use the new key.
		if ( class_exists( 'Stripe\Stripe' ) ) {
			Stripe\Stripe::setApiKey( $key );
		}

		return self::check_key_permissions();
	}

	/**
	 * Remove the site's own Stripe key and its saved status.
	 *
	 * @since TBD
	 */
	public static function clear_restricted_key() {
		delete_option( 'rwstripe_restricted_key' );
		delete_option( 'rwstripe_restricted_key_account_id' );
		delete_option( 'rwstripe_restricted_key_error' );
		delete_option( 'rwstripe_restricted_key_permissions' );
	}

	/**
	 * Get the permissions a restricted key needs, derived from every Stripe call this plugin makes.
	 *
	 * Resource IDs match Stripe's permission names; labels match the Dashboard's restricted key editor.
	 *
	 * @since TBD
	 *
	 * @return array[]
	 */
	public static function get_required_permissions() {
		return array(
			array(
				'resource' => 'product',
				'label'    => __( 'Products', 'restrict-with-stripe' ),
				'access'   => 'read',
				'used_for' => __( 'List products to restrict content and build the paywall.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Product',
			),
			array(
				'resource' => 'plan',
				'label'    => __( 'Prices', 'restrict-with-stripe' ),
				'access'   => 'read',
				'used_for' => __( 'Show prices and look up the price being bought.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Price',
			),
			array(
				'resource' => 'customer',
				'label'    => __( 'Customers', 'restrict-with-stripe' ),
				'access'   => 'write',
				'used_for' => __( 'Create a Stripe customer for each user and keep their email in sync.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Customer',
			),
			array(
				'resource' => 'subscription',
				'label'    => __( 'Subscriptions', 'restrict-with-stripe' ),
				'access'   => 'read',
				'used_for' => __( 'Check active or trialing subscriptions.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Subscription',
			),
			array(
				'resource' => 'invoice',
				'label'    => __( 'Invoices', 'restrict-with-stripe' ),
				'access'   => 'read',
				'used_for' => __( 'Check paid one-time purchases.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Invoice',
			),
			array(
				'resource' => 'checkout_session',
				'label'    => __( 'Checkout Sessions', 'restrict-with-stripe' ),
				'access'   => 'write',
				'used_for' => __( 'Create Stripe Checkout sessions.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Checkout\Session',
			),
			array(
				'resource' => 'customer_portal',
				'label'    => __( 'Customer portal', 'restrict-with-stripe' ),
				'access'   => 'write',
				'used_for' => __( 'Create the portal configuration and customer portal sessions.', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\BillingPortal\Configuration',
			),
			array(
				'resource' => 'connected_account',
				'label'    => __( 'Accounts', 'restrict-with-stripe' ),
				'access'   => 'read',
				'optional' => true,
				'used_for' => __( 'Optional: read the account ID when the key is saved, so restrictions and customers stay tied to your Stripe account (and carry over from Stripe Connect).', 'restrict-with-stripe' ),
				'probe'    => 'Stripe\Account',
			),
		);
	}

	/**
	 * Check which permissions the saved key has, using read-only list requests (limit=1).
	 *
	 * Never creates or changes anything in Stripe. Write permissions can only be confirmed
	 * by the first real request, so they are reported as unverified once read access passes.
	 * The report is saved so the settings page can show it.
	 *
	 * @since TBD
	 *
	 * @return array|WP_Error The permission report, or an error if no key is saved.
	 */
	public static function check_key_permissions() {
		if ( ! self::is_using_restricted_key() ) {
			return new WP_Error( 'rwstripe_no_key', __( 'No restricted key is saved.', 'restrict-with-stripe' ), array( 'status' => 400 ) );
		}
		if ( ! self::load_library() ) {
			return new WP_Error( 'rwstripe_no_library', __( 'The Stripe library could not be loaded. Make sure the curl, mbstring, and json PHP extensions are enabled.', 'restrict-with-stripe' ), array( 'status' => 500 ) );
		}

		$key     = self::get_secret_key();
		$opts    = array( 'api_key' => $key );
		$results = array();
		$invalid = '';

		// Use short timeouts so an unreachable Stripe does not stall the settings page.
		$http_client      = class_exists( 'Stripe\HttpClient\CurlClient' ) ? Stripe\HttpClient\CurlClient::instance() : null;
		$default_timeouts = null;
		if ( $http_client && method_exists( $http_client, 'getTimeout' ) && method_exists( $http_client, 'setTimeout' ) && method_exists( $http_client, 'getConnectTimeout' ) && method_exists( $http_client, 'setConnectTimeout' ) ) {
			$default_timeouts = array( $http_client->getTimeout(), $http_client->getConnectTimeout() );
			$http_client->setTimeout( 10 );
			$http_client->setConnectTimeout( 5 );
		}

		foreach ( self::get_required_permissions() as $permission ) {
			$result = array(
				'resource' => $permission['resource'],
				'label'    => $permission['label'],
				'access'   => $permission['access'],
				'optional' => ! empty( $permission['optional'] ),
				'used_for' => $permission['used_for'],
			);
			try {
				if ( 'Stripe\Account' === $permission['probe'] ) {
					Stripe\Account::retrieve( null, $opts );
				} else {
					call_user_func( array( $permission['probe'], 'all' ), array( 'limit' => 1 ), $opts );
				}
				if ( 'write' === $permission['access'] ) {
					$result['status']  = 'unverified';
					$result['message'] = __( 'Read access confirmed. Write access is verified on first use.', 'restrict-with-stripe' );
				} else {
					$result['status']  = 'ok';
					$result['message'] = __( 'Granted.', 'restrict-with-stripe' );
				}
			} catch ( Exception $e ) {
				$status = self::get_exception_http_status( $e );
				if ( 401 === $status ) {
					$invalid = $e->getMessage();
					break;
				} elseif ( 403 === $status ) {
					$result['status']  = 'missing';
					$result['message'] = $e->getMessage();
				} else {
					$result['status']  = 'error';
					$result['message'] = $e->getMessage() ? $e->getMessage() : __( 'Stripe could not be reached to check this permission.', 'restrict-with-stripe' );
				}
			}
			$results[] = $result;
		}

		if ( $default_timeouts ) {
			$http_client->setTimeout( $default_timeouts[0] );
			$http_client->setConnectTimeout( $default_timeouts[1] );
		}

		$missing = array();
		foreach ( $results as $result ) {
			if ( empty( $result['optional'] ) && 'missing' === $result['status'] ) {
				$missing[] = $result;
			}
		}

		$report = array(
			'checked_at'  => time(),
			'key'         => self::mask_key( $key ),
			'invalid_key' => '' !== $invalid,
			'message'     => $invalid,
			'ok'          => '' === $invalid && empty( $missing ),
			'results'     => '' === $invalid ? $results : array(),
		);
		update_option( 'rwstripe_restricted_key_permissions', $report, false );

		// Keep the admin health notice in sync with the latest check.
		if ( '' !== $invalid ) {
			self::set_key_error( 'invalid', $invalid );
		} elseif ( ! empty( $missing ) ) {
			self::set_key_error( 'permission', $missing[0]['message'] );
		} else {
			delete_option( 'rwstripe_restricted_key_error' );
		}

		return $report;
	}

	/**
	 * Remember an authentication or permission error from the site's own key for the admin notice.
	 *
	 * @since TBD
	 *
	 * @param Exception $e Exception thrown by the Stripe library.
	 */
	public static function record_api_error( $e ) {
		if ( ! self::is_using_restricted_key() ) {
			return;
		}
		$status = self::get_exception_http_status( $e );
		if ( 401 === $status ) {
			self::set_key_error( 'invalid', $e->getMessage() );
		} elseif ( 403 === $status ) {
			self::set_key_error( 'permission', $e->getMessage() );
		}
	}

	/**
	 * Save the key health error, writing only when it changes.
	 *
	 * @since TBD
	 *
	 * @param string $type    'invalid' or 'permission'.
	 * @param string $message Stripe's error message.
	 */
	private static function set_key_error( $type, $message ) {
		$error    = array(
			'type'    => $type,
			'message' => sanitize_text_field( $message ),
		);
		$existing = get_option( 'rwstripe_restricted_key_error' );
		if ( is_array( $existing ) && $existing['type'] === $error['type'] && $existing['message'] === $error['message'] ) {
			return;
		}
		update_option( 'rwstripe_restricted_key_error', $error, false );
	}

	/**
	 * Get the HTTP status from a Stripe exception, even if another plugin loaded an older Stripe library.
	 *
	 * @since TBD
	 *
	 * @param Exception $e Exception thrown by the Stripe library.
	 * @return int HTTP status, or 0 if there was no response.
	 */
	private static function get_exception_http_status( $e ) {
		return method_exists( $e, 'getHttpStatus' ) ? (int) $e->getHttpStatus() : 0;
	}

	/**
	 * Load the Stripe library and set the active credential.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Stripe library is available.
	 */
	private static function load_library() {
		self::get_instance();
		return class_exists( 'Stripe\Stripe' );
	}

	/**
	 * Get the Stripe account object.
	 *
	 * Includes branding settings.
	 *
	 * @since 1.0
	 *
	 * @return Stripe\Account|string The Stripe account object or an error message.
	 */
	public function get_account() {
		static $account;
		if ( ! isset( $account ) ) {
			try {
				$account = Stripe\Account::retrieve();
			} catch ( Exception $e ) {
				self::record_api_error( $e );
				$account = $e->getMessage();
			}
		}
		return $account;
	}

	/**
	 * Get a price from Stripe.
	 *
	 * @since 1.0
	 *
	 * @param string $price_id to get.
	 * @return Stripe\Price|string error message or Stripe\Price object.
	 */
	public function get_price( $price_id ) {
		static $prices = array();
		if ( ! isset( $prices[ $price_id ] ) ) {
			try {
				$prices[ $price_id ] = Stripe\Price::retrieve( $price_id );
			} catch ( Exception $e ) {
				self::record_api_error( $e );
				$prices[ $price_id ] = $e->getMessage();
			}
		}
		return $prices[ $price_id ];
	}

	/**
	 * Get all products from Stripe.
	 *
	 * @since 1.0
	 *
	 * @return Stripe\Product[]|string Array of Stripe\Product objects or error message.
	 */
	public function get_all_products() {
		static $products = null;
		if ( $products === null ) {
			try {
				$products = Stripe\Product::all( array( 
					'limit' => 100,
					'active' => true,
				) );
			} catch ( Exception $e ) {
				self::record_api_error( $e );
				$products = $e->getMessage();
			}
		}
		return $products;
	}

	/**
	 * Get a product from Stripe.
	 *
	 * @since 1.0
	 *
	 * @param string $product_id to get.
	 * @return Stripe\Product|string error message or Stripe\Product object.
	 */
	public function get_product( $product_id ) {
		$all_products = $this->get_all_products();
		if ( is_string( $all_products ) ) {
			return 'Could not get products. ' . $all_products;
		}
		foreach ( $all_products->data as $product ) {
			if ( $product->id == $product_id ) {
				return $product;
			}
		}
		return 'Could not find product ' . $product_id;
	}

	/**
	 * Get the default price for a given product in Stripe. 
	 *
	 * @since 1.0
	 *
	 * @param string $product_id to get prices for.
	 * @return Stripe\Price|null|string The default price for the product, null if no default price exists, or error message.
	 */
	public function get_default_price_for_product( $product_id ) {
		$all_products = $this->get_all_products();
		if ( is_string( $all_products ) ) {
			return 'Could not get products. ' . $all_products;
		}
		foreach ( $all_products as $product ) {
			if ( $product->id == $product_id ) {
				$price_id = $product->default_price;
				if ( ! empty( $price_id ) ) {
					return $this->get_price( $price_id );
				}
			}
		}
		return null;
	}

	/**
	 * Get all prices in Stripe.
	 *
	 * TODO: Maybe trash this method.
	 *
	 * @since 1.0
	 *
	 * @return Stripe\Price[]|string Array of Stripe\Price objects or error message.
	 */
	private function get_all_prices() {
		static $prices = null;
		if ( $prices === null ) {
			try {
				$prices = Stripe\Price::all( array( 'limit' => 100000 ) );
			} catch ( Exception $e ) {
				self::record_api_error( $e );
				$prices = $e->getMessage();
			}
		}
		return $prices;
	}

	/**
	 * Create a new customer in Stripe with a given email address.
	 *
	 * @since 1.0
	 *
	 * @param string $email to create customer with.
	 * @return Stripe\Customer|string Stripe\Customer object or error message.
	 */
	public function create_customer_with_email( $email ) {
		try {
			$customer = Stripe\Customer::create( array( 'email' => $email ) );
		} catch ( Exception $e ) {
			self::record_api_error( $e );
			$customer = $e->getMessage();
		}
		return $customer;
	}

	/**
	 * Update the email for a given customer in Stripe.
	 *
	 * @since 1.0
	 *
	 * @param string $customer_id to update.
	 * @param string $email to update with.
	 */
	public function update_customer_email( $customer_id, $email ) {
		try {
			$customer = Stripe\Customer::retrieve( $customer_id );
			$customer->email = $email;
			$customer->save();
		} catch ( Exception $e ) {
			self::record_api_error( $e );
		}
	}

	/**
	 * Get a Customer Portal URL for a given customer.
	 *
	 * @since 1.0
	 *
	 * @param string $customer_id to get URL for.
	 * @return Stripe\BillingPortal\Session|string Stripe\BillingPortal\Session object or error message.
	 */
	public function get_customer_portal_session( $customer_id ) {
		// Before we can send the user to the customer portal,
		// we need to have a portal configuration.
		$portal_configurations = array();
		try {
			// Get all active portal configurations.
			$portal_configurations = Stripe\BillingPortal\Configuration::all( array( 'active' => true, 'limit' => 100 ) );
		} catch( Exception $e ) {
			// Error getting portal configurations.
			self::record_api_error( $e );
			return $e->getMessage();
		}

		// Check if one of the portal configurations is default.
		foreach ( $portal_configurations as $portal_configuration ) {
			if ( $portal_configuration->is_default ) {
				$portal_configuration_id = $portal_configuration->id;
				break;
			}
		}

		// If we still don't have a portal configuration, create one.
		if ( empty( $portal_configuration_id ) ) {
			$portal_configuration_params = array(
				'business_profile' => array(
					'headline' => esc_html__( 'Manage Your Subscriptions', 'restrict-with-stripe' ),
				),
				'features' => array(
					'customer_update' => array( 'enabled' => true, 'allowed_updates' => array( 'address', 'phone', 'tax_id' ) ),
					'invoice_history' => array( 'enabled' => true ),
					'payment_method_update' => array( 'enabled' => true ),
					'subscription_cancel' => array( 'enabled' => true ),
					'subscription_pause' => array( 'enabled' => true ),
				),
			);
			try {
				$portal_configuration = Stripe\BillingPortal\Configuration::create( $portal_configuration_params );
			} catch( Exception $e ) {
				// Error creating portal configuration.
				self::record_api_error( $e );
				return $e->getMessage();
			}

			if ( ! empty( $portal_configuration ) ) {
				$portal_configuration_id = $portal_configuration->id;
			}
		}

		// Get the customer portal URL.
		try {
			$session = \Stripe\BillingPortal\Session::create([
				'customer' => $customer_id,
				'return_url' => get_site_url(),
				'configuration' => $portal_configuration_id,
			]);
			return $session;
		} catch ( Exception $e ) {
			self::record_api_error( $e );
			return $e->getMessage();
		}
	}

	/**
	 * Get all subscriptions for a given customer.
	 *
	 * @since 1.0
	 *
	 * @param string $customer_id to get subscriptions for.
	 * @return Stripe\Subscription[]|string Array of Stripe\Subscription objects or error message.
	 */
	public function get_subscriptions_for_customer( $customer_id ) {
		static $cached_subscriptions = array();
		if ( isset( $cached_subscriptions[ $customer_id ] ) ) {
			return $cached_subscriptions[ $customer_id ];
		}

		try {
			$subscriptions = Stripe\Subscription::all( array( 'customer' => $customer_id ) );
		} catch ( Exception $e ) {
			self::record_api_error( $e );
			$subscriptions = $e->getMessage();
		}

		$cached_subscriptions[ $customer_id ] = $subscriptions;
		return $subscriptions;
	}

	/**
	 * Check if a customer has an active subscription for a given product or has
	 * purchased it as a one-time payment.
	 *
	 * @since 1.0
	 *
	 * @param string $customer_id to check.
	 * @param string[] $product_ids to check.
	 * @return bool
	 */
	public function customer_has_product( $customer_id, $product_ids ) {
		// Make sure that $product_ids is an array.
		if ( ! is_array( $product_ids ) ) {
			$product_ids = array( $product_ids );
		}

		// Set up a variable to track errors if we get any.
		$error = '';

		// Check subscriptions for access.
		$subscriptions = $this->get_subscriptions_for_customer( $customer_id );
		if ( is_string( $subscriptions ) ) {
			$error .= __( 'Error getting subscriptions.', 'restrict-with-stripe' ) . ' ' . $subscriptions;
		} else {
			// Check if the customer has an active subscription for any of the products.
			foreach( $subscriptions as $subscription ) {
				// Make sure that the subscription is active or trialing.
				if ( $subscription->status !== 'active' && $subscription->status !== 'trialing' ) {
					continue;
				}

				// Check if the subscription has any of the product IDs.
				foreach ( $subscription->items as $item ) {
					if ( in_array( $item->price->product, $product_ids ) ) {
						return true;
					}
				}
			}
		}

		// Check one-time payment invoices for access.
		try {
			$invoices = Stripe\Invoice::all( array( 'customer' => $customer_id, 'status' => 'paid' ) );
			foreach ( $invoices as $invoice ) {
				// If the invoice is part of a subscription, ignore it.
				if ( ! empty( $invoice->subscription ) ) {
					continue;
				}

				// Check if we are ignoring this invoice.
				if ( ! empty( $invoice->metadata ) && ! empty( $invoice->metadata['rwstripe-ignore'] ) ) {
					continue;
				}

				// Check if the invoice has any of the product IDs.
				foreach ( $invoice->lines as $line ) {
					if ( in_array( $line->price->product, $product_ids ) ) {
						return true;
					}
				}
			}
		} catch ( Exception $e ) {
			self::record_api_error( $e );
			if ( ! empty( $error ) ) {
				$error .= ' ';
			}
			$error .= __( 'Error getting invoices.', 'restrict-with-stripe' ) . ' ' . $e->getMessage();
		}

		return empty( $error ) ? false : $error;
	}

	/**
	 * Create a checkout session.
	 *
	 * @since 1.0
	 *
	 * @param string $price_id to create session for.
	 * @param string $customer_id to create session for.
	 * @param string $redirect_url to redirect to after checkout.
	 * @return Stripe\Checkout\Session|string Stripe\Checkout\Session object or error message.
	 */
	public function create_checkout_session( $price_id, $customer_id, $redirect_url ) {
		$price = $this->get_price( $price_id );
		if ( is_string( $price ) ) {
			return 'Could not get price.' . $price;
		}

		// Set up line items.
		$line_items = array(
			array(
				'price' => $price_id,
				'quantity' => 1,
			),
		);

		// Remove anchors and query strings from the redirect URL.
		$cleaned_redirect_url = strtok( $redirect_url, '?' );
		$cleaned_redirect_url = strtok( $cleaned_redirect_url, '#' );

		// Set up checkout session params.
		$checkout_session_params = array(
			'customer' => $customer_id,
			'line_items' => $line_items,
			'success_url' => $cleaned_redirect_url,
			'cancel_url' => $cleaned_redirect_url,
		);

		// Application fees only apply to Stripe Connect. Sites using their own key are charged no fee.
		$add_application_fee = ! self::is_using_restricted_key();

		if ( $price['type'] === 'recurring' ) {
			// If price is recurring, set up subscription params.
			$checkout_session_params['mode'] = 'subscription';
			if ( $add_application_fee ) {
				$checkout_session_params['subscription_data'] = array(
					'application_fee_percent' => 2,
				);
			}
			$checkout_session_params['payment_method_collection'] = 'if_required';
		} else {
			// If price is one-time, set up payment params.
			$checkout_session_params['mode'] = 'payment';
			if ( $add_application_fee ) {
				$checkout_session_params['payment_intent_data'] = array(
					'application_fee_amount' => floor( $price['unit_amount'] * 0.02 ),
				);
			}
			$checkout_session_params['invoice_creation']['enabled'] = true;
		}

		/**
		 * Filters the checkout session params.
		 *
		 * @since TBD
		 *
		 * @param array $checkout_session_params Checkout session params.
		 * @param Stripe\Price $price Price object.
		 * @param string $customer_id Customer ID.
		 * @param string $redirect_url Redirect URL.
		 * @return array
		 */
		$checkout_session_params = apply_filters( 'rwstripe_checkout_session_params', $checkout_session_params, $price, $customer_id, $redirect_url );

		try {
			return \Stripe\Checkout\Session::create( $checkout_session_params );
		} catch ( Exception $e ) {
			self::record_api_error( $e );
			return $e->getMessage();
		}
	}
}
