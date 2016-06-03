<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce;
?>
<table class="shop_table shop_table_responsive quote" cellspacing="0">
	<thead>
		<tr>
			<th class="product-remove">&nbsp;</th>
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"><?php _e( 'Product', 'boopis-woocommerce-rfq' ); ?></th>
			<th class="product-quantity"><?php _e( 'Quantity', 'boopis-woocommerce-rfq' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php do_action( 'boopis_before_quote_contents' ); ?>

		<?php
		if ( sizeof( $woocommerce->quote->boopis_get_quote() ) > 0 ) {
			foreach ( $woocommerce->quote->boopis_get_quote() as $quote_item_key => $values ) {
				$_product = $values['data'];
				if ( $_product->exists() && $values['quantity'] > 0 ) {
					?>
					<tr class = "<?php echo esc_attr( apply_filters('boopis_quote_table_item_class', 'cart_table_item', $values, $quote_item_key ) ); ?>">
						<!-- Remove from quote link -->
						<td class="product-remove">
							<?php
								echo apply_filters( 'boopis_quote_item_remove_link', sprintf('<a href="%s" class="remove" title="%s">&times;</a>', esc_url( $woocommerce->quote->boopis_get_remove_url( $quote_item_key ) ), __( 'Remove this item', 'boopis-woocommerce-rfq' ) ), $quote_item_key );
							?>
						</td>

						<!-- The thumbnail -->
						<td class="product-thumbnail">
							<?php
								$thumbnail = apply_filters( 'boopis_in_quote_product_thumbnail', $_product->get_image(), $values, $quote_item_key );

								if ( ! $_product->is_visible() || ( ! empty( $_product->variation_id ) && ! $_product->parent_is_visible() ) )
									echo $thumbnail;
								else
									printf('<a href="%s">%s</a>', esc_url( get_permalink( apply_filters('boopis_in_quote_product_id', $values['product_id'] ) ) ), $thumbnail );
							?>
						</td>

						<!-- Product Name -->
						<td class="product-name">
							<?php
								if ( ! $_product->is_visible() || ( ! empty( $_product->variation_id ) && ! $_product->parent_is_visible() ) )
									echo apply_filters( 'boopis_in_quote_product_title', $_product->get_title(), $values, $quote_item_key );
								else
									printf('<a href="%s">%s</a>', esc_url( get_permalink( apply_filters('boopis_in_quote_product_id', $values['product_id'] ) ) ), apply_filters('boopis_in_quote_product_title', $_product->get_title(), $values, $quote_item_key ) );

								// Meta data
								echo $woocommerce->quote->boopis_get_item_data( $values );
							?>
						</td>

						<!-- Quantity inputs -->
						<td class="product-quantity">
							<?php
								if ( $_product->is_sold_individually() ) {
									$product_quantity = sprintf( '1 <input type="hidden" name="quote[%s][qty]" value="1" />', $quote_item_key );
								} else {

									$step	= apply_filters( 'boopis_quantity_input_step', '1', $_product );
									$min 	= apply_filters( 'boopis_quantity_input_min', '', $_product );
									$max 	= apply_filters( 'boopis_quantity_input_max', '', $_product );

									$product_quantity = sprintf( '<div class="quantity"><input readonly type="number" name="pquantity[]" step="%s" min="%s" max="%s" value="%s" size="4" title="' . _x( 'Qty', 'Product quantity input tooltip', 'boopis-woocommerce-rfq' ) . '" class="input-text qty text" maxlength="12" /></div>', $step, $min, $max, esc_attr( $values['quantity'] ) );
								}

								echo apply_filters( 'boopis_quote_item_quantity', $product_quantity, $quote_item_key );
					
							?>
						</td>

					</tr>
					<?php
				}
			}
		}

		do_action( 'boopis_quote_contents' );
		?>
	</tbody>
</table>