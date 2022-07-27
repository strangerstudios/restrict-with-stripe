jQuery(document).ready(function(){
	/**
	 * When the user clicks the button to go to the Stripe Customer Portal,
	 * get the link for a portal session and redirect the user.
	 */
	jQuery( ".rwstripe-customer-portal-button" ).click( function (e) {
		// Disable the button to prevent multiple clicks.
		jQuery( ".rwstripe-customer-portal-button" ).prop( "disabled", true );

		// Create a portal session.
		jQuery.noConflict().ajax({
			url: rwstripe.restUrl + 'customer_portal_url',
			dataType: 'json',
			data: {
				user_id: e.target.value
			},
			beforeSend: function (xhr) {
				xhr.setRequestHeader('X-WP-Nonce', rwstripe.nonce);
			},
			success: function(response) {
				// Redirect the user to the Stripe Customer Portal.
				window.location.replace( response );
			},
			error: function (xhr, ajaxOptions, thrownError) {
				var err = eval("(" + xhr.responseText + ")");
				alert(err.message);
			}
		});
	});
});