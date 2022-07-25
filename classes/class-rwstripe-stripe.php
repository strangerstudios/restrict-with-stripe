<?php

define( "RWSTRIPE_STRIPE_API_VERSION", "2020-03-02" );

class RWStripe_Stripe {

	// Singlton class.
	private static $instance = null;

	/**
	 * Connect to Stripe.
	 *
	 * @since TBD
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

		Stripe\Stripe::setApiKey( get_option( 'rwstripe_stripe_access_token', '' ) );
		Stripe\Stripe::setAPIVersion( RWSTRIPE_STRIPE_API_VERSION );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since TBD
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
	 * Get a price from Stripe.
	 *
	 * @since TBD
	 *
	 * @param string $price_id to get.
	 * @return Stripe\Price|null
	 */
	public function get_price( $price_id ) {
		static $prices = array();
		if ( ! isset( $prices[ $price_id ] ) ) {
			try {
				$prices[ $price_id ] = Stripe\Price::retrieve( $price_id );
			} catch ( Exception $e ) {
				$prices[ $price_id ] = null;
			}
		}
		return $prices[ $price_id ];
	}

	/**
	 * Get all products from Stripe.
	 *
	 * @since TBD
	 *
	 * @return Stripe\Product[] Array of Stripe\Product objects.
	 */
	public function get_all_products() {
		static $products = null;
		if ( $products === null ) {
			try {
				$products = Stripe\Product::all( array( 'limit' => 100000 ) );
			} catch ( Exception $e ) {
				$products = array();
			}
		}
		return $products;
	}

	/**
	 * Get the default price for a given product in Stripe. 
	 *
	 * @since TBD
	 *
	 * @param string $product_id to get prices for.
	 * @return Stripe\Price|null The default price for the product or null if no default price exists.
	 */
	public function get_default_price_for_product( $product_id ) {
		$all_prices = $this->get_all_prices();
		$prices_for_product = array();
		foreach ( $all_prices as $price ) {
			if ( $price->product === $product_id ) {
				$prices_for_product[] = $price;
			}
		}

		// TODO: Get smarter about which price we choose.
		if ( empty( $prices_for_product ) ) {
			return '';
		} else {
			// Return ID of the first price.
			return $prices_for_product[0];
		}
	}

	/**
	 * Get all prices in Stripe.
	 *
	 * TODO: Maybe trash this method.
	 *
	 * @since TBD
	 *
	 * @return Stripe\Price[] Array of Stripe\Price objects.
	 */
	private function get_all_prices() {
		static $prices = null;
		if ( $prices === null ) {
			try {
				$prices = Stripe\Price::all( array( 'limit' => 100000 ) );
			} catch ( Exception $e ) {
				$prices = array();
			}
		}
		return $prices;
	}

	/**
	 * Get all checkout sessions for a given customer.
	 *
	 * @since TBD
	 *
	 * @param string $customer_id to get checkout sessions for.
	 * @return Stripe\Checkout\Session[] Array of Stripe\Checkout\Session objects.
	 */
	private function get_checkout_sessions_for_customer( $customer_id ) {
		try {
			$checkout_sessions = Stripe\Checkout\Session::all( array( 'customer' => $customer_id, 'limit' => 10000, 'expand' => array( 'data.line_items', 'data.payment_intent' ) ) );
		} catch ( Exception $e ) {
			$checkout_sessions = array();
		}
		return $checkout_sessions;
	}

	/**
	 * Create a new customer in Stripe with a given email address.
	 *
	 * @since TBD
	 *
	 * @param string $email to create customer with.
	 * @return Stripe\Customer|null
	 */
	public function create_customer_with_email( $email ) {
		try {
			$customer = Stripe\Customer::create( array( 'email' => $email ) );
		} catch ( Exception $e ) {
			$customer = null;
		}
		return $customer;
	}

	/**
	 * Get a Customer Portal URL for a given customer.
	 *
	 * @since TBD
	 *
	 * @param string $customer_id to get URL for.
	 * @return string|null
	 */
	public function get_customer_portal_url( $customer_id ) {
		try {
			$session = \Stripe\BillingPortal\Session::create([
				'customer' => $customer_id,
				'return_url' => get_site_url(),
			]);
			return $session->url;
		} catch ( Exception $e ) {
			return '';
		}
	}

	/**
	 * Get an active subscription for a given customer and product
	 * if one exists.
	 *
	 * @since TBD
	 *
	 * @param string $customer_id to get subscription for.
	 * @param string $product_id to get subscription for.
	 * @return Stripe\Subscription|null
	 */
	private function get_active_customer_subscription_for_product( $customer_id, $product_id ) {
		try {
			$params = array(
				'customer' => $customer_id,
				'status'   => 'active',
			);
			$subscriptions = Stripe\Subscription::all( $params );
			foreach( $subscriptions as $subscription ) {
				foreach ( $subscription->items as $item ) {
					if ( $item->price->product === $product_id ) {
						return $subscription;
					}
				}
			}
		} catch ( Exception $e ) {
			return null;
		}
		return null;
	}

	/**
	 * Check if a customer has an active subscription for a given product or has
	 * purchased it as a one-time payment.
	 *
	 * @since TBD
	 *
	 * @param string $customer_id to check.
	 * @param string $product_id to check.
	 * @return bool
	 */
	public function customer_has_product( $customer_id, $product_id ) {
		// Check if user has subscription.
		$subscription = $this->get_active_customer_subscription_for_product( $customer_id, $product_id );
		if ( ! empty( $subscription ) ) {
			return true;
		}

		// Check if user has purchased with a one-time payment.
		$checkout_sessions = $this->get_checkout_sessions_for_customer( $customer_id );
		foreach ( $checkout_sessions as $checkout_session ) {
			// Verify that the checkout was successful.
			if ( $checkout_session->payment_status !== 'paid' ) {
				continue;
			}

			// Check whether a one-time payment was made for this product.
			foreach ( $checkout_session->line_items as $line_item ) {
				if ( empty( $line_item->price->recurring ) && $line_item->price->product === $product_id ) {
					// Make sure the charge was not refunded.
					foreach ( $checkout_session->payment_intent->charges->data as $charge ) {
						if ( $charge->refunded ) {
							continue 2; // Move to checking the next line item.
						}
					}
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Create a checkout session.
	 *
	 * @since TBD
	 *
	 * @param string $price_id to create session for.
	 * @param string $customer_id to create session for.
	 * @param string $redirect_url to redirect to after checkout.
	 * @return Stripe\Checkout\Session|null
	 */
	public function create_checkout_session( $price_id, $customer_id, $redirect_url ) {
		$price = $this->get_price( $price_id );
		if ( empty( $price ) || empty( $customer_id ) || empty( $redirect_url ) ) {
			return;
		}
		
		$checkout_session_params = array(
			'customer' => $customer_id,
			'line_items' => [[
			  'price' => $price_id,
			  'quantity' => 1,
			]],
			'mode' => $price['type'] == 'recurring' ? 'subscription' : 'payment', // Get from price.
			'success_url' => $redirect_url,
			'cancel_url' => $redirect_url,
		);

		if ( $price['type'] == 'recurring' ) {
			$checkout_session_params['subscription_data'] = array(
				'application_fee_percent' => 2 // Get paid for subscriptions.
			);
		} else {
			$checkout_session_params['payment_intent_data'] = array(
				'application_fee_amount' => 0.02 * $price['amount'] // Get paid for orders.
			);
		}

		try {
			return \Stripe\Checkout\Session::create( $checkout_session_params );
		} catch ( Exception $e ) {
			return null;
		}
	}
}