=== Boopis WooCommerce RFQ ===
Contributors: boopis
Tags: rfq, request quote, quote request, zero price, hide price, hide add to cart, make offer, negotiable pricing, price filter, ask for price
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PEZPERKEW2XG6
Requires at least: 3.8.0
Tested up to: 4.5
Stable tag: trunk
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Replaces products with a price of zero to an open form for inquiry

== Description ==
[Demo](http://demo.boopis.com/wp_rfq/ "rfq")

#### The Original RFQ Plugin For Wordpress

Turning your online store into a lead generating machine has never been easier with the WooCommerce Request For Quotation Plugin for Wordpress. As competition increases with everyone selling the same products online, merchants are beginning to use ecommerce stores as a means to generate inquiries for custom quoted products.

If you have restrictions from your manufacturer for published prices, this plugin in perfect for you to generate interest from potential customers, and turn them into sales. If you sell expensive goods that requires negotiating prices, this plugin will help you start selling online.

Turn all you products into quotable items or pick just a few and mix and match items that can be purchased online and items that need quotes. All you need to do is change the price to zero, and presto, you've got a quotable item.

Alernatively you can turn your products into quotable items based on product tags.

Once the users click the inquire button, they are sent to a contact form where the product information is automatically filled into the form and you can define the fields that the customer needs to fill in. By reducing the amount of steps your online users take, the better chance you have of converting them into customers.

#### Features
* Convert products into quotatable items with prices set to zero
* Convert products into quotatable items based on tags
* Add multiple products to quotations request
* Autofill logged in user's information into quote request
* Transfer filled in data for quote request into an order
* Create proposal to customer with emails and webviews of proposal
* Adjust all the details of proposal with terms, expiration date, pricing, line items
* Set auto emails for new, pending, expired, and failed quotes
* Copy and customize your own emails
* Allow customer to pay for proposal on your website through checkout
* Translatable text

== Installation ==
Ensure that you have WooCommerce installed. Then upload the contents via ftp or ssh to the file directory of your wordpress site under wp-content/plugins/

#### Product Settings
Once installed, change the price of the items you want displayed as quotable items to ZERO. You will notice that on the front end, your quotable item buttons have changed. You can also choose to modify products based on tags in the settings menu.

#### RFQ Page
You can change the page where all quotation requests are made from the default under the settings menu.

#### Proposal Settings
Add details under the terms meta box in the quote order to present terms for the proposal.
Add an expiration date that shows valididy of the proposal. If the date exceeds, the expiration date, the user will not be able to move forward to pay based on the offer.

#### Hooks To Change Elements

##### RFQ Form
* RFQ Page product list heading
> add_filter('boopis_rfq_page_item_title', 'your_function_to_change_page_item_heading');

* RFQ Page details form heading
> add_filter('boopis_rfq_page_details_title', 'your_function_to_change_page_details_heading');

* RFQ Page when products have not been added to list
> add_filter('boopis_rfq_page_empty_text', 'your_function_to_change_page_text');

*more to come...*
##### RFQ Emails
##### RFQ Proposal Page

== Frequently Asked Questions ==
Q: Can I add multiple products?

A: Yes.

Q: Can I add or remove form fields?

A: Yes. Please make a support request for more details.


== Screenshots ==
1. Modifies WooCommerce Add to Cart buttons to quotations request buttons
2. Add variable products with options into quote list
3. Collect same fields as checkout for seamless checkout integration
4. On submission of request, customer is redirected to a review of request
5. Admin email when a request for quotation is made
6. Customer email when a request for quotation is made
7. Set terms, expiration, and pricing for proposal from rfq
8. Customer email wil proposal and action links to accept or deline
9. Web view of proposal with action links
10. Option to pay once proposal is accepted

== Changelog ==

= 1.0.0 = 
* Initial Release 1/23/14

= 1.0.1 = 
* Add-On Link Adjustment 1/24/14

= 1.0.2 = 
* Main Link Adjustment 1/24/14

= 1.0.3 = 
* Free price replace on sale price 1/24/14

= 1.0.4 = 
* Readme changes and version correction 1/24/14

= 1.0.5 = 
* Multi-Product Compatibility 2/4/14

= 1.1.0 = 
* Compatibility with WooCommerce v2.1.1 2/13/14

= 1.2.0 = 
* Added alternative trigger by tags 3/21/14
* Added price replacement option 3/21/14

= 1.2.2 = 
* Compatibility with Wordpress 4.0 & WooCommerce 2.2 9/30/14

= 1.3.0 = 
* Added field for custom thank you message 11/25/15

= 1.4.0 = 
* Added analytics data for form submissions 1/12/16

= 1.4.1 = 
* Sanitize, validate, and escape POST/GET/REQUEST calls 5/2/16

= 1.5.0 = 
* Added language support (ES) 5/19/16

= 2.0.0 = 
* Integrated multiple products into inquiry form 5/20/16

= 2.0.1 = 
* Bug fix declare array for sanitize array in form 5/20/16

= 3.0.0 = 
* Added quotes to orders for db storage and proposal creation 6/1/16

= 3.0.1 = 
* Added options to modify form fields 6/4/16

== Upgrade Notice ==
= 3.0.1 = 
* Added options to modify form fields 6/4/16

== Modify / Remove / And Add New Form Fields ==

####List of fields to modify (based on wc checkout): 

`
['billing']['billing_first_name']
['billing']['billing_last_name']
['billing']['billing_company']
['billing']['billing_address_1']
['billing']['billing_address_2']
['billing']['billing_city']
['billing']['billing_postcode']
['billing']['billing_country']
['billing']['billing_state']
['billing']['billing_email']
['billing']['billing_phone']
['order']['order_comments']
`

### Modifying or removing existing fields

`
// Hook in to form
add_filter( 'boopis_rfq_form_fields' , 'custom_override_rfq_fields' );

// Our hooked in function - $fields is passed via the filter!
function custom_override_rfq_fields( $fields ) {

	// Remove billing first and last name
	unset($fields['billing']['billing_first_name']);
	unset($fields['billing']['billing_last_name']);


	// Make phone number optional
	$fields['billing']['billing_phone']['required'] = false;

	// Modify name and class of postcode 
	$fields['billing']['billing_postcode'] = array(
		'label'     => __('Zip Code', 'woocommerce'),
		'placeholder'   => _x('Zip Code', 'placeholder', 'woocommerce'),
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
	);

  return $fields;
}
`

### Adding new custom fields

#### Add the new field
`
// Add new custom field
add_action( 'boopis_rfq_after_order_notes', 'custom_select_referal_rfq' );

function custom_select_referal_rfq( $rfq ) {

	woocommerce_form_field( 'referal', array(
		'type'          => 'select',
		'class'         => array('form-row-wide'),
		'label'         => __('How did you hear about us?'),
		'required'   		=> true,
		'clear'       	=> false,
		'options'     	=> array(
			'' 						=> __('Select Option', 'boopis-woocommerce-rfq' ),
			'friend' 			=> __('Friend', 'boopis-woocommerce-rfq' ),
			'coworker' 		=> __('Coworker', 'boopis-woocommerce-rfq' )
		),
	), $rfq->get_value( 'referal' ));

}
`

#### Validate the new field
`
// Validate new custom field
add_action('boopis_rfq_process', 'custom_select_referal_rfq_process');

function custom_select_referal_rfq_process() {
  // Check if set, if its not set add an error.
	if ( empty($_POST['referal']) ) {
		wc_add_notice( __( 'You must select the referal field.' ), 'error' );
	}
}
`

#### Update the new field
`
// Update new custom field
add_action( 'boopis_rfq_update_order_meta', 'custom_select_referal_update_order_meta' );

function custom_select_referal_update_order_meta( $order_id ) {
	if ( ! empty( $_POST['referal'] ) ) {
		update_post_meta( $order_id, 'Referal', sanitize_text_field( $_POST['referal'] ) );
	}
}
`

See [WooCommerce Docs](https://docs.woothemes.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/) for more details.