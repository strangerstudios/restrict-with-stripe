jQuery( document ).ready( function () {
	/**
	 * When the user clicks the button to pay with Stripe,
	 * create a new Stripe Checkout session and redirect the user.
	 */
	jQuery( '.rwstripe-checkout-button' ).click( function ( e ) {
		// Disable the button to prevent multiple clicks.
		jQuery( '.rwstripe-checkout-button' ).prop( 'disabled', true );

		// Get the value of the rwstripe-email field next to the pay button.
		var email = jQuery( this ).siblings().filter( '.rwstripe-email' ).val();

		// Create a checkout session.
		jQuery.noConflict().ajax( {
			url: rwstripe.restUrl + 'checkout',
			dataType: 'json',
			data: {
				price_id: e.target.value,
				email: email,
				redirect_url: window.location.href,
			},
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rwstripe.nonce );
			},
			success: function ( response ) {
				// Redirect the user to the Stripe Checkout page.
				window.location.replace( response );
			},
			error: function ( xhr, ajaxOptions, thrownError ) {
				// Show the error message.
				var err = eval( '(' + xhr.responseText + ')' );
				jQuery( '.rwstripe-checkout-error-message' ).html(
					err.message
				);

				// Disable the button so that the user can try again.
				jQuery( '.rwstripe-checkout-button' ).prop( 'disabled', false );
			},
		} );
	} );

	/**
	 * When the user clicks the button to go to the Stripe Customer Portal,
	 * get the link for a portal session and redirect the user.
	 */
	jQuery( '.rwstripe-customer-portal-button' ).click( function ( e ) {
		// Disable the button to prevent multiple clicks.
		jQuery( '.rwstripe-customer-portal-button' ).prop( 'disabled', true );

		// Create a portal session.
		jQuery.noConflict().ajax( {
			url: rwstripe.restUrl + 'customer_portal_url',
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rwstripe.nonce );
			},
			success: function ( response ) {
				// Redirect the user to the Stripe Customer Portal.
				window.location.replace( response );
			},
			error: function ( xhr, ajaxOptions, thrownError ) {
				var err = eval( '(' + xhr.responseText + ')' );
				alert( err.message );
			},
		} );
	} );
} );
