<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BOOPIS_RFQ_Emails' ) ) :

class BOOPIS_RFQ_Emails {

	/**
	 * Get the email header.
	 *
	 * @param mixed $email_heading heading for the email
	 */
	public function email_header( $email_heading ) {
		wc_get_template( 'emails/email-quote-header.php', array( 'email_heading' => $email_heading ) );
	}

	/**
	 * Get the email footer.
	 */
	public function email_footer() {
		wc_get_template( 'emails/email-quote-footer.php' );
	}

	/**
	 * Show the order details table
	 */
	public function order_details( $order, $sent_to_admin = false, $plain_text = false, $email = '', $prices = false ) {
		if ( $plain_text ) {
			wc_get_template( 'emails/plain/email-quote-details.php', array( 'order' => $order, 'sent_to_admin' => $sent_to_admin, 'plain_text' => $plain_text, 'email' => $email, 'prices' => $prices ) );
		} else {
			wc_get_template( 'emails/email-quote-details.php', array( 'order' => $order, 'sent_to_admin' => $sent_to_admin, 'plain_text' => $plain_text, 'email' => $email, 'prices' => $prices ) );
		}
	}

	/**
	 * Add order meta to email templates.
	 *
	 * @param mixed $order
	 * @param bool $sent_to_admin (default: false)
	 * @param bool $plain_text (default: false)
	 * @return string
	 */
	public function order_meta( $order, $sent_to_admin = false, $plain_text = false ) {
		$fields = apply_filters( 'woocommerce_email_order_meta_fields', array(), $sent_to_admin, $order );

		/**
		 * Deprecated woocommerce_email_order_meta_keys filter.
		 *
		 * @since 2.3.0
		 */
		$_fields = apply_filters( 'woocommerce_email_order_meta_keys', array(), $sent_to_admin );

		if ( $_fields ) {
			foreach ( $_fields as $key => $field ) {
				if ( is_numeric( $key ) ) {
					$key = $field;
				}

				$fields[ $key ] = array(
					'label' => wptexturize( $key ),
					'value' => wptexturize( get_post_meta( $order->id, $field, true ) )
				);
			}
		}

		if ( $fields ) {

			if ( $plain_text ) {

				foreach ( $fields as $field ) {
					if ( isset( $field['label'] ) && isset( $field['value'] ) && $field['value'] ) {
						echo $field['label'] . ': ' . $field['value'] . "\n";
					}
				}

			} else {

				foreach ( $fields as $field ) {
					if ( isset( $field['label'] ) && isset( $field['value'] ) && $field['value'] ) {
						echo '<p><strong>' . $field['label'] . ':</strong> ' . $field['value'] . '</p>';
					}
				}
			}
		}
	}

	/**
	 * Is customer detail field valid?
	 * @param  array  $field
	 * @return boolean
	 */
	public function customer_detail_field_is_valid( $field ) {
		return isset( $field['label'] ) && ! empty( $field['value'] );
	}
	
	/**
	 * Add customer details to email templates.
	 *
	 * @param mixed $order
	 * @param bool $sent_to_admin (default: false)
	 * @param bool $plain_text (default: false)
	 * @return string
	 */
	public function customer_details( $order, $sent_to_admin = false, $plain_text = false ) {
		$fields = array();

		if ( $order->billing_email ) {
			$fields['billing_email'] = array(
				'label' => __( 'Email', 'woocommerce' ),
				'value' => wptexturize( $order->billing_email )
			);
	  }

	  if ( $order->billing_phone ) {
			$fields['billing_phone'] = array(
				'label' => __( 'Tel', 'woocommerce' ),
				'value' => wptexturize( $order->billing_phone )
			);
	  }

		if ( $order->customer_note ) {
			$fields['customer_note'] = array(
				'label' => __( 'Note', 'woocommerce' ),
				'value' => wptexturize( $order->customer_note )
			);
		}

		$fields = array_filter( apply_filters( 'woocommerce_email_customer_details_fields', $fields, $sent_to_admin, $order ), array( $this, 'customer_detail_field_is_valid' ) );

		if ( $plain_text ) {
			wc_get_template( 'emails/plain/email-quote-customer-details.php', array( 'order' => $order, 'fields' => $fields ) );
		} else {
			wc_get_template( 'emails/email-quote-customer-details.php', array( 'order' => $order, 'fields' => $fields ) );
		}
	 	
	}

	public function proposal_terms( $order, $sent_to_admin = false, $plain_text = false ) {
		if ( $plain_text ) {
			wc_get_template( 'emails/plain/email-quote-terms.php', array( 'order' => $order ) );
		} else {
			wc_get_template( 'emails/email-quote-terms.php', array( 'order' =>  $order ) );
		}
	}

}

endif;