<div class="wrap">
<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (isset($_POST["update_settings"])):
	
	check_admin_referer( 'update_rfq_settings' );

	$quote_trigger = isset($_POST["quote_trigger"]) ? $_POST["quote_trigger"] : '';
	$tag_trigger_value = isset($_POST["tag_trigger_value"]) ? $_POST["tag_trigger_value"] : '';
	$replace_price = isset($_POST["replace_price"]) ? $_POST["replace_price"] : '';
	$rfq_page = isset($_POST["rfq_page"]) ? $_POST["rfq_page"] : '';

	// Validate and Sanitize
	$quote_trigger = !empty($quote_trigger) ? ($quote_trigger == 'on' ? 1 : 0) : '';
	$tag_trigger_value = !empty($tag_trigger_value) ? sanitize_text_field(strval($tag_trigger_value)) : '';
	$replace_price = !empty($replace_price) ? ($replace_price == 'on' ? 1 : 0) : '';
	$rfq_page = !empty($rfq_page) ? balanceTags(strval($rfq_page)) : '';

	update_option("boopis_rfq_tag_trigger_value", $tag_trigger_value);
	update_option("boopis_rfq_replace_price", $replace_price);
	update_option("boopis_rfq_quote_trigger", $quote_trigger);
	update_option("boopis_rfq_page", $rfq_page);
?> 
<div id="message" class="updated">Settings saved</div>  
<?php endif; ?>

	<h2><?php _e('Boopis RFQ Settings', 'boopis-woocommerce-rfq'); ?></h2>

	<form method="POST" action="">  
		<?php wp_nonce_field( 'update_rfq_settings' ); ?>
		<table class="form-table">  
			<tr valign="top">  
				<th scope="row">  
					<label for="quote_trigger">  
						<?php _e('Change Quotation Trigger (Default - Zero Price Recommended): ', 'boopis-woocommerce-rfq'); ?>
					</label>   
				</th>  
				<td>
					<input type="checkbox" id="quote_trigger" name="quote_trigger" <?php echo get_option("boopis_rfq_quote_trigger") ? ' checked="checked"' : '' ; ?> />
						<?php _e('Define by Tag', 'boopis-woocommerce-rfq'); ?>
						<br>
					<div class="hidden-tag">
						<label for="tag_trigger_value">  
							<?php _e('Tag Name', 'boopis-woocommerce-rfq'); ?>
						</label> 
						<input type="text" id="tag_trigger_value" name="tag_trigger_value" value='<?php echo get_option("boopis_rfq_tag_trigger_value"); ?>' />
					</div> 
				</td> 

			</tr>
			<tr valign="top">  
				<th scope="row">  
					<label for="replace_price">  
						<?php _e('Replace "Free" with "Request Quote" on triggered items? ', 'boopis-woocommerce-rfq'); ?>
					</label>   
				</th>  
				<td>
					<input type="checkbox" id="replace_price" name="replace_price" <?php echo get_option("boopis_rfq_replace_price") ? ' checked="checked"' : ''; ?> />
						<?php _e('Check to replace price', 'boopis-woocommerce-rfq'); ?>
				</td>  
			</tr> 

			<?php
			$value = array(
				'title'    => __( 'RFQ Page', 'woocommerce' ),
				'desc'     => __( 'Page contents:', 'woocommerce' ) . ' [' . apply_filters( 'woocommerce_cart_shortcode_tag', 'woocommerce_cart' ) . ']',
				'id'       => 'boopis_rfq_page_id',
				'type'     => 'single_select_page',
				'default'  => '',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
				'desc_tip' => true,
			);
			
			$args = array(
				'name'             => $value['id'],
				'id'               => $value['id'],
				'sort_column'      => 'menu_order',
				'sort_order'       => 'ASC',
				'show_option_none' => ' ',
				'class'            => $value['class'],
				'echo'             => false,
				'selected'         => absint( get_option( $value['id'] ) )
			);

			if ( isset( $value['args'] ) ) {
				$args = wp_parse_args( $value['args'], $args );
			}

			?>

			<tr valign="top" class="single_select_page">
				<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> </th>
				<td class="forminp">
					<?php echo str_replace(' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'woocommerce' ) .  "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?>
				</td>
			</tr>

		</table>  
		<p>  
			<input type="hidden" name="update_settings" value="Y" />  
			<input type="submit" value="Save settings" class="button-primary"/>  
		</p>  
	</form>

</div>
<?php $fb = class_exists( 'BOOPIS_Formbuilder' ) ? true : false; ?>
<script>
jQuery('[name=quote_trigger]').click(function(){
	if (jQuery(this).attr('checked')) {
		jQuery('.hidden-tag').show();
	} else {
		jQuery('.hidden-tag').hide();   
	}

});
var tag_trigger = '<?php echo get_option('boopis_rfq_quote_trigger'); ?>';
if ( tag_trigger == '' ) {
	jQuery('.hidden-tag').hide();   
}
</script>