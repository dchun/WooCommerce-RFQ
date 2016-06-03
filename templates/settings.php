<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="wrap">
	<div id="feedback"></div>

	<h2><?php _e('Boopis Settings', 'boopis-woocommerce-rfq'); ?></h2>

	<h3>
		<a href='admin.php?page=boopis_rfq_settings'>
			<?php esc_html_e('Click here to edit your settings.', 'boopis-woocommerce-rfq'); ?>
		</a>
	</h3>

	<h3>
		<a href='http://boopis.com/contact' target='_blank'>
			<?php esc_html_e('Need custom work? Let me know!', 'boopis-woocommerce-rfq'); ?>
		</a>
	</h3>
	<h3><a href="http://boopis.com/support" target="_blank"><?php _e('Support', 'boopis-woocommerce-rfq'); ?></a></h3>
	<br>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="PEZPERKEW2XG6">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	<h3><?php _e('Donate to continue supporting development of this plugin.', 'boopis-woocommerce-rfq'); ?></h3>
</div>