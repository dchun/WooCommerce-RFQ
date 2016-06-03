<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo strtoupper( __( 'Request Details', 'boopis-woocommerce-rfq' ) ) . "\n\n";

echo preg_replace( '#<br\s*/?>#i', "\n", $order->get_formatted_billing_address() ) . "\n";

echo preg_replace( '#<br\s*/?>#i', "\n", $shipping ) . "\n";

foreach ( $fields as $field ) {
    echo wp_kses_post( $field['label'] ) . ': ' . wp_kses_post( $field['value'] ) . "\n";
}