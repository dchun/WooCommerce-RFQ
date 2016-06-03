<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php _e( 'Request Details', 'boopis-woocommerce-rfq' ); ?></h2>

<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top;" border="0">
	<tr>
		<td class="td" style="text-align:left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" valign="top" width="50%">

			<p class="text"><?php echo $order->get_formatted_billing_address(); ?></p>
		</td>

		<td class="td" style="text-align:left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" valign="top" width="50%">

			<p class="text">
				<?php foreach ( $fields as $field ) : ?>
					<strong><?php echo wp_kses_post( $field['label'] ); ?>:</strong> 
					<span class="text"><?php echo wp_kses_post( $field['value'] ); ?></span>
					<br/>
				<?php endforeach; ?>
			</p>
		</td>

	</tr>
</table>