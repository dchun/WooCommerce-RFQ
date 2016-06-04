<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo strtoupper( __( 'Proposal Terms', 'boopis-woocommerce-rfq' ) ) . "\n\n";

echo nl2br( esc_html( boopis_rfq_get_quote_terms( $order->id ) ) ) . "\n";
	
echo sprintf( 'Valid Until - %s', boopis_rfq_get_quote_expiration( $order->id ) );

$return_url = get_permalink( get_option('boopis_rfq_page_id') );
$return_url = add_query_arg( 'proposal', $order->id, $return_url );
$return_url = add_query_arg( 'key', $order->order_key, $return_url );
if ( 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) || is_ssl() ) {
	$return_url = str_replace( 'http:', 'https:', $return_url );
}
$text = apply_filters('boopis_rfq_proposal_email_link_text', __('Accept or decline proposal here', 'boopis-woocommere-rfq') );

echo $text . ': ' . $return_url;
