/* global boopis_rfq_params */
jQuery( function( $ ) {

	// boopis_rfq_params is required to continue, ensure the object exists
	if ( typeof boopis_rfq_params === 'undefined' ) {
		return false;
	}

	$.blockUI.defaults.overlayCSS.cursor = 'default';

	var boopis_rfq_form = {
		updateTimer: false,
		dirtyInput: false,
		xhr: false,
		$rfq_form: $( 'form.rfq' ),
		init: function() {
			$( document.body ).bind( 'update_rfq', this.update_rfq );
			$( document.body ).bind( 'init_rfq', this.init_rfq );

			// Form submission
			this.$rfq_form.on( 'submit', this.submit );

			// Inline validation
			this.$rfq_form.on( 'blur change', '.input-text, select', this.validate_field );

			// Manual trigger
			this.$rfq_form.on( 'update', this.trigger_update_rfq );

			// Inputs/selects which update totals
			this.$rfq_form.on( 'change', '.address-field select', this.input_changed );

			// Update on page load
			$( document.body ).trigger( 'init_rfq' );
			$( 'input#createaccount' ).change( this.toggle_create_account ).change();
		},
		toggle_create_account: function() {
			$( 'div.create-account' ).hide();

			if ( $( this ).is( ':checked' ) ) {
				$( 'div.create-account' ).slideDown();
			}
		},
		init_rfq: function() {
			$( '#billing_country, .country_to_state' ).change();
			$( document.body ).trigger( 'update_rfq' );
		},
		maybe_input_changed: function( e ) {
			if ( boopis_rfq_form.dirtyInput ) {
				boopis_rfq_form.input_changed( e );
			}
		},
		input_changed: function( e ) {
			boopis_rfq_form.dirtyInput = e.target;
			boopis_rfq_form.maybe_update_rfq();
		},
		queue_update_rfq: function( e ) {
			var code = e.keyCode || e.which || 0;

			if ( code === 9 ) {
				return true;
			}

			boopis_rfq_form.dirtyInput = this;
			boopis_rfq_form.reset_update_rfq_timer();
			boopis_rfq_form.updateTimer = setTimeout( boopis_rfq_form.maybe_update_rfq, '1000' );
		},
		trigger_update_rfq: function() {
			boopis_rfq_form.reset_update_rfq_timer();
			boopis_rfq_form.dirtyInput = false;
			$( document.body ).trigger( 'update_rfq' );
		},
		maybe_update_rfq: function() {
			var update_totals = true;

			if ( $( boopis_rfq_form.dirtyInput ).size() ) {
				var $required_inputs = $( boopis_rfq_form.dirtyInput ).closest( 'div' ).find( '.address-field.validate-required' );

				if ( $required_inputs.size() ) {
					$required_inputs.each( function() {
						if ( $( this ).find( 'input.input-text' ).val() === '' ) {
							update_totals = false;
						}
					});
				}
			}
			if ( update_totals ) {
				boopis_rfq_form.trigger_update_rfq();
			}
		},
		reset_update_rfq_timer: function() {
			clearTimeout( boopis_rfq_form.updateTimer );
		},
		validate_field: function() {
			var $this     = $( this ),
				$parent   = $this.closest( '.form-row' ),
				validated = true;

			if ( $parent.is( '.validate-required' ) ) {
				if ( 'checkbox' === $this.attr( 'type' ) && ! $this.is( ':checked' ) ) {
					$parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
					validated = false;
				} else if ( $this.val() === '' ) {
					$parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-required-field' );
					validated = false;
				}
			}

			if ( $parent.is( '.validate-email' ) ) {
				if ( $this.val() ) {

					/* http://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
					var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

					if ( ! pattern.test( $this.val()  ) ) {
						$parent.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid woocommerce-invalid-email' );
						validated = false;
					}
				}
			}

			if ( validated ) {
				$parent.removeClass( 'woocommerce-invalid woocommerce-invalid-required-field' ).addClass( 'woocommerce-validated' );
			}
		},
		update_rfq: function( event, args ) {
			// Small timeout to prevent multiple requests when several fields update at the same time
			boopis_rfq_form.reset_update_rfq_timer();
			boopis_rfq_form.updateTimer = setTimeout( boopis_rfq_form.update_rfq_action, '5', args );
		},
		update_rfq_action: function( args ) {
			if ( boopis_rfq_form.xhr ) {
				boopis_rfq_form.xhr.abort();
			}

			if ( $( 'form.rfq' ).size() === 0 ) {
				return;
			}

			var country			 = $( '#billing_country' ).val(),
				state			 = $( '#billing_state' ).val(),
				postcode		 = $( 'input#billing_postcode' ).val(),
				city			 = $( '#billing_city' ).val(),
				address			 = $( 'input#billing_address_1' ).val(),
				address_2		 = $( 'input#billing_address_2' ).val();

			var data = {
				security:					boopis_rfq_params.update_order_review_nonce,
				country:					country,
				state:						state,
				postcode:					postcode,
				city:						city,
				address:					address,
				address_2:					address_2,
				post_data:					$( 'form.rfq' ).serialize()
			};

			boopis_rfq_form.xhr = $.ajax({
				type:		'POST',
				url:		boopis_rfq_params.ajax_url,
				data:		data,
				success:	function( data ) {
					// Reload the page if requested
					if ( 'true' === data.reload ) {
						window.location.reload();
						return;
					}

					// Always update the fragments
					if ( data && data.fragments ) {
						$.each( data.fragments, function ( key, value ) {
							$( key ).replaceWith( value );
							$( key ).unblock();
						} );
					}

					// Check for error
					if ( 'failure' === data.result ) {

						var $form = $( 'form.rfq' );

						$( '.woocommerce-error, .woocommerce-message' ).remove();

						// Add new errors
						if ( data.messages ) {
							$form.prepend( data.messages );
						} else {
							$form.prepend( data );
						}

						// Lose focus for all fields
						$form.find( '.input-text, select' ).blur();

						// Scroll to top
						$( 'html, body' ).animate( {
							scrollTop: ( $( 'form.rfq' ).offset().top - 100 )
						}, 1000 );

					}

					// Fire updated_rfq e
					$( document.body ).trigger( 'updated_rfq' );
				}

			});
		},
		submit: function() {
			boopis_rfq_form.reset_update_rfq_timer();
			var $form = $( this );

			if ( $form.is( '.processing' ) ) {
				return false;
			}

			// Trigger a handler to let gateways manipulate the form if needed
			if ( $form.triggerHandler( 'rfq_place_order' ) !== false ) {

				$form.addClass( 'processing' );

				var form_data = $form.data();

				if ( 1 !== form_data['blockUI.isBlocked'] ) {
					$form.block({
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					});
				}

				// ajaxSetup is global, but we use it to ensure JSON is valid once returned.
				$.ajaxSetup( {
					dataFilter: function( raw_response, dataType ) {
						// We only want to work with JSON
						if ( 'json' !== dataType ) {
							return raw_response;
						}

						try {
							// check for valid JSON
							var data = $.parseJSON( raw_response );

							if ( data && 'object' === typeof data ) {

								// Valid - return it so it can be parsed by Ajax handler
								return raw_response;
							}

						} catch ( e ) {

							// attempt to fix the malformed JSON
							var valid_json = raw_response.match( /{"result.*"}/ );

							if ( null === valid_json ) {
								console.log( 'Unable to fix malformed JSON' );
							} else {
								console.log( 'Fixed malformed JSON. Original:' );
								console.log( raw_response );
								raw_response = valid_json[0];
							}
						}

						return raw_response;
					}
				} );

				$.ajax({
					type:		'POST',
					url:		boopis_rfq_params.ajax_url,
					data:		$form.serialize(),
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
								window.location.reload();
								return;
							}

							// Trigger update in case we need a fresh nonce
							if ( result.refresh === 'true' ) {
								$( document.body ).trigger( 'update_rfq' );
							}

							// Add new errors
							if ( result.messages ) {
								boopis_rfq_form.submit_error( result.messages );
							} else {
								boopis_rfq_form.submit_error( '<div class="woocommerce-error">' + boopis_rfq_params.i18n_rfq_error + '</div>' );
							}
						}
					},
					error:	function( jqXHR, textStatus, errorThrown ) {
						boopis_rfq_form.submit_error( '<div class="woocommerce-error">' + errorThrown + '</div>' );
					}
				});
			}

			return false;
		},
		submit_error: function( error_message ) {
			$( '.woocommerce-error, .woocommerce-message' ).remove();
			boopis_rfq_form.$rfq_form.prepend( error_message );
			boopis_rfq_form.$rfq_form.removeClass( 'processing' ).unblock();
			boopis_rfq_form.$rfq_form.find( '.input-text, select' ).blur();
			$( 'html, body' ).animate({
				scrollTop: ( $( 'form.rfq' ).offset().top - 100 )
			}, 1000 );
			$( document.body ).trigger( 'rfq_error' );
		}
	};

	var boopis_rfq_login_form = {
		init: function() {
			$( document.body ).on( 'click', 'a.showlogin', this.show_login_form );
		},
		show_login_form: function() {
			$( 'form.login' ).slideToggle();
			return false;
		}
	};

	boopis_rfq_form.init();
	boopis_rfq_login_form.init();
});