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
* [Demo](http://demo.boopis.com/wp_rfq/ "rfq")

Turning your online store into a lead generating machine has never been easier with the WooCommerce Request For Quotation Plugin for Wordpress. As competition increases with everyone selling the same products online, merchants are beginning to use ecommerce stores as a means to generate inquiries for custom quoted products.

If you have restrictions from your manufacturer for published prices, this plugin in perfect for you to generate interest from potential customers, and turn them into sales. If you sell expensive goods that requires negotiating prices, this plugin will help you start selling online.

Turn all you products into quotable items or pick just a few and mix and match items that can be purchased online and items that need quotes. All you need to do is change the price to zero, and presto, you've got a quotable item.

Alernatively you can turn your products into quotable items based on product tags.

Once the users click the inquire button, they are sent to a contact form where the product information is automatically filled into the form and you can define the fields that the customer needs to fill in. By reducing the amount of steps your online users take, the better chance you have of converting them into customers.

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
1. Add multiple products to a quote list
2. Formbuilder (for older versions. New version will rely on checkout hooks)
3. Create terms and expiration date for your proposal
4. Emails delivered to admin and customer when rfq is received
5. Simple settings to adjust products for inquiries and providing new pricing
6. Customers can go straight to payment once a proposal has been made

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

== Upgrade Notice ==
= 3.0.0 = 
* Added quotes to orders for db storage and proposal creation 6/1/16

== Todo ==