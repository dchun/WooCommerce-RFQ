<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wp;

wc_print_notices();

$order_id = $_GET['rfq-received'];

$order_key = empty( $_GET['key'] ) ? '' : wc_clean( $_GET['key'] );

$order = false; 

if ( $order_id > 0 ) {
	$order = wc_get_order( $order_id );
	if ( $order->order_key != $order_key )
		unset( $order );
}

if ( isset($order) ) : ?>

	<p class="woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your request for quotation has been received.', 'boopis-woocommerce-rfq' ), $order ); ?></p>

	<ul class="woocommerce-thankyou-order-details order_details">
		<li class="order">
			<?php _e( 'RFQ Number:', 'woocommerce' ); ?>
			<strong><?php echo $order->get_order_number(); ?></strong>
		</li>
		<li class="date">
			<?php _e( 'Date:', 'woocommerce' ); ?>
			<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></strong>
		</li>
	</ul>
	<div class="clear"></div>

	<!-- Order Details -->
	<?php

	$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
	$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
	?>
	<h2><?php _e( 'RFQ Details', 'woocommerce' ); ?></h2>
	<table class="shop_table order_details">
		<thead>
			<tr>
				<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach( $order->get_items() as $item_id => $item ) :
					$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
					$purchase_note = get_post_meta( $product->id, '_purchase_note', true );
			?>
					
					<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
						<td class="product-name">
							<?php
								$is_visible = $product && $product->is_visible();

								echo apply_filters( 'woocommerce_order_item_name', $is_visible ? sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ) : $item['name'], $item, $is_visible );
								echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );

								do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

								$order->display_item_meta( $item );
								$order->display_item_downloads( $item );

								do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
							?>
						</td>
					</tr>
					<?php if ( $show_purchase_note && $purchase_note ) : ?>
					<tr class="product-purchase-note">
						<td colspan="3"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
					</tr>
					<?php endif; ?>

			<?php endforeach; ?>
		</tbody>
	</table>

	<!-- Customer Details  -->

	<header><h2><?php _e( 'Request Details', 'boopis-woocommerce-rfq' ); ?></h2></header>

	<div class="col2-set addresses">
		<div class="col-1">
			<header class="title">
			</header>
			<address>
				<?php echo ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'woocommerce' ); ?>
			</address>
		</div><!-- /.col-1 -->
		<div class="col-2">
			<header class="title">
			</header>
			<address>
				<?php if ( $order->billing_email ) : ?>
					<strong><?php _e( 'Email:', 'woocommerce' ); ?></strong>&nbsp;<?php echo esc_html( $order->billing_email ); ?>
					<br/>
				<?php endif; ?>

				<?php if ( $order->billing_phone ) : ?>
					<strong><?php _e( 'Telephone:', 'woocommerce' ); ?></strong>&nbsp;<?php echo esc_html( $order->billing_phone ); ?>
					<br/>
				<?php endif; ?>

				<?php if ( $order->customer_note ) : ?>
					<strong><?php _e( 'Note:', 'woocommerce' ); ?></strong>&nbsp;<?php echo wptexturize( $order->customer_note ); ?>
					<br/>
				<?php endif; ?>

			</address>
		</div><!-- /.col-2 -->
	</div><!-- /.col2-set -->

<?php else : ?>

	<p class="woocommerce-thankyou-order-received">
		<?php _e( 'Thank you. Your rfq has been made. Check your email to ensure that your request has been received.', 'boopis-woocommerce-rfq' ); ?>		
	</p>

<?php endif; ?>
