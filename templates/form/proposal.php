<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="woocommerce">

<?php

wc_print_notices();

$order_id = $_GET['proposal'];

$order_key = empty( $_GET['key'] ) ? '' : wc_clean( $_GET['key'] );

$order = false;

if ( $order_id > 0 ) {
	$order = wc_get_order( $order_id );
	if ( $order->order_key != $order_key )
		unset( $order );
}

if ( !isset($order) ) {
	echo '<div class="woocommerce-error">' . __( 'Sorry this proposal is invalid', 'boopis-woocomemrce-rfq' ) . '</div>';
	return;
}

$errors = 0;

if ( ! current_user_can( 'pay_for_order', $order_id ) ) {
	echo '<div class="woocommerce-error">' . __( 'Invalid order. If you have an account please log in and try again.', 'woocommerce' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ) . '" class="wc-forward">' . __( 'My Account', 'woocommerce' ) . '</a>' . '</div>';
	$errors += 1;
}

if ( !$order->has_status( 'pending-quote' ) ) {
	echo '<div class="woocommerce-error">' . sprintf( __( "This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.", "woocommerce" ), wc_get_order_status_name( $order->get_status() ) ) . '</div>';
	$errors += 1;
}

if ( $order->get_total() <= 0 ) {
	echo '<div class="woocommerce-error">' . __( 'It seems like the proposal price is invalid.', 'boopis-woocomemrce-rfq' ) . '</div>';
	$errors += 1;
}

$validity_date = boopis_rfq_get_quote_expiration( $order->id );
$today = date("Y-m-d");

if(!empty($validity_date)) {
	if ($validity_date < $today) {
		echo '<div class="woocommerce-error">' . __( 'Your proposal has expired', 'boopis-woocommerce-rfq' ) . '</div>';
		$errors += 1;
	}
}

if($errors > 0) {
	return;
}

$terms = boopis_rfq_get_quote_terms( $order->id ); 

?>
		<form id="proposal" method="post">

			<table class="shop_table">
				<thead>
					<tr>
						<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
						<th class="product-quantity"><?php _e( 'Qty', 'woocommerce' ); ?></th>
						<th class="product-total"><?php _e( 'Totals', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( sizeof( $order->get_items() ) > 0 ) : ?>
						<?php foreach ( $order->get_items() as $item ) : ?>
							<tr>
								<td class="product-name">
									<?php echo esc_html( $item['name'] ); ?>
									<?php $order->display_item_meta( $item ); ?>
								</td>
								<td class="product-quantity"><?php echo esc_html( $item['qty'] ); ?></td>
								<td class="product-subtotal"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<?php if ( $totals = $order->get_order_item_totals() ) : ?>
						<?php foreach ( $totals as $total ) : ?>
							<tr>
								<th scope="row" colspan="2"><?php echo $total['label']; ?></th>
								<td class="product-total"><?php echo $total['value']; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tfoot>
			</table>

			<?php if((!empty($validity_date) || !empty($terms))) : ?>
				<h2><?php _e( 'Terms', 'boopis-woocommerce-rfq' ); ?></h2>

				<?php if (!empty($terms)) : ?>
					<p><?php echo nl2br( esc_html($terms) ); ?></p>
				<?php endif; ?>

				<?php if (!empty($validity_date)) : ?>
					<p><strong>Valid Until - <?php echo date_i18n(get_option('date_format'), strtotime($validity_date)); ?></strong></p>
				<?php endif; ?>

			<?php endif; ?>


			<div class="form-row">
        <input type="hidden" name="action" value="boopis_rfq_process_proposal">
				<input type="submit" class="button alt" name="accept" id="accept" value='<?php echo __("Accept Proposal", "boopis-woocommere-rfq"); ?> ' />
				<input type="submit" class="button alt" name="decline" id="decline" value='<?php echo __("Decline Proposal", "boopis-woocommere-rfq"); ?> ' />


				<?php wp_nonce_field( 'boopis-rfq-process-proposal' ); ?>
				
			</div>
		</form>
		<?php
			wp_enqueue_script( 'boopis-rfq-proposal', BOOPIS_RFQ_URL . 'assets/js/frontend/proposal.js', array('jquery'), '1.0.0', true );

			$params = array(
				'ajax_url'                  => WC()->ajax_url(),
				'debug_mode'                => defined('WP_DEBUG') && WP_DEBUG,
				'i18n_rfq_error'       			=> esc_attr__( 'Error processing request. Please try again.', 'boopis-woocommerce-rfq' ),
			);

			wp_localize_script( 'boopis-rfq-proposal', 'boopis_rfq_params', $params );

		?>
</div>
