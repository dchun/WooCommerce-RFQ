<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$quote_mail = new BOOPIS_RFQ_Emails();

$quote_mail->email_header( $email_heading, $email ); 

?>

<p><?php __( 'You have received a request for quotation. The RFQ is as follows:', 'boopis-woocommerce-rfq' ); ?></p>

<?php

$quote_mail->order_details( $order, $sent_to_admin, $plain_text, $email, false );

$quote_mail->customer_details( $order, $sent_to_admin, $plain_text, $email );

$quote_mail->order_meta( $order, $sent_to_admin, $plain_text, $email );

$quote_mail->email_footer( $email );