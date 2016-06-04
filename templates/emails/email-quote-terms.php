<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php _e( 'Proposal Terms', 'boopis-woocommerce-rfq' ); ?></h2>
<p>
	<?php echo nl2br( esc_html( boopis_rfq_get_quote_terms( $order->id ) ) ); ?>
</p>
<p>
	<?php _e('Valid Until', 'boopis-woocommerce-rfq'); ?> - <?php echo boopis_rfq_get_quote_expiration( $order->id ); ?>
</p>
<?php
	$return_url = get_permalink( get_option('boopis_rfq_page_id') );
	$return_url = add_query_arg( 'proposal', $order->id, $return_url );
	$return_url = add_query_arg( 'key', $order->order_key, $return_url );
	if ( 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) || is_ssl() ) {
		$return_url = str_replace( 'http:', 'https:', $return_url );
	}
	$text = apply_filters('boopis_rfq_proposal_email_link_text', __('Accept or decline proposal here', 'boopis-woocommere-rfq') );
?>
<p>
	<a href="<?php echo $return_url; ?>"><?php echo $text; ?></a>
</p>