jQuery(document).ready(function($) {

	// wc_add_to_cart_params is required to continue, ensure the object exists
	if ( typeof wc_add_to_cart_params === 'undefined' || typeof woocommerce_params === 'undefined' ) {
		return false;
	}

	// Ajax add to quote
	$(document).on( 'click', '.add_to_quote_button', function() {

		// AJAX add to quote request
		var $thisbutton = $(this);

		if ($thisbutton.is('.product_type_simple, .product_type_downloadable, .product_type_virtual')) {

			if (!$thisbutton.attr('data-product_id')) return true;

			$thisbutton.removeClass('added');
			$thisbutton.addClass('loading');

			var data = {
				action: 		'woocommerce_add_to_quote',
				product_id: 	$thisbutton.attr('data-product_id'),
				quantity:       $thisbutton.attr('data-quantity')
			};

			// Trigger event
			$('body').trigger( 'adding_to_quote', [ $thisbutton, data ] );

			// Ajax action
			$.post( wc_add_to_cart_params.ajax_url, data, function( response ) {

				if ( ! response )
					return;

				var this_page = window.location.toString();

				this_page = this_page.replace( 'add-to-quote', 'added-to-quote' );

				if ( response.error && response.product_url ) {
					window.location = response.product_url;
					return;
				}

				// Redirect to quote option
				if ( wc_add_to_cart_params.cart_redirect_after_add == 'yes' ) {

					window.location = woocommerce_params.quote_url;
					return;

				} else {

					$thisbutton.removeClass('loading');

					fragments = response.fragments;
					quote_hash = response.quote_hash;

					// Block fragments class
					if ( fragments ) {
						$.each(fragments, function(key, value) {
							$(key).addClass('updating');
						});
					}

					// Block widgets and fragments
					$('.shop_table.quote, .updating, .quote_totals').fadeTo('400', '0.6').block({message: null, overlayCSS: {background: 'transparent url(' + wc_add_to_cart_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6 } } );

					// Changes button classes
					$thisbutton.addClass('added');

					// View quote text
					if ( $thisbutton.parent().find('.added_to_quote').size() == 0 )
						$thisbutton.after( ' <a href="' + woocommerce_params.quote_url + '" class="added_to_quote" title="' + woocommerce_params.i18n_view_quote + '">' + woocommerce_params.i18n_view_quote + '</a>' );

					// Replace fragments
					if ( fragments ) {
						$.each(fragments, function(key, value) {
							$(key).replaceWith(value);
						});
					}

					// Unblock
					$('.widget_shopping_quote, .updating').stop(true).css('opacity', '1').unblock();

					// quote page elements
					$('.shop_table.quote').load( this_page + ' .shop_table.quote:eq(0) > *', function() {

						$("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").addClass('buttons_added').append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');

						$('.shop_table.quote').stop(true).css('opacity', '1').unblock();

						$('body').trigger('quote_page_refreshed');
					});

					$('.quote_totals').load( this_page + ' .quote_totals:eq(0) > *', function() {
						$('.quote_totals').stop(true).css('opacity', '1').unblock();
					});

					// Trigger event so themes can refresh other areas
					$('body').trigger( 'added_to_quote', [ fragments, quote_hash ] );
				}
			});

			return false;

		} else {
			return true;
		}

	});

});