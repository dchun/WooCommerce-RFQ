/* global boopis_rfq_params */
jQuery( function( $ ) {

	// boopis_rfq_params is required to continue, ensure the object exists
	if ( typeof boopis_rfq_params === 'undefined' ) {
		return false;
	}
	var boopis_proposal_form = {
		$proposal_form: $("form#proposal"),
		submit_error: function( error_message ) {
			$( '.woocommerce-error, .woocommerce-message' ).remove();
			boopis_proposal_form.$proposal_form.prepend( error_message );
			$( 'html, body' ).animate({
				scrollTop: ( $( 'form#proposal' ).offset().top - 100 )
			}, 1000 );
			$( document.body ).trigger( 'rfq_error' );
		}
	};

	$("form#proposal :submit").click(function(e) {
		var formData = $(this).closest('form').serializeArray();
		formData.push({ name: 'decision', value: this.name });

		$.ajax({
			type:		'POST',
			url:		boopis_rfq_params.ajax_url,
			data:		formData,
			dataType:   'json',
			success:	function( result ) {
				try {
					if ( result.result === 'success' ) {
						if ( -1 === result.redirect.indexOf( 'https://' ) || -1 === result.redirect.indexOf( 'http://' ) ) {
							window.location = result.redirect;
						} else {
							window.location = decodeURI( result.redirect );
						}
					} else if ( result.result === 'failure' ) {
						throw 'Result failure';
					} else {
						throw 'Invalid response';
					}
				} catch( err ) {
					// Reload page
					if ( result.reload === 'true' ) {
						// window.location.reload();
						return;
					}

					// Add new errors
					if ( result.messages ) {
						boopis_proposal_form.submit_error( result.messages );
					} else {
						boopis_proposal_form.submit_error( '<div class="woocommerce-error">' + boopis_rfq_params.i18n_rfq_error + '</div>' );
					}
				}
			},
			error:	function( jqXHR, textStatus, errorThrown ) {
				boopis_proposal_form.submit_error( '<div class="woocommerce-error">' + errorThrown + '</div>' );
			}
		});
		
		return false;

	});
});