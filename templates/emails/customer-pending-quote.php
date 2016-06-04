<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quote_mail = new BOOPIS_RFQ_Emails();

$quote_mail->email_header( $email_heading, $email ); 

?>

<p><?php _e( "Your request for quotation has been processed. The details of our proposal are shown below:", 'boopis-woocommerce-rfq' ); ?></p>

<?php

$quote_mail->order_details( $order, $sent_to_admin, $plain_text, $email, true );

$quote_mail->proposal_terms( $order, $sent_to_admin, $plain_text, $email );

$quote_mail->customer_details( $order, $sent_to_admin, $plain_text, $email );

$quote_mail->order_meta( $order, $sent_to_admin, $plain_text, $email );

$quote_mail->email_footer( $email );