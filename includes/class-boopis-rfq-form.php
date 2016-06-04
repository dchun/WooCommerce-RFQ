<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BOOPIS_RFQ_Form' ) ) :

class BOOPIS_RFQ_Form {

	/** @var array Array of posted form data. */
	public $posted;

	/** @var array Array of fields to display on the form. */
	public $form_fields;

	/** @var bool Whether or not signups are allowed. */
	public $enable_signup;

	/** @var int ID of customer. */
	private $customer_id;

	/**
	 * Constructor for the form class. Hooks in methods and defines form fields.
	 *
	 * @access public
	 */
	public function __construct () {

		// Define all Form fields
		$this->form_fields['billing'] 	= WC()->countries->get_address_fields( $this->get_value( 'billing_country' ), 'billing_' );

		if ( get_option( 'woocommerce_registration_generate_username' ) == 'no' ) {
			$this->form_fields['account']['account_username'] = array(
				'type' 			=> 'text',
				'label' 		=> __( 'Account username', 'woocommerce' ),
				'required'      => true,
				'placeholder' 	=> _x( 'Username', 'placeholder', 'woocommerce' )
			);
		}

		if ( get_option( 'woocommerce_registration_generate_password' ) == 'no' ) {
			$this->form_fields['account']['account_password'] = array(
				'type' 				=> 'password',
				'label' 			=> __( 'Account password', 'woocommerce' ),
				'required'          => true,
				'placeholder' 		=> _x( 'Password', 'placeholder', 'woocommerce' )
			);
		}

		$this->form_fields['order']	= array(
			'order_comments' => array(
				'type' => 'textarea',
				'class' => array('notes'),
				'label' => __( 'Quote Notes', 'woocommerce' ),
				'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce')
			)
		);

		$this->form_fields = apply_filters( 'boopis_rfq_form_fields', $this->form_fields );

	}

	/**
	 * Process the form on submit.
	 */
	public function process_form() {
		global $woocommerce;
		try {
			if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'boopis-rfq-process-form' ) ) {
				throw new Exception( __( 'We were unable to process your request, please try again.', 'boopis-woocommerce-rfq' ) );
			}

			// Prevent timeout
			@set_time_limit(0);

			// Allow plugins to process incoming post data
			do_action( 'boopis_rfq_process' );

			// Form fields (not defined in form_fields)
			$this->posted['createaccount']             = isset( $_POST['createaccount'] ) && ! empty( $_POST['createaccount'] ) ? 1 : 0;

			// Get posted form_fields and do validation
			foreach ( $this->form_fields as $fieldset_key => $fieldset ) {

				// Skip account if not needed
				if ( $fieldset_key == 'account' && ( is_user_logged_in() || empty( $this->posted['createaccount'] ) ) ) {
					continue;
				}

				foreach ( $fieldset as $key => $field ) {

					if ( ! isset( $field['type'] ) ) {
						$field['type'] = 'text';
					}

					// Get Value
					switch ( $field['type'] ) {
						case "checkbox" :
							$this->posted[ $key ] = isset( $_POST[ $key ] ) ? 1 : 0;
						break;
						case "multiselect" :
							$this->posted[ $key ] = isset( $_POST[ $key ] ) ? implode( ', ', array_map( 'wc_clean', $_POST[ $key ] ) ) : '';
						break;
						case "textarea" :
							$this->posted[ $key ] = isset( $_POST[ $key ] ) ? wp_strip_all_tags( wp_check_invalid_utf8( stripslashes( $_POST[ $key ] ) ) ) : '';
						break;
						default :
							$this->posted[ $key ] = isset( $_POST[ $key ] ) ? ( is_array( $_POST[ $key ] ) ? array_map( 'wc_clean', $_POST[ $key ] ) : wc_clean( $_POST[ $key ] ) ) : '';
						break;
					}

					// Validation: Required fields
					if ( isset( $field['required'] ) && $field['required'] && empty( $this->posted[ $key ] ) ) {
						wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
					}

					if ( ! empty( $this->posted[ $key ] ) ) {

						// Validation rules
						if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
							foreach ( $field['validate'] as $rule ) {
								switch ( $rule ) {
									case 'postcode' :
										$this->posted[ $key ] = strtoupper( str_replace( ' ', '', $this->posted[ $key ] ) );

										if ( ! WC_Validation::is_postcode( $this->posted[ $key ], $_POST[ $fieldset_key . '_country' ] ) ) :
											wc_add_notice( __( 'Please enter a valid postcode/ZIP.', 'woocommerce' ), 'error' );
										else :
											$this->posted[ $key ] = wc_format_postcode( $this->posted[ $key ], $_POST[ $fieldset_key . '_country' ] );
										endif;
									break;
									case 'phone' :
										$this->posted[ $key ] = wc_format_phone_number( $this->posted[ $key ] );

										if ( ! WC_Validation::is_phone( $this->posted[ $key ] ) )
											wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid phone number.', 'woocommerce' ), 'error' );
									break;
									case 'email' :
										$this->posted[ $key ] = strtolower( $this->posted[ $key ] );

										if ( ! is_email( $this->posted[ $key ] ) )
											wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid email address.', 'woocommerce' ), 'error' );
									break;
									case 'state' :
										// Get valid states
										$valid_states = WC()->countries->get_states( isset( $_POST[ $fieldset_key . '_country' ] ) ? $_POST[ $fieldset_key . '_country' ] : ( 'billing' === $fieldset_key ? WC()->customer->get_country() : WC()->customer->get_shipping_country() ) );

										if ( ! empty( $valid_states ) && is_array( $valid_states ) ) {
											$valid_state_values = array_flip( array_map( 'strtolower', $valid_states ) );

											// Convert value to key if set
											if ( isset( $valid_state_values[ strtolower( $this->posted[ $key ] ) ] ) ) {
												 $this->posted[ $key ] = $valid_state_values[ strtolower( $this->posted[ $key ] ) ];
											}
										}

										// Only validate if the country has specific state options
										if ( ! empty( $valid_states ) && is_array( $valid_states ) && sizeof( $valid_states ) > 0 ) {
											if ( ! in_array( $this->posted[ $key ], array_keys( $valid_states ) ) ) {
												wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not valid. Please enter one of the following:', 'woocommerce' ) . ' ' . implode( ', ', $valid_states ), 'error' );
											}
										}
									break;
								}
							}
						}
					}
				}
			}

			if ( wc_notice_count( 'error' ) == 0 ) {

				// Customer accounts
				$this->customer_id = get_current_user_id();
				
				if ( ! is_user_logged_in() &&  ! empty( $this->posted['createaccount'] ) ) {

					$username     = ! empty( $this->posted['account_username'] ) ? $this->posted['account_username'] : '';
					$password     = ! empty( $this->posted['account_password'] ) ? $this->posted['account_password'] : '';
					$new_customer = wc_create_new_customer( $this->posted['billing_email'], $username, $password );

					if ( is_wp_error( $new_customer ) ) {
						throw new Exception( $new_customer->get_error_message() );
					}

					$this->customer_id = $new_customer;

					wc_set_customer_auth_cookie( $this->customer_id );

					// As we are now logged in, rfq will need to refresh to show logged in data
					WC()->session->set( 'reload_rfq', true );

					// Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering
					WC()->cart->calculate_totals();

					// Add customer info from other billing fields
					if ( $this->posted['billing_first_name'] && apply_filters( 'woocommerce_checkout_update_customer_data', true, $this ) ) {
						$userdata = array(
							'ID'           => $this->customer_id,
							'first_name'   => $this->posted['billing_first_name'] ? $this->posted['billing_first_name'] : '',
							'last_name'    => $this->posted['billing_last_name'] ? $this->posted['billing_last_name'] : '',
							'display_name' => $this->posted['billing_first_name'] ? $this->posted['billing_first_name'] : ''
						);
						wp_update_user( apply_filters( 'woocommerce_checkout_customer_userdata', $userdata, $this ) );
					}
				}

				// Abort if errors are present
				if ( wc_notice_count( 'error' ) > 0 )
					throw new Exception();

				$order_id = $this->create_order_from_quote();

				if ( is_wp_error( $order_id ) ) {
					throw new Exception( $order_id->get_error_message() );
				}

				if ( empty( $order ) ) {
					$order = wc_get_order( $order_id );
				}

				// Empty the quote
				$woocommerce->quote->boopis_empty_quote();

				// Get redirect
				$return_url = get_permalink( get_option('boopis_rfq_page_id') );
				$return_url = add_query_arg( 'rfq-received', $order_id, $return_url );
				$return_url = add_query_arg( 'key', $order->order_key, $return_url );
				if ( 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) || is_ssl() ) {
					$return_url = str_replace( 'http:', 'https:', $return_url );
				}

				// Redirect to success page
				if ( is_ajax() ) {
					wp_send_json( array(
						'result' 	=> 'success',
						'redirect'  => $return_url, $order
					) );
				} else {
					wp_safe_redirect( $return_url, $order );
					exit;
				}

			}

		} catch ( Exception $e ) {
			if ( ! empty( $e ) ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}

		// If we reached this point then there were errors
		if ( is_ajax() ) {

			// only print notices if not reloading the form, otherwise they're lost in the page reload
			if ( ! isset( WC()->session->reload_rfq ) ) {
				ob_start();
				wc_print_notices();
				$messages = ob_get_clean();
			}

			$response = array(
				'result'	=> 'failure',
				'messages' 	=> isset( $messages ) ? $messages : '',
				'reload'    => isset( WC()->session->reload_rfq ) ? 'true' : 'false'
			);

			unset( WC()->session->reload_rfq );

			wp_send_json( $response );
		}
	}

	/**
	 * Get a posted address field after sanitization and validation.
	 * @param string $key
	 * @param string $type billing for shipping
	 * @return string
	 */
	public function get_posted_address_data( $key, $type = 'billing' ) {
		if ( 'billing' === $type ) {
			$return = isset( $this->posted[ 'billing_' . $key ] ) ? $this->posted[ 'billing_' . $key ] : '';
		}

		// Use logged in user's billing email if neccessary
		if ( 'email' === $key && empty( $return ) && is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$return       = $current_user->user_email;
		}
		return $return;
	}

	public function get_value( $input ) {
		if ( ! empty( $_POST[ $input ] ) ) {

			return wc_clean( $_POST[ $input ] );

		} else {

			$value = null;

			if ( $value !== null ) {
				return $value;
			}

			// Get the billing_ fields
			$address_fields = WC()->countries->get_address_fields();

			if ( is_user_logged_in() && array_key_exists( $input, $address_fields ) ) {
				$current_user = wp_get_current_user();

				if ( $meta = get_user_meta( $current_user->ID, $input, true ) ) {
					return $meta;
				}

				if ( $input == 'billing_email' ) {
					return $current_user->user_email;
				}
			}

			switch ( $input ) {
				case 'billing_country' :
					return apply_filters( 'default_rfq_country', WC()->customer->get_country() ? WC()->customer->get_country() : WC()->countries->get_base_country(), 'billing' );
				case 'billing_state' :
					return apply_filters( 'default_rfq_state', WC()->customer->has_calculated_shipping() ? WC()->customer->get_state() : '', 'billing' );
				case 'billing_postcode' :
					return apply_filters( 'default_rfq_postcode', WC()->customer->get_postcode() ? WC()->customer->get_postcode() : '', 'billing' );
				default :
					return apply_filters( 'default_rfq_' . $input, null, $input );
			}

		}
	}

	public function create_order_from_quote() {
		global $wpdb;

		try {
			// Start transaction if available
			wc_transaction_query( 'start' );

			$order_data = array(
				'status'        => 'new-quote',
				'customer_id'   => $this->customer_id,
				'customer_note' => isset( $this->posted['order_comments'] ) ? $this->posted['order_comments'] : '',
				'created_via'   => 'rfq'
			);

			$order = wc_create_order( $order_data );

			if ( is_wp_error( $order ) ) {
				throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 520 ) );
			} elseif ( false === $order ) {
				throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 521 ) );
			} else {
				$order_id = $order->id;
				do_action( 'woocommerce_new_order', $order_id );
			}
			

			// Store the line items to the new/resumed order
			global $woocommerce;
			foreach ( $woocommerce->quote->boopis_get_quote() as $quote_item_key => $values ) {
				$item_id = $order->add_product(
					$values['data'],
					$values['quantity'],
					array(
						'variation' => $values['variation'],
					)
				);

				if ( ! $item_id ) {
					throw new Exception( sprintf( __( 'Error %d: Unable to create rfq. Please try again.', 'boopis-woocommerce-rfq' ), 525 ) );
				}
			}

			// Billing address
			$billing_address = array();
			if ( $this->form_fields['billing'] ) {
				foreach ( array_keys( $this->form_fields['billing'] ) as $field ) {
					$field_name = str_replace( 'billing_', '', $field );
					$billing_address[ $field_name ] = $this->get_posted_address_data( $field_name );
				}
			}

			$order->set_address( $billing_address, 'billing' );

			// Update user meta
			if ( $this->customer_id ) {
				if ( apply_filters( 'woocommerce_checkout_update_customer_data', true, $this ) ) {
					foreach ( $billing_address as $key => $value ) {
						update_user_meta( $this->customer_id, 'billing_' . $key, $value );
					}
				}
			}

			// Let plugins add meta
			do_action( 'boopis_rfq_update_order_meta', $order_id, $this->posted );

			// If we got here, the order was created without problems!
			wc_transaction_query( 'commit' );

			// Send new email to customer and admin
			boopis_rfq_send_email('customer_new_quote', $order);
			boopis_rfq_send_email('new_quote', $order);

		} catch ( Exception $e ) {
			// There was an error adding order data!
			wc_transaction_query( 'rollback' );
			return new WP_Error( 'checkout-error', $e->getMessage() );
		}

		return $order_id;
	}

}

endif;