<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

echo strtoupper( sprintf( __( 'RFQ number: %s', 'woocommerce' ), $order->get_order_number() ) ) . "\n";
echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $order->order_date ) ) . "\n";
echo "\n" .	wc_get_template( 'emails/plain/email-quote-items.php', array(
					'order'               => $order,
					'items'               => $order->get_items(),
					'show_download_links' => $order->is_download_permitted() && ! $args['sent_to_admin'],
					'show_sku'            => $sent_to_admin,
					'show_purchase_note'  => $order->is_paid() && ! $args['sent_to_admin'],
					'show_image'          => false,
					'image_size'          => array( 32, 32 ),
					'sent_to_admin'       => $sent_to_admin,
					'prices'							=> $prices
				) );

echo "==========\n\n";

if ( $totals = $order->get_order_item_totals() ) {
	foreach ( $totals as $total ) {
		echo $total['label'] . "\t " . $total['value'] . "\n";
	}
}

if ( $sent_to_admin ) {
    echo "\n" . sprintf( __( 'View order: %s', 'woocommerce'), admin_url( 'post.php?post=' . $order->id . '&action=edit' ) ) . "\n";
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
