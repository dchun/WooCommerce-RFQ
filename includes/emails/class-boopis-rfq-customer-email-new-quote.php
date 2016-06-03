<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BOOPIS_RFQ_Customer_New_Quote' ) ) :

/**
 * Customer New Quote Email.
 *
 * An email sent to the customer when a new quote is made.
 *
 * @class       BOOPIS_RFQ_Customer_New_Quote
 * @extends     WC_Email
 */
class BOOPIS_RFQ_Customer_New_Quote extends WC_Email {

	/**
	 * Constructor.
	 */
	function __construct() {
		$this->id               = 'customer_new_quote';
		$this->customer_email   = true;
		$this->title            = __( 'New Quote (Customer)', 'boopis-woocommerce-rfq' );
		$this->description      = __( 'This is a quote notification sent to customers containing their quote details.', 'boopis-woocommerce-rfq' );
		$this->heading          = __( 'Thank you for your quote request', 'boopis-woocommerce-rfq' );
		$this->subject          = __( '[{site_title}] Your RFQ from {order_date}', 'boopis-woocommerce-rfq' );
		$this->template_html    = 'emails/customer-new-quote.php';
		$this->template_plain   = 'emails/plain/customer-new-quote.php';

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Trigger.
	 *
	 * @param int $order_id
	 */
	function trigger( $order_id ) {

		if ( $order_id ) {
			$this->object       = wc_get_order( $order_id );
			$this->recipient    = $this->object->billing_email;

			$this->find['order-date']      = '{order_date}';
			$this->find['order-number']    = '{order_number}';

			$this->replace['order-date']   = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
			$this->replace['order-number'] = $this->object->get_order_number();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this
		) );
	}

	/**
	 * Get content plain.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		) );
	}
}

endif;

return new BOOPIS_RFQ_Customer_New_Quote();
