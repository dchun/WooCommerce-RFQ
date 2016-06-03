<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BOOPIS_RFQ_Admin' ) ) {

	class BOOPIS_RFQ_Admin {

		public function __construct() {

			// Add new order states
			add_action( 'init', array($this, 'register_custom_post_status'), 10 );

			// Add order states to order admin dropdown view
			add_filter( 'wc_order_statuses', array($this, 'custom_wc_order_statuses') );

			add_filter( 'wc_order_is_editable', array($this, 'add_custom_quotes_to_editable'), 10, 2 );
			
			add_filter( 'woocommerce_email_actions', array($this, 'quote_email_actions'), 10 );
		
			add_action( 'add_meta_boxes', array($this, 'add_quote_meta_box') );

			add_action( 'save_post', array($this, 'boopis_rfq_save_proposal') );

			add_action( 'woocommerce_order_actions', array($this, 'add_order_meta_box_actions') );

			add_action( 'woocommerce_order_action_boopis_rfq_send_quote', array($this, 'process_order_meta_box_actions') );
			
			add_filter( 'woocommerce_locate_template', array($this, 'boopis_rfq_locate_template'), 10, 3 );
			add_filter( 'woocommerce_locate_core_template', array($this, 'boopis_rfq_locate_template'), 10, 3 );
			add_filter( 'woocommerce_valid_order_statuses_for_payment', array($this, 'add_pending_quote_to_valid_order_statuses_for_payment'), 10, 2 );
			add_filter( 'the_title', array($this, 'change_title_based_on_endpoints'), 10, 2 );

		}

		public function register_custom_post_status() {
			register_post_status( 'wc-new-quote', array(
				'label'                     => _x( 'New Quote', 'Order status', 'boopis-woocommerce-rfq' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'New Quote <span class="count">(%s)</span>', 'Back Order <span class="count">(%s)</span>', 'woocommerce' )
			) );
			register_post_status( 'wc-pending-quote', array(
				'label'                     => _x( 'Pending Quote', 'Order status', 'boopis-woocommerce-rfq' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Pending Quote <span class="count">(%s)</span>', 'Back Order <span class="count">(%s)</span>', 'woocommerce' )
			) );
			register_post_status( 'wc-expired-quote', array(
				'label'                     => _x( 'Expired Quote', 'Order status', 'boopis-woocommerce-rfq' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Expired Quote <span class="count">(%s)</span>', 'Back Order <span class="count">(%s)</span>', 'woocommerce' )
			) );
			register_post_status( 'wc-failed-quote', array(
				'label'                     => _x( 'Failed Quote', 'Order status', 'boopis-woocommerce-rfq' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Failed Quote <span class="count">(%s)</span>', 'Back Order <span class="count">(%s)</span>', 'woocommerce' )
			) );
		}

		public function custom_wc_order_statuses( $order_statuses ) {
			$order_statuses['wc-new-quote'] 		= _x( 'New Quote', 'Order status', 'boopis-woocommerce-rfq' );
			$order_statuses['wc-pending-quote'] = _x( 'Pending Quote', 'Order status', 'boopis-woocommerce-rfq' );
			$order_statuses['wc-expired-quote'] = _x( 'Expired Quote', 'Order status', 'boopis-woocommerce-rfq' );
			$order_statuses['wc-failed-quote'] 	= _x( 'Failed Quote', 'Order status', 'boopis-woocommerce-rfq' );
			return $order_statuses;
		}

		public function add_custom_quotes_to_editable( $editable, $order ) {
			// list the slugs of all order statuses that should be editable.
			// Note 'pending', 'on-hold', 'auto-draft' are editable by default
			$editable_custom_statuses = array( 'new-quote', 'pending-quote', 'expired-quote', 'failed-quote');

			if ( in_array( $order->get_status(), $editable_custom_statuses ) ) {
				$editable = true;
			}

			return $editable;
		}

		public function quote_email_actions( $actions ){
			$actions[] = "woocommerce_order_status_new-quote_to_pending-quote";
			$actions[] = "woocommerce_order_status_pending-quote_to_processing";
			$actions[] = "woocommerce_order_status_pending-quote_to_expired-quote";
			$actions[] = "woocommerce_order_status_pending-quote_to_failed-quote";
			$actions[] = "woocommerce_order_status_expired-quote_to_pending-quote";
			$actions[] = "woocommerce_order_status_failed-quote_to_pending-quote";
			return $actions;
		}

		public function add_quote_meta_box(){
			global $post;
			if (strpos($post->post_status, 'quote') !== false) {
				add_meta_box('boopis-rfq-order-proposal', __( 'Proposal Details' ) . wc_help_tip( __( "Note: Don't forget to change the status to pending when you save this proposal. An email will be sent upon saving this order with the prices and terms that have been added (You can disable this under the email settings tab labled 'Pending Quote').", 'boopis-woocommerce-rfq' ) ), array($this,'proposal_fields'), 'shop_order', 'normal', 'high');
			}
		}

		public function proposal_fields( $post ){
			wp_nonce_field( 'boopis_rfq_save_proposal', 'boopis_rfq_nonce' );

			woocommerce_wp_textarea_input(
				array(
					'id' => '_boopis_rfq_terms',
					'class' => '',
					'label' => __('Proposal Terms: ', 'boopis-woocommerce-rfq'),
					// 'desc_tip' => true,
					'description' => __( 'Terms of proposal will be added to quotation.', 'boopis-woocommerce-rfq' )
				)
			);

			woocommerce_wp_text_input(
				array(
					'id' => '_boopis_rfq_expiration_date',
					'class' => 'date',
					'label' => __('Expiration Date: ', 'boopis-woocommerce-rfq'),
					// 'desc_tip' => true,
					'description' => __( 'Optionally set a date at which this proposal will expire.', 'boopis-woocommerce-rfq' ),
					'custom_attributes' => array(
						'pattern' 	=> '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])') 
				)
			);

			// wp_enqueue_script('jquery');
			// wp_enqueue_script('jquery-ui-core');
			// wp_enqueue_script('jquery-ui-datepicker');
			// wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('.date').datepicker({
					dateFormat: 'yy-mm-dd',
					minDate: 0
				});
			});
			</script>
			<?php
		}

		public function boopis_rfq_save_proposal( $post_id ) {

			// Check if nonce is set
			if ( ! isset( $_POST['boopis_rfq_nonce'] ) ) {
				return $post_id;
			}

			if ( ! wp_verify_nonce( $_POST['boopis_rfq_nonce'], 'boopis_rfq_save_proposal' ) ) {
				return $post_id;
			}

			$order = wc_get_order( $post_id );

			$terms = sanitize_text_field( $_POST['_boopis_rfq_terms'] );
			$exp_date = preg_replace("([^0-9-])", "", $_POST['_boopis_rfq_expiration_date']);
			update_post_meta( $post_id, '_boopis_rfq_terms', $terms );
			update_post_meta( $post_id, '_boopis_rfq_expiration_date', $exp_date );

			// Add note when updating quote
			// $order->add_order_note( sprintf( __( 'Terms set as %s, expiring %s.', 'boopis-woocommerce-rfq' ), $terms, $exp_date ), false, true );

		}

		public function add_order_meta_box_actions( $actions ) {
			$actions['boopis_rfq_send_quote'] = __( 'Send Quotation To Customer', 'boopis-woocommerce-rfq' );
			return $actions;
		}

		public function process_order_meta_box_actions( $order ) {
			boopis_rfq_send_email('customer_pending_quote', $order);
		}

		public function boopis_rfq_locate_template( $template, $template_name, $template_path ) {

			$_template = $template;

			if ( ! $template_path ) {
				$template_path = WC()->template_url;
			} 

			$plugin_path  = BOOPIS_RFQ_PATH . '/templates/';

  		// Look within passed path within the theme - this is priority
			$template = locate_template(
				array(
					$template_path . $template_name,
					$template_name
					)
				);

  		// Modification: Get the template from this plugin, if it exists
			if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}

  		// Use default template
			if ( ! $template ) {
				$template = $_template;
			}

			return $template;
		}

		public function add_pending_quote_to_valid_order_statuses_for_payment( $statuses, $order ) {
			$statuses[] = 'pending-quote';
			return $statuses;
		}

		public function change_title_based_on_endpoints( $title, $id = null ) {

	    if ( $id == get_option('boopis_rfq_page_id') ) {
	        if (isset($_GET['rfq-received'])) {
						$title = __( 'RFQ Received', 'boopis-woocommerce-rfq' );
	        } else if (isset($_GET['proposal'])) {
						$title = __( 'Proposal', 'boopis-woocommerce-rfq' );
	        }

	        remove_filter( 'the_title', 'change_title_based_on_endpoints' );
	    }

	    return $title;
		}
	} // End Class

}

return new BOOPIS_RFQ_Admin();