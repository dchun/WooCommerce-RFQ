<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BOOPIS_RFQ_Front' ) ) {

	class BOOPIS_RFQ_Front {

		public function __construct() {

			// prices with value
			add_filter( 'woocommerce_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_sale_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_get_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_variation_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_get_variation_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_variation_sale_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_variable_sale_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_variable_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 


			// prices with no value or zero
			add_filter( 'woocommerce_empty_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_variable_empty_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 
			add_filter( 'woocommerce_variation_empty_price_html', array($this, 'boopis_price_replace'), 1, 2 ); 

			// Template function overrides < WooCommerce 2.1.0
			// add_filter( 'woocommerce_variable_free_price_html', 'boopis_price_replace' ); 
			// add_filter( 'woocommerce_variable_free_sale_price_html', 'boopis_price_replace' ); 

			add_filter( 'woocommerce_free_price_html', array($this,'boopis_price_replace'), 1, 2 );
			add_filter( 'woocommerce_free_sale_price_html', array($this,'boopis_price_replace'), 1, 2 );
			add_filter( 'woocommerce_variation_free_price_html', array($this,'boopis_price_replace'), 1, 2 );
			add_filter( 'woocommerce_grouped_price_html', array($this,'boopis_price_replace'), 1, 2 );

			//add to cart button
			add_filter( 'add_to_cart_text', array($this,'boopis_add_to_quote_text') );
			add_filter( 'add_to_cart_url', array($this,'boopis_add_to_quote_url') );
			add_filter( 'add_to_cart_class', array($this,'boopis_add_to_quote_class') );
			add_action( 'woocommerce_before_add_to_cart_button', array($this,'boopis_before_add_to_quote_button') );
			add_action( 'woocommerce_after_add_to_cart_button', array($this,'boopis_after_add_to_quote_button') );
			add_action( 'woocommerce_before_add_to_cart_form', array($this,'boopis_before_add_to_quote_form') );

	  	// Template Function Overrides > WooCommerce 2.1.0
			add_filter( 'woocommerce_loop_add_to_cart_link', array($this,'boopis_loop_add_to_quote_link') );

			// For multiple products
			add_action( 'before_woocommerce_init', array($this,'boopis_rfq_init') );
			add_filter( 'woocommerce_params', array($this,'boopis_rfq_params') );
			add_action( 'wp_ajax_woocommerce_add_to_quote', array($this,'boopis_rfq_ajax_add_to_quote'));
			add_action( 'wp_ajax_nopriv_woocommerce_add_to_quote', array($this,'boopis_rfq_ajax_add_to_quote'));
			add_action( 'wp_ajax_boopis_rfq_process_form', array($this,'boopis_rfq_process_form'));
			add_action( 'wp_ajax_nopriv_boopis_rfq_process_form', array($this,'boopis_rfq_process_form'));
			add_action( 'wp_ajax_boopis_rfq_process_proposal', array($this,'boopis_rfq_process_proposal'));
			add_action( 'wp_ajax_nopriv_boopis_rfq_process_proposal', array($this,'boopis_rfq_process_proposal'));
			add_action( 'init', array($this,'boopis_rfq_update_quote_action') );
			add_action( 'init', array($this,'boopis_rfq_add_to_quote_action') );

		}

		public function searchForTag($tag, $array) {
			if ($array) {
				foreach ($array as $term) {
					if ($term->name === $tag) {
						return true;
					}
				}
			}
			return null;
		}

		public function boopis_price_replace( $price, $_product ) {
			global $product;

			if ( get_option("boopis_rfq_replace_price") == true ) {
		    	if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) ) {
					return __( 'Request Quote', 'boopis-woocommerce-rfq' );
		    	} elseif ( get_option('boopis_rfq_quote_trigger') == false && $_product->get_price() == 0 ) {
		    		return __( 'Request Quote', 'boopis-woocommerce-rfq' );
		    	}
			}
			return $price;
		}

		public function boopis_add_to_quote_text( $link ) {
			global $product;

			if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) ) {
				$link = __( 'Inquire', 'boopis-woocommerce-rfq' );
			} elseif ( get_option('boopis_rfq_quote_trigger') == false && $product->get_price() == 0 ) {
				$link = __( 'Inquire', 'boopis-woocommerce-rfq' );
			}

			return $link;
		}

		public function boopis_add_to_quote_url( $link ) {
			global $product;

			if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) || ( get_option('boopis_rfq_quote_trigger') == false && $product->get_price() == 0 ) ) {
				$link = esc_url( remove_query_arg( 'added-to-quote', add_query_arg( 'add-to-quote', $product->id ) ) );
			}

			return $link;
		}

		public function boopis_add_to_quote_class( $link ) {
			global $product;

			if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) || ( get_option('boopis_rfq_quote_trigger') == false && $product->get_price() == 0 ) ) {
				$link = 'add_to_quote_button';
			}

			return $link;
		}

		public function boopis_before_add_to_quote_form() {
			echo "<script type=\"text/javascript\">" . "\r\n";
			echo "function changeMethod() {" . "\r\n";
			echo "$(\".cart\").attr(\"method\", \"get\");" . "\r\n";
			echo "}" . "\r\n";
			echo "</script>";
		}

		public function boopis_before_add_to_quote_button() {
			global $product;
			global $post;

			if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) || ( get_option('boopis_rfq_quote_trigger') == false && $product->get_price() == 0 ) ) {
				if( $product->is_type( 'simple' ) ) {

					if ( ! $product->is_sold_individually() ) {
						woocommerce_quantity_input( array(
							'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
							'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
							) );
					}
					echo "<input type=\"hidden\" name=\"add-to-quote\" value=\"". esc_attr( $product->id ) . "\" />";
					echo "<button type=\"submit\" class=\"single_add_to_quote_button button alt\">" . apply_filters('single_add_to_cart_text', __( 'Inquire', 'boopis-woocommerce-rfq' ), $product->product_type) . "</button>";

				} elseif( $product->is_type( 'variable' ) ) {

					echo "<div class=\"single_variation_wrap\" style=\"display:none;\">";
					echo "<div class=\"single_variation\"></div>";
					echo "<div class=\"variations_button\">";
					echo "<input type=\"hidden\" name=\"variation_id\" value=\"\" />";
					woocommerce_quantity_input();
			    echo "<button type=\"submit\" class=\"single_add_to_quote_button button alt\">" . apply_filters('single_add_to_cart_text', __( 'Inquire', 'boopis-woocommerce-rfq' ), $product->product_type) . "</button>";
					echo "</div>";
					echo "</div>";
					echo "<div>";
					echo "<input type=\"hidden\" name=\"add-to-quote\" value=\"" . $product->id . "\" />";
					echo "<input type=\"hidden\" name=\"product_id\" value=\"" . esc_attr( $post->ID ) . "\" />";
					echo "</div>";

				} elseif( $product->is_type( 'grouped' ) ) {

		  		echo "<button type=\"submit\" class=\"single_add_to_quote_button button alt\">" . apply_filters('single_add_to_cart_text', __( 'Inquire', 'boopis-woocommerce-rfq' ), $product->product_type) . "</button>";

				} elseif( $product->is_type( 'external' ) ) {

					echo "<p class=\"quote\"><a href=\"" . esc_url( $product_url ) . "\" rel=\"nofollow\" class=\"single_add_to_quote_button button alt\">" . apply_filters('single_add_to_cart_text', $button_text, 'external') . "</a></p>";

				}

				echo "<!--";
			}

		} 

		public function boopis_after_add_to_quote_button() {
			global $product;

			if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) || ( get_option('boopis_rfq_quote_trigger') == false && $product->get_price() == 0 ) ) {
				echo "-->";
			}
		}

		public function boopis_loop_add_to_quote_link( $link ) {
			global $product;

			if ( get_option('boopis_rfq_quote_trigger') == true && $this->searchForTag(get_option('boopis_rfq_tag_trigger_value'), get_the_terms($product->id, 'product_tag')) || ( get_option('boopis_rfq_quote_trigger') == false && $product->get_price() == 0 ) ) {

				if ( $product->product_type == 'variable' ) {
					$link = '<a href="'.esc_url( $product->add_to_cart_url() ).'" rel="nofollow" data-product_id="'.$product->id.'" data-product_sku="'.$product->get_sku().'" class="button add_to_quote_button product_type_'.$product->product_type.'">' . esc_html( $product->add_to_cart_text() ) .'</a>';
				} else {
					$link = '<a href="'.esc_url( remove_query_arg( 'added-to-quote', add_query_arg( 'add-to-quote', $product->id ) ) ).'" rel="nofollow" data-product_id="'.$product->id.'" data-product_sku="'.$product->get_sku().'" class="button add_to_quote_button product_type_'.$product->product_type.'">' . __( "Inquire", "boopis-woocommerce-rfq" ) .'</a>';
				}


			}

			return $link;
		}

		public function boopis_rfq_init() {
		  global $woocommerce;
		  $woocommerce->quote = new BOOPIS_RFQ_Session();
		}

		public function boopis_rfq_params( $params ) {
		  $params['i18n_view_quote' ] = esc_attr__( 'View Quote &rarr;', 'boopis-woocommerce-rfq' );
		  $params['quote_url' ] = get_permalink( get_option('boopis_rfq_page_id') );
		  return $params;
		}

		public function boopis_rfq_process_form() {
			$form = new BOOPIS_RFQ_Form();
			return $form->process_form();
		}

		public function boopis_rfq_process_proposal() {
			try {
				if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'boopis-rfq-process-proposal' ) ) {
					throw new Exception( __( 'We were unable to process your request, please try again.', 'boopis-woocommerce-rfq' ) );
				}
		
				$accepted = isset( $_POST['decision'] ) && ( $_POST['decision'] == 'accept' ) ? 1 : 0;
				$declined = isset( $_POST['decision'] ) && ( $_POST['decision'] == 'decline' ) ? 1 : 0;
				$referer = wp_get_referer();

				$params = strstr($referer, 'proposal');

				parse_str($params, $referer_params);

				// $debug_export = var_export($referer_params, true);

				$order_id = $referer_params['proposal'];
                $order_key = $referer_params['key'];

                $order = wc_get_order($order_id);

				if ( $order->order_key != $order_key ) {
					throw new Exception( __( 'Your actions are invalid, please try again.', 'boopis-woocommerce-rfq' ) );
				} else {

					if ( $accepted ) {
						// change state of order to pending payment
						$order->update_status( 'pending', __('Customer accepted proposal.', 'boopis-woocommerce-rfq') );

						// redirect to payment form
						$return_url = $order->get_checkout_payment_url();;

					} elseif ( $declined ) {

						// change state of order to failed quote
						$order->update_status( 'failed-quote', __('Customer declined proposal.', 'boopis-woocommerce-rfq') );

						// redirect to failed 
						$return_url = $order->get_view_order_url();

					} else {
						throw new Exception( __( 'Your must either accept or decline the offer.', 'boopis-woocommerce-rfq' ) );
					}

				}

				// Redirect to success page
				if ( is_ajax() ) {
					wp_send_json( array(
						'result' 	=> 'success',
						'redirect'  => $return_url, $order
					) );
				} else {
					wp_safe_redirect( $return_url, $order );
					exit;
				}

			} catch ( Exception $e ) {
				if ( ! empty( $e ) ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}

			// If we reached this point then there were errors
			if ( is_ajax() ) {

				$response = array(
					'result'	=> 'failure',
					'messages' 	=> isset( $messages ) ? $messages : '',
				);

				wp_send_json( $response );
			}
	
		}

		public function boopis_rfq_ajax_add_to_quote() {
		  global $woocommerce;

		  $product_id        = apply_filters( 'boopis_add_to_quote_product_id', absint( $_POST['product_id'] ) );
		  $quantity          = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'boopis_stock_amount', $_POST['quantity'] );
		  $passed_validation = apply_filters( 'boopis_add_to_quote_validation', true, $product_id, $quantity );

		  if ( $passed_validation && $woocommerce->quote->boopis_add_to_quote( $product_id, $quantity ) ) {

		    do_action( 'boopis_ajax_added_to_quote', $product_id );

		    if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
		      $this->boopis_rfq_add_to_quote_message( $product_id );
		    }

		    // Return fragments
		    $this->boopis_get_refreshed_fragments();

		  } else {

		    header( 'Content-Type: application/json; charset=utf-8' );

		    // If there was an error adding to the quote, redirect to the product page to show any errors
		    $data = array(
		      'error' => true,
		      'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
		      );

		    echo json_encode( $data );
		  }

		  die();

		}

		public function boopis_get_refreshed_fragments() {
		  global $woocommerce;

		  header( 'Content-Type: application/json; charset=utf-8' );

		  // Get mini cart
		  ob_start();
		  $mini_quote = ob_get_clean();

		  // Fragments and mini cart are returned
		  $data = array(
		    'fragments' => apply_filters( 'add_to_quote_fragments', array(
		        'div.widget_shopping_quote_content' => '<div class="widget_shopping_quote_content">' . $mini_quote . '</div>'
		      )
		    ),
		    'quote_hash' => $woocommerce->quote->boopis_get_quote() ? md5( json_encode( $woocommerce->quote->boopis_get_quote() ) ) : ''
		  );

		  echo json_encode( $data );

		  die();
		}

		public function boopis_rfq_update_quote_action() {
		  global $woocommerce;

		  // Remove from quote
		  if ( ! empty( $_GET['remove_item'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'boopis-quote' ) ) {

		    $woocommerce->quote->boopis_set_quantity( $_GET['remove_item'], 0 );

		    wc_add_notice( __( 'Quote updated.', 'boopis-woocommerce-rfq' ) );

		    $referer = ( wp_get_referer() ) ? wp_get_referer() : $woocommerce->quote->boopis_get_quote_url();
		    wp_safe_redirect( $referer );
		    exit;

		  }

		}

		public function boopis_rfq_add_to_quote_action( $url = false ) {

		  if ( empty( $_REQUEST['add-to-quote'] ) || ! is_numeric( $_REQUEST['add-to-quote'] ) )
		    return;

		  global $woocommerce;

		  $product_id          = apply_filters( 'boopis_add_to_quote_product_id', absint( $_REQUEST['add-to-quote'] ) );
		  $was_added_to_quote   = false;
		  $added_to_quote       = array();
		  $adding_to_quote      = get_product( $product_id );
		  $add_to_quote_handler = apply_filters( 'boopis_add_to_quote_handler', $adding_to_quote->product_type, $adding_to_quote );

		    // Variable product handling
		  if ( 'variable' === $add_to_quote_handler ) {

		    $variation_id       = empty( $_REQUEST['variation_id'] ) ? '' : absint( $_REQUEST['variation_id'] );
		    $quantity           = empty( $_REQUEST['quantity'] ) ? 1 : apply_filters( 'boopis_stock_amount', $_REQUEST['quantity'] );
		    $all_variations_set = true;
		    $variations         = array();

		    // Only allow integer variation ID - if its not set, redirect to the product page
		    if ( empty( $variation_id ) ) {
		      wc_add_notice( __( 'Please choose product options', 'boopis-woocommerce-rfq' ), 'error' );
		      return;
		    }

		    $attributes = $adding_to_quote->get_attributes();
		    $variation  = get_product( $variation_id );

		    // Verify all attributes
		    foreach ( $attributes as $attribute ) {
		      if ( ! $attribute['is_variation'] )
		        continue;

		      $taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

		      if ( ! empty( $_REQUEST[ $taxonomy ] ) ) {

		                // Get value from post data
		                // Don't use woocommerce_clean as it destroys sanitized characters
		        $value = sanitize_title( trim( stripslashes( $_REQUEST[ $taxonomy ] ) ) );

		                // Get valid value from variation
		        $valid_value = $variation->variation_data[ $taxonomy ];

		                // Allow if valid
		        if ( $valid_value == '' || $valid_value == $value ) {
		          if ( $attribute['is_taxonomy'] )
		            $variations[ esc_html( $attribute['name'] ) ] = $value;
		          else {
		                    // For custom attributes, get the name from the slug
		            $options = array_map( 'trim', explode( '|', $attribute['value'] ) );
		            foreach ( $options as $option ) {
		              if ( sanitize_title( $option ) == $value ) {
		                $value = $option;
		                break;
		              }
		            }
		            $variations[ esc_html( $attribute['name'] ) ] = $value;
		          }
		          continue;
		        }

		      }

		      $all_variations_set = false;
		    }

		    if ( $all_variations_set ) {
		          // Add to quote validation
		      $passed_validation  = apply_filters( 'boopis_add_to_quote_validation', true, $product_id, $quantity, $variation_id, $variations );

		      if ( $passed_validation ) {
		        if ( $woocommerce->quote->boopis_add_to_quote( $product_id, $quantity, $variation_id, $variations ) ) {
		          $this->boopis_rfq_add_to_quote_message( $product_id );
		          $was_added_to_quote = true;
		          $added_to_quote[] = $product_id;
		        }
		      }
		    } else {
		      wc_add_notice( __( 'Please choose product options', 'boopis-woocommerce-rfq' ), 'error' );
		      return;
		    }

		    // Grouped Products
		  } elseif ( 'grouped' === $add_to_quote_handler ) {

		    if ( ! empty( $_REQUEST['quantity'] ) && is_array( $_REQUEST['quantity'] ) ) {

		      $quantity_set = false;

		      foreach ( $_REQUEST['quantity'] as $item => $quantity ) {
		        if ( $quantity <= 0 )
		          continue;

		        $quantity_set = true;

		        // Add to quote validation
		        $passed_validation  = apply_filters( 'boopis_add_to_quote_validation', true, $item, $quantity );

		        if ( $passed_validation ) {
		          if ( $woocommerce->quote->boopis_add_to_quote( $item, $quantity ) ) {
		            $was_added_to_quote = true;
		            $added_to_quote[] = $item;
		          }
		        }
		      }

		      if ( $was_added_to_quote ) {
		        $this->boopis_rfq_add_to_quote_message( $added_to_quote );
		      }

		      if ( ! $was_added_to_quote && ! $quantity_set ) {
		        wc_add_notice( __( 'Please choose the quantity of items you wish to add to your quote', 'boopis-woocommerce-rfq' ), 'error' );
		        return;
		      }

		    } elseif ( $product_id ) {

		      /* Link on product archives */
		      wc_add_notice( __( 'Please choose a product to add to your quot', 'boopis-woocommerce-rfq' ), 'error' );
		      return;

		    }

		  // Simple Products
		  } else {

		    $quantity       = empty( $_REQUEST['quantity'] ) ? 1 : apply_filters( 'boopis_stock_amount', $_REQUEST['quantity'] );

		    // Add to quote validation
		    $passed_validation  = apply_filters( 'boopis_add_to_quote_validation', true, $product_id, $quantity );

		    if ( $passed_validation ) {
		        // Add the product to the quote
		      if ( $woocommerce->quote->boopis_add_to_quote( $product_id, $quantity ) ) {
		        $this->boopis_rfq_add_to_quote_message( $product_id );
		        $was_added_to_quote = true;
		        $added_to_quote[] = $product_id;
		      }
		    }

		  }

		    // If we added the product to the quote we can now do a redirect, otherwise just continue loading the page to show errors
		  if ( $was_added_to_quote  && wc_notice_count( 'error' ) === 0 ) {

		    $url = apply_filters( 'add_to_quote_redirect', $url, $product_id );

		    // If has custom URL redirect there
		    if ( $url ) {
		      wp_safe_redirect( $url );
		      exit;
		    }

		    // Redirect to quote option
		    elseif ( get_option('woocommerce_cart_redirect_after_add') == 'yes' ) {
		      wp_safe_redirect( $woocommerce->quote->boopis_get_quote_url() );
		      exit;
		    }

		    // Redirect to page without querystring args
		    elseif ( wp_get_referer() ) {
		      wp_safe_redirect( add_query_arg( 'added-to-quote', implode( ',', $added_to_quote ), remove_query_arg( array( 'add-to-quote', 'quantity', 'product_id' ), wp_get_referer() ) ) );
		      exit;
		    }

		  }

		}

		public function boopis_rfq_add_to_quote_message( $product_id ) {
		  global $woocommerce;

		  if ( is_array( $product_id ) ) {

		    $titles = array();

		    foreach ( $product_id as $id ) {
		      $titles[] = get_the_title( $id );
		    }

		    $added_text = sprintf( __( 'Added &quot;%s&quot; to your quote.', 'boopis-woocommerce-rfq' ), join( __( '&quot; and &quot;', 'boopis-woocommerce-rfq' ), array_filter( array_merge( array( join( '&quot;, &quot;', array_slice( $titles, 0, -1 ) ) ), array_slice( $titles, -1 ) ) ) ) );

		  } else {
		    $added_text = sprintf( __( '&quot;%s&quot; was successfully added to your quote.', 'boopis-woocommerce-rfq' ), get_the_title( $product_id ) );
		  }

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				$return_to = apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), false ) : wc_get_page_permalink( 'shop' ) );
				$message   = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( $return_to ), esc_html__( 'Continue Shopping', 'woocommerce' ), esc_html( $added_text ) );
			} else {
				$message   = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( get_permalink( get_option('boopis_rfq_page_id') ) ), esc_html__( 'View Quote', 'boopis-rfq' ), esc_html( $added_text ) );
			}

		  wc_add_notice( apply_filters('boopis_rfq_add_to_quote_message', $message) );
		}

	} // End Class


}

return new BOOPIS_RFQ_Front();
