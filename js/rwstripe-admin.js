jQuery( document ).ready( function () {
    // When the user clicks the button to edit the Stripe Customer ID on the user profile page,
    // show the input element and hide the code element.
	jQuery( '#rwstripe_edit_customer_id' ).click( function ( e ) {
        // Hide this element (the edit link).
        jQuery( this ).hide();

        // Hide the sibling code element.
        jQuery( this ).siblings( 'code' ).hide();

        // Show the sibling input element.
        jQuery( this ).siblings( 'input' ).show();
    } );
} );
