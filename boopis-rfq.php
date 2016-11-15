<?php
/*
Plugin Name: Boopis WooCommerce RFQ
Plugin URI: https://boopis.com/products/1-wordpress-woocommerce-request-for-quotation
Description: Replaces products with a price of zero to an open form for inquiry. Also use tags. Create proposals and convert to sales for large ticket items.
Version: 3.0.3
Author: Boopis Media
Author URI: http://boopis.com/
Text Domain: boopis-woocommerce-rfq

    Copyright: © 2016 Boopis Media (email : info@boopis.com)
    License: GNU General Public License v3.0
    License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    if ( ! class_exists( 'BOOPIS_RFQ' ) ) {

        define('BOOPIS_RFQ_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));
        define( 'BOOPIS_RFQ_URL', plugin_dir_url( __FILE__ ) );

        register_activation_hook( __FILE__, 'boopis_add_rfq_page' ); 

        load_plugin_textdomain( 'boopis-woocommerce-rfq', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        
        add_shortcode( 'boopis_rfq', 'boopis_rfq_handler' );

        function boopis_add_rfq_page() {
            global $wpdb;

            $option_value = get_option( 'boois_rfq_page_id' );

            if ( $option_value > 0 && get_post( $option_value ) )
              return;

            $page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'rfq' LIMIT 1;" );
            if ( $page_found ) :
                if ( ! $option_value )
                    update_option( 'boopis_rfq_page_id', $page_found );
                return;
            endif;

            $page_data = array(
                'post_status'     => 'publish',
                'post_type'     => 'page',
                'post_author'     => 1,
                'post_name'     => esc_sql( _x( 'rfq', 'page_slug', 'boopis-woocommerce-rfq' ) ),
                'post_title'    => __( 'RFQ', 'boopis-woocommerce-rfq' ),
                'post_content'    => '[boopis_rfq]',
                'post_parent'     => 0,
                'comment_status'  => 'closed'
                );

            $page_id = wp_insert_post( $page_data );

            update_option( 'boopis_rfq_page_id', $page_id );
        }


        function boopis_rfq_handler ( $atts ) {
            global $wp;
            
            if (isset($_GET['proposal'])) {
                wc_get_template( 'templates/form/proposal.php', array(), '', BOOPIS_RFQ_PATH . '/' );
            } elseif (isset($_GET['rfq-received'])) {
                wc_get_template( 'templates/form/thankyou.php', array(), '', BOOPIS_RFQ_PATH . '/' );
            } else {
                wc_get_template( 'templates/form/main.php', array(), '', BOOPIS_RFQ_PATH . '/' );
            }
        }

        function boopis_rfq_update_notice() {
            $info = __( 'ATTENTION! Plugin has been merged with premium MPRFQ Plugin. If you have the plugin installed, deactivate and delete it first before updating. Also, the formbuilder plugin is no longer compatible.', 'boopis-woocommerce-rfq' );
            echo '<div class="wc_plugin_upgrade_notice">' . strip_tags( $info, '<br><a><b><i><div>' ) . '</span>';
        } 

        if(is_admin()) {
            add_action( 'in_plugin_update_message-' . plugin_basename(__FILE__), 'boopis_rfq_update_notice' );
        }

        class BOOPIS_RFQ {
            public function __construct() {
                add_action( 'plugins_loaded', array( &$this, 'includes' ) );
                add_filter( 'woocommerce_email_classes', array( &$this, 'add_email_classes' ) );
                add_action( 'admin_menu', array( &$this, 'add_menu' ) );
                add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
                add_action( 'wp_enqueue_scripts', array( &$this,'scripts' ) );
            }

            public function includes() {
                require_once( 'includes/boopis-rfq-order-functions.php' );
                require_once( 'includes/class-boopis-rfq-session.php' );
                require_once( 'includes/class-boopis-rfq-emails.php' );
                require_once( 'includes/class-boopis-rfq-admin.php' );
                require_once( 'includes/class-boopis-rfq-form.php' );
                require_once( 'includes/class-boopis-rfq-front.php' );
            }

            public function add_email_classes( $email_classes ) {            
                $email_classes['BOOPIS_RFQ_New_Quote'] = include( 'includes/emails/class-boopis-rfq-email-new-quote.php' );  
                $email_classes['BOOPIS_RFQ_Customer_New_Quote'] = include( 'includes/emails/class-boopis-rfq-customer-email-new-quote.php' );
                $email_classes['BOOPIS_RFQ_Customer_Pending_Quote'] = include( 'includes/emails/class-boopis-rfq-customer-email-pending-quote.php' );
                $email_classes['BOOPIS_RFQ_Customer_Expired_Quote'] = include( 'includes/emails/class-boopis-rfq-customer-email-expired-quote.php' );
                $email_classes['BOOPIS_RFQ_Failed_Quote'] = include( 'includes/emails/class-boopis-rfq-email-failed-quote.php' );
                return $email_classes;
            }

            public function add_menu() {
                add_menu_page('Boopis Settings', 'Boopis', 'manage_options', 'boopis_settings', array(&$this, 'plugin_settings_page')); 
                add_submenu_page('boopis_settings', 'Boopis RFQ Settings', 'Boopis RFQ', 'manage_options', 'boopis_rfq_settings', array(&$this, 'plugin_settings_subpage')); 
            }

            public function plugin_settings_page() { 
                if(!current_user_can('manage_options')) { 
                    wp_die(__('You do not have sufficient permissions to access this page.', 'boopis-woocommerce-rfq')); 
                }
                include(sprintf("%s/templates/settings.php", dirname(__FILE__))); 
            }

            public function plugin_settings_subpage() { 
                if(!current_user_can('manage_options')) { 
                    wp_die(__('You do not have sufficient permissions to access this page.', 'boopis-woocommerce-rfq')); 
                }
                include(sprintf("%s/templates/rfq_settings.php", dirname(__FILE__))); 
            }

            public function plugin_action_links( $links, $file ) {
                if ( $file == plugin_basename( __FILE__ ) )
                    $links[] = '<a href="admin.php?page=boopis_rfq_settings">' . __( 'Settings' , 'boopis-woocommerce-rfq') . '</a>';

                return $links;
            }

            public function scripts() {
                if( is_woocommerce() ) {
                    wp_enqueue_style( 'boopis-rfq', BOOPIS_RFQ_URL . 'assets/css/styles.css' );
                    wp_enqueue_script( 'boopis-rfq-atq', BOOPIS_RFQ_URL . 'assets/js/frontend/add-to-quote.js', array('jquery'), '1.0.1', true );
                }
            }

        }

        // finally instantiate our plugin class and add it to the set of globals
        $GLOBALS['boopis_rfq'] = new BOOPIS_RFQ();
    }

}