<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form = new BOOPIS_RFQ_Form();
global $woocommerce;
$rfq_page = get_permalink( get_option('boois_rfq_page_id') );

?>
<div class="woocommerce">
<?php
wc_print_notices();

if ( ! is_user_logged_in() ) {

	$info_message  = apply_filters( 'woocommerce_checkout_login_message', __( 'Returning customer?', 'woocommerce' ) );
	$info_message .= ' <a href="#" class="showlogin">' . __( 'Click here to login', 'woocommerce' ) . '</a>';
	wc_print_notice( $info_message, 'notice' );

	woocommerce_login_form(
		array(
			'message'  => __( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer.', 'boopis-woocommerce-rfq' ),
			'redirect' => $rfq_page,
			'hidden'   => true
			)
		);
}
?>

<?php if ( sizeof( $woocommerce->quote->boopis_get_quote() ) > 0 ) : ?>

	<h3 id="order_review_heading"><?php echo apply_filters('boopis_rfq_page_item_title', __( 'Request Items', 'boopis-woocommerce-rfq' ) ); ?></h3>

	<div id="order_review" class="woocommerce-checkout-review-order">

		<?php wc_get_template('form/items.php'); ?>

	</div>

	<form method="post" class="rfq woocommerce-checkout" action="<?php echo esc_url($rfq_page); ?>" enctype="multipart/form-data">

			<div id="customer_details">

				<div>
					<div class="woocommerce-billing-fields">
						<h3><?php echo apply_filters('boopis_rfq_page_details_title', __( 'Request Details', 'boopis-woocommerce-rfq' ) ); ?></h3>

						
						<?php foreach ( $form->form_fields['billing'] as $key => $field ) : ?>

							<?php woocommerce_form_field( $key, $field, $form->get_value( $key ) ); ?>

						<?php endforeach; ?>

						<?php foreach ( $form->form_fields['order'] as $key => $field ) : ?>

							<?php woocommerce_form_field( $key, $field, $form->get_value( $key ) ); ?>

						<?php endforeach; ?>

						<?php do_action( 'boopis_rfq_after_order_notes', $form ); ?>

						<?php if ( ! is_user_logged_in() ) : ?>

							<p class="form-row form-row-wide create-account">
								<input class="input-checkbox" id="createaccount" <?php checked( ( true === $form->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" class="checkbox"><?php _e( 'Create an account?', 'woocommerce' ); ?></label>
							</p>

							<?php if ( ! empty( $form->form_fields['account'] ) ) : ?>

								<div class="create-account">

									<p><?php _e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'woocommerce' ); ?></p>

									<?php foreach ( $form->form_fields['account'] as $key => $field ) : ?>

										<?php woocommerce_form_field( $key, $field, $form->get_value( $key ) ); ?>

									<?php endforeach; ?>

									<div class="clear"></div>

								</div>

							<?php endif; ?>

						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="form-row place-order">
				<?php wp_nonce_field( 'boopis-rfq-process-form' ); ?>
				<input type="hidden" name="action" value="boopis_rfq_process_form" />
				<input type="submit" class="button alt" style="float:none;width:100%" value='<?php _e("Submit Request","boopis-woocommerce-rfq"); ?>' />
			</div>
	</form>

<?php else: ?>
	<h3 id="order_review_heading">
		<?php echo apply_filters( 'boopis_rfq_page_empty_text', __( 'Your RFQ is empty.', 'boopis-woocommerce-rfq' ) ); ?>
	</h3>
<?php endif; ?>

</div><!-- end woocommerce class div -->
<?php 
	wp_enqueue_script( 'boopis-rfq-form', BOOPIS_RFQ_URL . 'assets/js/frontend/form.js', array('jquery'), '1.0.0', true );

	$params = array(
		'ajax_url'                  => WC()->ajax_url(),
		'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
		'action'              			=> 'boopis_rfq_process_form',
		'debug_mode'                => defined('WP_DEBUG') && WP_DEBUG,
		'i18n_rfq_error'       			=> esc_attr__( 'Error processing request. Please try again.', 'boopis-woocommerce-rfq' ),
	);

	wp_localize_script( 'boopis-rfq-form', 'boopis_rfq_params', $params );

?>