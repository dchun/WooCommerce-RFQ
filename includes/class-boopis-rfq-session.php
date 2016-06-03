<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Boopis RFQ Session
 *
 * @version     2.0.0
 * @package     Boopis RFQ
 * @category    Class
 * @author      Boopis Media
 */
class BOOPIS_RFQ_Session {

    /** @var array Contains an array of quote items. */
    public $quote_contents;

    /** @var float The total cost of the quote items. */
    public $quote_contents_total;

    /** @var float quote grand total. */
    public $total;


    /**
     * Constructor for the quote class. Loads options and hooks in the init method.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        add_action( 'init', array( $this, 'init' ), 5 ); // Get quote on init
    }


    /**
     * Loads the quote data from the PHP session during WordPress init and hooks in other methods.
     *
     * @access public
     * @return void
     */
    public function init() {
        $this->boopis_get_quote_from_session();
    }

    /*-----------------------------------------------------------------------------------*/
    /* quote Session Handling */
    /*-----------------------------------------------------------------------------------*/

        /**
         * Get the quote data from the PHP session and store it in class variables.
         *
         * @access public
         * @return void
         */
        public function boopis_get_quote_from_session() {
            global $woocommerce;

         
            // Load the quote
            if ( isset( $woocommerce->session->quote ) && is_array( $woocommerce->session->quote ) ) {
                $quote = $woocommerce->session->quote;

                foreach ( $quote as $key => $values ) {

                    $_product = get_product( $values['variation_id'] ? $values['variation_id'] : $values['product_id'] );

                    if ( ! empty( $_product ) && $_product->exists() && $values['quantity'] > 0 ) {

                        // Put session data into array. Run through filter so other plugins can load their own session data
                        $this->quote_contents[ $key ] = apply_filters( 'woocommerce_get_quote_item_from_session', array(
                            'product_id'    => $values['product_id'],
                            'variation_id'  => $values['variation_id'],
                            'variation'     => $values['variation'],
                            'quantity'      => $values['quantity'],
                            'data'          => $_product
                        ), $values, $key );
                    }
                }
            }

            if ( empty( $this->quote_contents ) || ! is_array( $this->quote_contents ) )
                $this->quote_contents = array();

            if ( sizeof( $this->quote_contents ) > 0 )
                $this->boopis_set_quote_cookies();
            else
                $this->boopis_set_quote_cookies( false );

            // Trigger action
            do_action( 'woocommerce_quote_loaded_from_session', $this );

            // Load totals
            $this->quote_contents_total  = isset( $woocommerce->session->quote_contents_total ) ? $woocommerce->session->quote_contents_total : 0;
            $this->quote_contents_count  = isset( $woocommerce->session->quote_contents_count ) ? $woocommerce->session->quote_contents_count : 0;
            $this->total                = isset( $woocommerce->session->total ) ? $woocommerce->session->total : 0;
            
        }


        /**
         * Sets the php session data for the quote and coupons.
         *
         * @access public
         * @return void
         */
        public function boopis_set_session() {
            global $woocommerce;

            // Set quote and coupon session data
            $quote_session = array();

            if ( $this->quote_contents ) {
                foreach ( $this->quote_contents as $key => $values ) {

                    $quote_session[ $key ] = $values;

                    // Unset product object
                    unset( $quote_session[ $key ]['data'] );
                }
            }

            $woocommerce->session->quote           = $quote_session;

            // Store totals to avoid re-calc on page load
            $woocommerce->session->quote_contents_total  = $this->quote_contents_total;
            $woocommerce->session->quote_contents_count  = $this->quote_contents_count;
            $woocommerce->session->total                = $this->total;

            if ( get_current_user_id() )
                $this->boopis_persistent_quote_update();

            do_action( 'woocommerce_quote_updated' );
        }

        /**
         * Empties the quote and optionally the persistent quote too.
         *
         * @access public
         * @param bool $clear_persistent_quote (default: true)
         * @return void
         */
        public function boopis_empty_quote( $clear_persistent_quote = true ) {
            global $woocommerce;

            $this->quote_contents = array();
            $this->boopis_reset();

            unset( $woocommerce->session->quote );

            if ( $clear_persistent_quote && get_current_user_id() )
                $this->boopis_persistent_quote_destroy();

            do_action( 'woocommerce_quote_emptied' );
        }

    /*-----------------------------------------------------------------------------------*/
    /* Persistent quote handling */
    /*-----------------------------------------------------------------------------------*/

        /**
         * Save the persistent quote when the quote is updated.
         *
         * @access public
         * @return void
         */
        public function boopis_persistent_quote_update() {
            global $woocommerce;

            update_user_meta( get_current_user_id(), '_woocommerce_persistent_quote', array(
                'quote' => $woocommerce->session->quote,
            ) );
        }


        /**
         * Delete the persistent quote permanently.
         *
         * @access public
         * @return void
         */
        public function boopis_persistent_quote_destroy() {
            delete_user_meta( get_current_user_id(), '_woocommerce_persistent_quote' );
        }

    /*-----------------------------------------------------------------------------------*/
    /* quote Data Functions */
    /*-----------------------------------------------------------------------------------*/

        /**
         * Get number of items in the quote.
         *
         * @access public
         * @return int
         */
        public function boopis_get_quote_contents_count() {
            return apply_filters( 'boopis_quote_contents_count', $this->quote_contents_count );
        }

        /**
         * Get quote items quantities - merged so we can do accurate stock checks on items across multiple lines.
         *
         * @access public
         * @return array
         */
        public function boopis_get_quote_item_quantities() {
            $quantities = array();

            foreach ( $this->boopis_get_quote() as $quote_item_key => $values ) {

                if ( $values['data']->managing_stock() ) {

                    if ( $values['variation_id'] > 0 ) {

                        if ( $values['data']->variation_has_stock ) {

                            // Variation has stock levels defined so its handled individually
                            $quantities[ $values['variation_id'] ] = isset( $quantities[ $values['variation_id'] ] ) ? $quantities[ $values['variation_id'] ] + $values['quantity'] : $values['quantity'];

                        } else {

                            // Variation has no stock levels defined so use parents
                            $quantities[ $values['product_id'] ] = isset( $quantities[ $values['product_id'] ] ) ? $quantities[ $values['product_id'] ] + $values['quantity'] : $values['quantity'];

                        }

                    } else {

                        $quantities[ $values['product_id'] ] = isset( $quantities[ $values['product_id'] ] ) ? $quantities[ $values['product_id'] ] + $values['quantity'] : $values['quantity'];

                    }

                }

            }
            return $quantities;
        }

        /**
         * Gets and formats a list of quote item data + variations for display on the frontend.
         *
         * @access public
         * @param array $quote_item
         * @param bool $flat (default: false)
         * @return string
         */
        public function boopis_get_item_data( $quote_item, $flat = false ) {
            global $woocommerce;

            $return = '';
            $has_data = false;

            if ( ! $flat ) $return .= '<dl class="variation">';

            // Variation data
            if ( ! empty( $quote_item['data']->variation_id ) && is_array( $quote_item['variation'] ) ) {

                $variation_list = array();

                foreach ( $quote_item['variation'] as $name => $value ) {

                    if ( ! $value ) continue;

                    // If this is a term slug, get the term's nice name
                    if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $name ) ) ) ) {
                        $term = get_term_by( 'slug', $value, esc_attr( str_replace( 'attribute_', '', $name ) ) );
                        if ( ! is_wp_error( $term ) && $term->name )
                            $value = $term->name;

                    // If this is a custom option slug, get the options name
                    } else {
                        $value = apply_filters( 'woocommerce_variation_option_name', $value );
                    }

                    if ( $flat )
                        $variation_list[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . $value;
                    else
                        $variation_list[] = '<dt>' . wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ':</dt><dd>' . $value . '</dd>';

                }

                if ($flat)
                    $return .= implode( ", \n", $variation_list );
                else
                    $return .= implode( '', $variation_list );

                $has_data = true;

            }

            // Other data - returned as array with name/value values
            $other_data = apply_filters( 'woocommerce_get_item_data', array(), $quote_item );

            if ( $other_data && is_array( $other_data ) && sizeof( $other_data ) > 0 ) {

                $data_list = array();

                foreach ($other_data as $data ) {
                    // Set hidden to true to not display meta on quote.
                    if ( empty( $data['hidden'] ) ) {
                        $display_value = !empty($data['display']) ? $data['display'] : $data['value'];

                        if ($flat)
                            $data_list[] = $data['name'].': '.$display_value;
                        else
                            $data_list[] = '<dt>'.$data['name'].':</dt><dd>'.$display_value.'</dd>';
                    }
                }

                if ($flat)
                    $return .= implode(', ', $data_list);
                else
                    $return .= implode('', $data_list);

                $has_data = true;

            }

            if ( ! $flat )
                $return .= '</dl>';

            if ( $has_data )
                return $return;
        }

        /**
         * Gets the url to the quote page.
         *
         * @return string url to page
         */
        public function boopis_get_quote_url() {
            $quote_page_id = get_option('boopis_rfq_page_id');
            if ( $quote_page_id ) return apply_filters( 'boopis_item_get_quote_url', get_permalink( $quote_page_id ) );
        }

        /**
         * Gets the url to remove an item from the quote.
         *
         * @return string url to page
         */
        public function boopis_get_remove_url( $quote_item_key ) {
            global $woocommerce;
            $quote_page_id = get_option('boopis_rfq_page_id');
            if ($quote_page_id)
                return apply_filters( 'boopis_item_get_remove_url', wp_nonce_url( add_query_arg( 'remove_item', $quote_item_key, get_permalink( $quote_page_id ) ), 'boopis-quote' ) );
        }

        /**
         * Returns the contents of the quote in an array.
         *
         * @return array contents of the quote
         */
        public function boopis_get_quote() {
            return array_filter( (array) $this->quote_contents );
        }

    /*-----------------------------------------------------------------------------------*/
    /* Add to quote handling */
    /*-----------------------------------------------------------------------------------*/

        /**
         * Check if product is in the quote and return quote item key.
         *
         * quote item key will be unique based on the item and its properties, such as variations.
         *
         * @param mixed id of product to find in the quote
         * @return string quote item key
         */
        public function boopis_find_product_in_quote( $quote_id = false ) {
            if ( $quote_id !== false )
                if( is_array( $this->quote_contents ) )
                    foreach ( $this->quote_contents as $quote_item_key => $quote_item )
                        if ( $quote_item_key == $quote_id )
                            return $quote_item_key;
        }

        /**
         * Generate a unique ID for the quote item being added.
         *
         * @param int $product_id - id of the product the key is being generated for
         * @param int $variation_id of the product the key is being generated for
         * @param array $variation data for the quote item
         * @param array $quote_item_data other quote item data passed which affects this items uniqueness in the quote
         * @return string quote item key
         */
        public function boopis_generate_quote_id( $product_id, $variation_id = '', $variation = '', $quote_item_data = array() ) {

            $id_parts = array( $product_id );

            if ( $variation_id ) $id_parts[] = $variation_id;

            if ( is_array( $variation ) ) {
                $variation_key = '';
                foreach ( $variation as $key => $value ) {
                    $variation_key .= trim( $key ) . trim( $value );
                }
                $id_parts[] = $variation_key;
            }

            if ( is_array( $quote_item_data ) && ! empty( $quote_item_data ) ) {
                $quote_item_data_key = '';
                foreach ( $quote_item_data as $key => $value ) {
                    if ( is_array( $value ) ) $value = http_build_query( $value );
                    $quote_item_data_key .= trim($key) . trim($value);
                }
                $id_parts[] = $quote_item_data_key;
            }

            return md5( implode( '_', $id_parts ) );
        }

        /**
         * Add a product to the quote.
         *
         * @param string $product_id contains the id of the product to add to the quote
         * @param string $quantity contains the quantity of the item to add
         * @param int $variation_id
         * @param array $variation attribute values
         * @param array $quote_item_data extra quote item data we want to pass into the item
         * @return bool
         */
        public function boopis_add_to_quote( $product_id, $quantity = 1, $variation_id = '', $variation = '', $quote_item_data = array() ) {
            global $woocommerce;

            if ( $quantity <= 0 ) return false;

            // Load quote item data - may be added by other plugins
            $quote_item_data = (array) apply_filters( 'boopis_add_quote_item_data', $quote_item_data, $product_id, $variation_id );

            // Generate a ID based on product ID, variation ID, variation data, and other quote item data
            $quote_id = $this->boopis_generate_quote_id( $product_id, $variation_id, $variation, $quote_item_data );

            // See if this product and its options is already in the quote
            $quote_item_key = $this->boopis_find_product_in_quote( $quote_id );

            $product_data = get_product( $variation_id ? $variation_id : $product_id );

            if ( ! $product_data )
                return false;

            // Force quantity to 1 if sold individually
            if ( $product_data->is_sold_individually() )
                $quantity = 1;

            // Downloadable/virtual qty check
            if ( $product_data->is_sold_individually() ) {
                $in_quote_quantity = $quote_item_key ? $this->quote_contents[$quote_item_key]['quantity'] : 0;

                // If its greater than 0, its already in the quote
                if ( $in_quote_quantity > 0 ) {
                    $woocommerce->add_error( sprintf('<a href="%s" class="button">%s</a> %s', get_permalink(woocommerce_get_page_id('quote')), __( 'View quote &rarr;', 'boopis-rfq' ), __( 'You already have this item in your quote.', 'boopis-rfq' ) ) );
                    return false;
                }
            }

            // If quote_item_key is set, the item is already in the quote
            if ( $quote_item_key ) {

                $new_quantity = $quantity + $this->quote_contents[$quote_item_key]['quantity'];

                $this->boopis_set_quantity( $quote_item_key, $new_quantity, false );

            } else {

                $quote_item_key = $quote_id;

                // Add item after merging with $quote_item_data - hook to allow plugins to modify quote item
                $this->quote_contents[$quote_item_key] = apply_filters( 'boopis_add_quote_item', array_merge( $quote_item_data, array(
                    'product_id'    => $product_id,
                    'variation_id'  => $variation_id,
                    'variation'     => $variation,
                    'quantity'      => $quantity,
                    'data'          => $product_data
                ) ), $quote_item_key );

            }

            do_action( 'boopis_add_to_quote', $quote_item_key, $product_id, $quantity, $variation_id, $variation, $quote_item_data );

            $this->boopis_set_quote_cookies();
            $this->boopis_calculate_totals();

            return true;
        }

        /**
         * Set the quantity for an item in the quote.
         *
         * @param string    quote_item_key   contains the id of the quote item
         * @param string    quantity        contains the quantity of the item
         * @param boolean   $refresh_totals whether or not to calculate totals after setting the new qty
         */
        public function boopis_set_quantity( $quote_item_key, $quantity = 1, $refresh_totals = true ) {

            if ( $quantity == 0 || $quantity < 0 ) {
                do_action( 'boopis_before_quote_item_quantity_zero', $quote_item_key );
                unset( $this->quote_contents[$quote_item_key] );
            } else {
                $this->quote_contents[$quote_item_key]['quantity'] = $quantity;
                do_action( 'boopis_after_quote_item_quantity_update', $quote_item_key, $quantity );
            }

            if ( $refresh_totals )
                $this->boopis_calculate_totals();
        }

        /**
         * Set quote hash cookie and items in quote.
         *
         * @access private
         * @param bool $set (default: true)
         * @return void
         */
        private function boopis_set_quote_cookies( $set = true ) {
            if ( ! headers_sent() ) {
                if ( $set ) {
                    setcookie( "boopis_items_in_quote", "1", 0, COOKIEPATH, COOKIE_DOMAIN, false );
                    setcookie( "boopis_quote_hash", md5( json_encode( $this->boopis_get_quote() ) ), 0, COOKIEPATH, COOKIE_DOMAIN, false );
                } else {
                    setcookie( "boopis_items_in_quote", "0", time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
                    setcookie( "boopis_quote_hash", "0", time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
                }
            }

            do_action( 'woocommerce_set_cart_cookies', $set );
        }

    /*-----------------------------------------------------------------------------------*/
    /* quote Calculation Functions */
    /*-----------------------------------------------------------------------------------*/

        /**
         * Reset quote totals and clear sessions.
         *
         * @access private
         * @return void
         */
        private function boopis_reset() {
            global $woocommerce;

            $this->total = $this->quote_contents_total = $this->quote_contents_weight = $this->quote_contents_count = $this->quote_contents_tax = $this->tax_total = $this->shipping_tax_total = $this->subtotal = $this->subtotal_ex_tax = $this->discount_total = $this->discount_quote = $this->shipping_total = $this->fee_total = 0;
            $this->shipping_taxes = $this->taxes = $this->coupon_discount_amounts = array();

            unset( $woocommerce->session->quote_contents_total, $woocommerce->session->quote_contents_weight, $woocommerce->session->quote_contents_count, $woocommerce->session->quote_contents_tax, $woocommerce->session->total, $woocommerce->session->subtotal, $woocommerce->session->subtotal_ex_tax, $woocommerce->session->tax_total, $woocommerce->session->taxes, $woocommerce->session->shipping_taxes, $woocommerce->session->discount_quote, $woocommerce->session->discount_total, $woocommerce->session->shipping_total, $woocommerce->session->shipping_tax_total, $woocommerce->session->shipping_label );
        }

        /**
         * Calculate totals for the items in the quote.
         *
         * @access public
         */
        public function boopis_calculate_totals() {
            global $woocommerce;

            $this->boopis_reset();

            do_action( 'boopis_before_calculate_totals', $this );

            // Get count of all items + weights + subtotal (we may need this for discounts)
            if ( sizeof( $this->quote_contents ) > 0 ) {
                foreach ( $this->quote_contents as $quote_item_key => $values ) {

                    $_product = $values['data'];

                    $this->quote_contents_count  = $this->quote_contents_count + $values['quantity'];

                }
            }
            
            $this->boopis_set_session();
        }

}