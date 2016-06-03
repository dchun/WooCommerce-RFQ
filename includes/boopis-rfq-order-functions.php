<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'boopis_rfq_get_quote_terms' ) ) {
	function boopis_rfq_get_quote_terms( $order_id ){
		return get_post_meta( $order_id, '_boopis_rfq_terms', true );
	}	
}

if ( ! function_exists( 'boopis_rfq_get_quote_expiration' ) ) {
	function boopis_rfq_get_quote_expiration( $order_id ){
		return get_post_meta( $order_id, '_boopis_rfq_expiration_date', true );
	}
}

if ( ! function_exists( 'boopis_rfq_send_email' ) ) {
	function boopis_rfq_send_email( $email_id, $order ){
		$mailer           = WC()->mailer();
		$mails            = $mailer->get_emails();

		if ( ! empty( $mails ) ) {
			foreach ( $mails as $mail ) {
				if ( $mail->id == $email_id ) {
					$mail->trigger( $order->id );
					$order->add_order_note( sprintf( __( '%s email notification manually sent.', 'woocommerce' ), $mail->title ), false, true );
				}
			}
		}
	}
}