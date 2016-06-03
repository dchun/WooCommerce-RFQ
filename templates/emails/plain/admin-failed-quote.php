<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quote_mail = new BOOPIS_RFQ_Emails();

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( 'Proposal for quote #%d from %s has failed. The quote was as follows:', 'woocommerce' ), $order->id ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

$quote_mail->order_details( $order, $sent_to_admin, $plain_text, $email, true );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

$quote_mail->order_meta( $order, $sent_to_admin, $plain_text, $email );

$quote_mail->proposal_terms( $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

$quote_mail->customer_details( $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
