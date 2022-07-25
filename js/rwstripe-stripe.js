jQuery(document).ready(function(){
	/**
	 * When the user clicks the button to pay with Stripe,
	 * create a new Stripe Checkout session and redirect the user.
	 */
	jQuery( ".rwstripe-checkout-button" ).click( function (e) {
		// Get the value of the rwstripe-email field next to the pay button.
		var email = jQuery(this).siblings().filter(".rwstripe-email").val();

		// Create a checkout session.
		jQuery.noConflict().ajax({
			url: rwstripeStripe.ajaxUrl,
			dataType: 'json',
			data: {
				action: 'rwstripe_create_checkout_session',
				price_id: e.target.value,
				email: email,
				redirect_url: window.location.href
			},
			success: function(response) {
				// Redirect the user to the Stripe Checkout page.
				if ( response.checkout_session_url ) {
					window.location.replace(response.checkout_session_url);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
			  alert(xhr.status);
			  alert(thrownError);
			}
		});
	});
});