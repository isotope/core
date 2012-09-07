<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['systemColumn']					= 'Name `%s` is reserved for system use. Please choose different name.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate']			= 'You must specify a template for the button "%s".';
$GLOBALS['TL_LANG']['ERR']['order_conditions']				= 'You must accept the terms & conditions to continue';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']		= 'No store configuration available';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration']	= 'Please create a default store configuration.';
$GLOBALS['TL_LANG']['ERR']['productNameMissing']			= '<no product name found>';
$GLOBALS['TL_LANG']['ERR']['noSubProducts']					= 'no sub-products found';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory']				= 'You have not yet placed any orders.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound']					= 'The requested order was not found.';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat']			= 'Currency formatting not found';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled']				= 'Search functionality is not enabled!';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired']				= 'You must be logged in to checkout.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption']				= 'Please select an option.';
$GLOBALS['TL_LANG']['ERR']['noAddressData']					= 'Address data is required to calculate taxes!';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate']				= 'A variant with this attributes is already available. Please select another combination.';
$GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty']				= 'The filtered category is empty, all products are now showing.';
$GLOBALS['TL_LANG']['ERR']['discount']						= 'Please enter whole numbers or decimals signed with + or - and optionally with a percentage.';
$GLOBALS['TL_LANG']['ERR']['surcharge']						= 'Please enter whole numbers or decimals optionally with a percentage.';
$GLOBALS['TL_LANG']['ERR']['orderFailed']					= 'Checkout failed. Please try again or choose another payment method.';
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] 		= 'A billing address was not fuond.  Please specify a billing address.';
$GLOBALS['TL_LANG']['ERR']['cc_num']						= 'Please provide a valid credit card number.';
$GLOBALS['TL_LANG']['ERR']['cc_type']						= 'Please select a credit card type.';
$GLOBALS['TL_LANG']['ERR']['cc_exp']						= 'Please provide a credit card expiration date in the mm/yy format.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv']						= 'Please provide a card code verification number (3 or 4 digits found on the front or back of the card).';
$GLOBALS['TL_LANG']['ERR']['cc_match']						= 'Your credit card number does not match the selected credit card type.';
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist']			= 'This address does not exist in your address book.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries']			= 'You have no address book entries.';
$GLOBALS['TL_LANG']['ERR']['cartMinSubtotal']				= 'The minimum order amount is %s. Please add more products before checkout.';


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['editLanguage']			= 'Edit';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage']		= 'Delete';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage']		= 'Fallback language';
$GLOBALS['TL_LANG']['MSC']['editingLanguage']		= 'ATTENTION: You are editing language-specific data!';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm']	= 'Are you sure you want to delete this language? There is no undo!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage']		= 'undefined';
$GLOBALS['TL_LANG']['MSC']['copyFallback']			= 'Duplicate Fallback';
$GLOBALS['TL_LANG']['MSC']['noSurcharges']			= 'No surcharges have been found.';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage']	= 'Loading...';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline']	= 'Order no %s / %s';
$GLOBALS['TL_LANG']['MSC']['payment_processing']	= 'Your payment is being processed. Please be patient...';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed']	= 'Your payment could not be processed.<br /><br />Reason: %s';
$GLOBALS['TL_LANG']['MSC']['mmNoUploads']			= 'No files uploaded.';
$GLOBALS['TL_LANG']['MSC']['mmUpload']				= 'Upload new file';
$GLOBALS['TL_LANG']['MSC']['quantity']				= 'Quantity';
$GLOBALS['TL_LANG']['MSC']['order_conditions']		= 'I agree to the terms & conditions';

$GLOBALS['TL_LANG']['MSC']['defaultSearchText'] = 'search products';
$GLOBALS['TL_LANG']['MSC']['blankSelectOptionLabel'] = '-';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'] = 'Please select...';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel']			= 'Your downloadable products';
$GLOBALS['TL_LANG']['MSC']['priceRangeLabel'] = '<span class="from">From</span> %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel'] = 'View Details';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel'] = 'Search Term: ';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel'] = 'Search Fields: ';
$GLOBALS['TL_LANG']['MSC']['perPageLabel'] = 'Products Per Page';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel'] = 'Keywords';
$GLOBALS['TL_LANG']['MSC']['submitLabel'] = 'Submit';
$GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'] = 'Clear Filters';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update'] = 'Update';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Add To Cart';
$GLOBALS['TL_LANG']['MSC']['pagerSectionTitleLabel'] = 'Page:';
$GLOBALS['TL_LANG']['MSC']['orderByLabel'] = 'Order By:';

$GLOBALS['TL_LANG']['MSC']['noProducts'] = 'No products have been found.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation'] = "We're sorry, the product information you have requested is not showing up in our store. For further assistance please contact us.";

$GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] = 'Options';

$GLOBALS['TL_LANG']['MSC']['previousStep']	= 'Back';
$GLOBALS['TL_LANG']['MSC']['nextStep']		= 'Continue';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']	= 'Order';

$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'No categories are associated with this product.';
$GLOBALS['TL_LANG']['MSC']['labelPerPage'] = 'Per Page';
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Sort By';
$GLOBALS['TL_LANG']['MSC']['labelSubmit'] = 'Submit';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants'] = 'Please Select';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText'] = 'Remove';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'There are no items in your cart.';
$GLOBALS['TL_LANG']['MSC']['addedToCart'] = 'The selected product has been added to your shopping cart.';
$GLOBALS['TL_LANG']['MSC']['cartMerged'] = 'The products from your last visit have been readded. Please review your shopping cart items.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = 'Remove %s from your cart';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel'] = 'Order Subtotal: ';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Shipping';
$GLOBALS['TL_LANG']['MSC']['paymentLabel'] = 'Payment';
$GLOBALS['TL_LANG']['MSC']['taxLabel'] = '%s Tax: ';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Order Total: ';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel'] = 'Selected Shipping Options: ';
$GLOBALS['TL_LANG']['MSC']['noVariants'] = 'No product variants found.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts'] = 'Generate Subproducts';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt'] = "(select)";
$GLOBALS['TL_LANG']['MSC']['actualPrice'] = 'Actual Price';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'No payment options are currently available';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'No shipping options are currently available.';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails'] = 'No orders emails found.';
$GLOBALS['TL_LANG']['MSC']['noOrders'] = 'No orders found.';
$GLOBALS['TL_LANG']['MSC']['downloadsRemaining'] = '<br />%s downloads remaining';

$GLOBALS['TL_LANG']['ISO']['couponsInputLabel'] = 'Promotional code';
$GLOBALS['TL_LANG']['ISO']['couponsHeadline'] = 'Apply Promotional Codes';
$GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel'] = 'Apply';

$GLOBALS['TL_LANG']['MSC']['cartBT']					= 'Shopping Cart';
$GLOBALS['TL_LANG']['MSC']['checkoutBT']				= 'Proceed to Checkout';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT']		= 'Continue shopping';
$GLOBALS['TL_LANG']['MSC']['updateCartBT']				= 'Update Cart';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline']		= 'Order Status: %s';
$GLOBALS['TL_LANG']['MSC']['checkboutStepBack']			= 'Go back to step "%s"';


//Addresses
$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel']		= 'Create New Address';
$GLOBALS['TL_LANG']['MSC']['useBillingAddress']			= 'Use billing address';
$GLOBALS['TL_LANG']['MSC']['useCustomerAddress']		= 'Use customer address';
$GLOBALS['TL_LANG']['MSC']['differentShippingAddress']	= 'Different shipping address';

$GLOBALS['TL_LANG']['MSC']['addressBookLabel'] = 'Addresses';
$GLOBALS['TL_LANG']['MSC']['editAddressLabel'] = 'Edit';
$GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'] = 'Delete';
$GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm'] = 'Do you really want to delete this address? This cannot be undone.';

//Invoice language Entries
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] = 'Invoice';
$GLOBALS['TL_LANG']['MSC']['iso_order_status'] = 'Status';
$GLOBALS['TL_LANG']['MSC']['iso_order_date'] = 'Order date';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'] = 'Billing Address';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'] = 'Shipping Address';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header'] = 'Tax';
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] = 'Subtotal';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'] = 'Shipping & Handling';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] = 'Grand Total';
$GLOBALS['TL_LANG']['MSC']['iso_order_items'] = 'Items';
$GLOBALS['TL_LANG']['MSC']['iso_order_sku'] = 'SKU';
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header'] = 'Quantity';
$GLOBALS['TL_LANG']['MSC']['iso_price_header'] = 'Price';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header'] = 'SKU';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header'] = 'Product Name';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] = 'Name on Credit Card';

$GLOBALS['TL_LANG']['MSC']['low_to_high'] = 'lo to hi';
$GLOBALS['TL_LANG']['MSC']['high_to_low'] = 'hi to lo';
$GLOBALS['TL_LANG']['MSC']['a_to_z'] = 'A to Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a'] = 'Z to A';
$GLOBALS['TL_LANG']['MSC']['old_to_new'] = 'earlier to later';
$GLOBALS['TL_LANG']['MSC']['new_to_old'] = 'later to earlier';


/**
 * Content elements
 */
$GLOBALS['TL_LANG']['CTE']['isotope']	= 'Isotope eCommerce';


/**
 * Isotope module labels
 */
$GLOBALS['TL_LANG']['ISO']['productSingle']		= '1 Product';
$GLOBALS['TL_LANG']['ISO']['productMultiple']	= '%s Products';

$GLOBALS['TL_LANG']['ISO']['shipping_address_message']			= 'Enter your shipping information or select an existing address.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message']			= 'Enter your billing information or select an existing address.';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message']		= 'Enter your billing information';
$GLOBALS['TL_LANG']['ISO']['customer_address_message']			= 'Enter your customer information or select an existing address.';
$GLOBALS['TL_LANG']['ISO']['customer_address_guest_message']	= 'Enter your customer information';
$GLOBALS['TL_LANG']['ISO']['shipping_method_message']	= 'Select a shipping method.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_missing']	= 'Please select a shipping method.';
$GLOBALS['TL_LANG']['ISO']['payment_method_message']	= 'Enter your payment information.';
$GLOBALS['TL_LANG']['ISO']['payment_method_missing']	= 'Please select a payment method.';
$GLOBALS['TL_LANG']['ISO']['order_review_message']		= 'Review and confirm your order details.';

$GLOBALS['TL_LANG']['ISO']['checkout_address']				= 'Address';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping']				= 'Shipping';
$GLOBALS['TL_LANG']['ISO']['checkout_payment']				= 'Payment';
$GLOBALS['TL_LANG']['ISO']['checkout_review']				= 'Review';
$GLOBALS['TL_LANG']['ISO']['billing_address']				= 'Billing Address';
$GLOBALS['TL_LANG']['ISO']['shipping_address']				= 'Shipping Address';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address']		= 'Billing & Shipping Address';
$GLOBALS['TL_LANG']['ISO']['customer_address']				= 'Customer Address';
$GLOBALS['TL_LANG']['ISO']['shipping_method']				= 'Shipping Method';
$GLOBALS['TL_LANG']['ISO']['payment_method']				= 'Payment Method';
$GLOBALS['TL_LANG']['ISO']['order_conditions']				= 'Order Conditions';
$GLOBALS['TL_LANG']['ISO']['order_review']					= 'Order Review';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo']			= 'Change';
$GLOBALS['TL_LANG']['ISO']['cc_num']						= 'Credit card number';
$GLOBALS['TL_LANG']['ISO']['cc_type']						= 'Credit card type';
$GLOBALS['TL_LANG']['ISO']['cc_ccv']						= 'CCV number (3 or 4 digit code)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_paypal']					= 'Expiration date';
$GLOBALS['TL_LANG']['ISO']['cc_exp_date']					= 'Expiration month/year';
$GLOBALS['TL_LANG']['ISO']['cc_exp_month']					= 'Expiration month';
$GLOBALS['TL_LANG']['ISO']['cc_exp_year']					= 'Expiration year';
$GLOBALS['TL_LANG']['ISO']['cc_issue_number']				= 'Credit card issue number, 2 digits (required for Maestro and Solo cards).';
$GLOBALS['TL_LANG']['ISO']['cc_start_date']					= 'Credit card start date (required for Maestro and Solo cards).';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc']					= array('Processing payment', 'Please enter the required information to process your payment.', 'Pay now');
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect']				= array('Processing payment', 'You will be redirected to the payment gateway website. If you are not automatically redirected, please click on the "Pay now" button.', 'Pay now');
$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound']		= 'Payment module not found!';
$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound']		= 'Shipping module not found!';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNoInfo']			= 'This payment module does not provide additional information.';
$GLOBALS['TL_LANG']['ISO']['backendShippingNoInfo']			= 'This shipping module does not provide additional information.';


/**
 * Miscellaneous
 */
$GLOBALS['ISO_LANG']['MSC']['useDefault']			= 'Use default value';
$GLOBALS['ISO_LANG']['MSC']['activeStep']			= 'active step: ';
$GLOBALS['ISO_LANG']['MSC']['productcacheLoading']	= 'Loading products...';
$GLOBALS['ISO_LANG']['MSC']['productcacheNoscript']	= 'Your browser does not support javascript. Please <a href="%s">click here</a> to load the product list.';
$GLOBALS['ISO_LANG']['MSC']['noFilesInFolder']		= 'No files in this folder';
$GLOBALS['ISO_LANG']['MSC']['loadingProductData']	= 'Loading product data …';
$GLOBALS['ISO_LANG']['MSC']['templateConfig']		= '%s (Store Config: %s)';
$GLOBALS['ISO_LANG']['MSC']['templateTheme']		= '%s (Theme: %s)';
$GLOBALS['ISO_LANG']['MSC']['splittedTaxRate']		= 'Splitted';
$GLOBALS['ISO_LANG']['MSC']['newOrders']			= 'You have %s order(s) with status "%s"';
$GLOBALS['ISO_LANG']['MSC']['checkoutStep']			= 'Step %s of %s (%s) - ';


/**
 * Shipping modules
 */
$GLOBALS['ISO_LANG']['SHIP']['flat']				= array('Flat-price shipping');
$GLOBALS['ISO_LANG']['SHIP']['weight_total']		= array('Weight total-based shipping');
$GLOBALS['ISO_LANG']['SHIP']['order_total']		= array('Order total-based shipping');
$GLOBALS['ISO_LANG']['SHIP']['collection']		= array('Collection');
$GLOBALS['ISO_LANG']['SHIP']['ups']				= array('UPS Live Rates and Service shipping');
$GLOBALS['ISO_LANG']['SHIP']['usps']				= array('USPS Live Rates and Service shipping');


/**
 * Payment modules
 */
$GLOBALS['ISO_LANG']['PAY']['cash']				= array('Cash', 'Use this for all offline processed payment.');
$GLOBALS['ISO_LANG']['PAY']['paypal']			= array('PayPal Standard Checkout', 'This PayPal module supports IPN (Instant Payment Notifications).');
$GLOBALS['ISO_LANG']['PAY']['paypalpayflowpro']	= array('PayPal Payflow Pro', 'The PayPal Payflow module is a full service credit card gateway, a more robust solution for most e-commerce sites.');
$GLOBALS['ISO_LANG']['PAY']['postfinance']		= array('Postfinance', 'Payment gateway for the swiss post payment system that supports various card types. The store will be instantly notified about successfull transactions.');
$GLOBALS['ISO_LANG']['PAY']['authorizedotnet']	= array('Authorize.net', 'An Authorize.net payment gateway.');
$GLOBALS['ISO_LANG']['PAY']['cybersource']		= array('Cybersource','For Cybersource users. Uses the Simple Order API method.');


/**
 * Galleries
 */
$GLOBALS['ISO_LANG']['GAL']['default']			= array('Default gallery (Lightbox/Mediabox)', '<p>Uses the lightbox/mediabox for full size images. Make sure you select the appropriate "moo_" template in your page layout configuration.</p><p>You can supply a "rel" attribute to the link target by using a pipe (eg. "tl_files/video.mov|lightbox[400 300]"). If no "rel" attribute is supplied, the link will be opened in a new window.</p>');
$GLOBALS['ISO_LANG']['GAL']['inline']			= array('Inline gallery', 'Clicking on a gallery image will replace the main image.');
$GLOBALS['ISO_LANG']['GAL']['zoom'] = array('Inline gallery with zoom effect', 'In addition to the behaviour of inline gallery, it also adds an image zoom feature to the main image.');


/**
 * Product types
 */
$GLOBALS['ISO_LANG']['PRODUCT']['regular']			= array('Regular product', 'A default product. Select this if nothing else matches.');


/**
 * Credit card types
 */
$GLOBALS['ISO_LANG']['CCT']['mc']					= 'MasterCard';
$GLOBALS['ISO_LANG']['CCT']['visa']					= 'Visa';
$GLOBALS['ISO_LANG']['CCT']['amex']					= 'American Express';
$GLOBALS['ISO_LANG']['CCT']['discover']				= 'Discover';
$GLOBALS['ISO_LANG']['CCT']['jcb']					= 'JCB';
$GLOBALS['ISO_LANG']['CCT']['diners']				= 'Diner\'s Club';
$GLOBALS['ISO_LANG']['CCT']['enroute']				= 'EnRoute';
$GLOBALS['ISO_LANG']['CCT']['carte_blanche']		= 'Carte Blanche';
$GLOBALS['ISO_LANG']['CCT']['jal']					= 'JAL';
$GLOBALS['ISO_LANG']['CCT']['maestro']				= 'Maestro UK';
$GLOBALS['ISO_LANG']['CCT']['delta']				= 'Delta';
$GLOBALS['ISO_LANG']['CCT']['solo']					= 'Solo';
$GLOBALS['ISO_LANG']['CCT']['visa_electron']		= 'Visa Electron';
$GLOBALS['ISO_LANG']['CCT']['dankort']				= 'Dankort';
$GLOBALS['ISO_LANG']['CCT']['laser']				= 'Laser';
$GLOBALS['ISO_LANG']['CCT']['carte_bleue']			= 'Carte Bleue';
$GLOBALS['ISO_LANG']['CCT']['carta_si']				= 'Carta Si';
$GLOBALS['ISO_LANG']['CCT']['enc_acct_num']			= 'Encoded Account Number';
$GLOBALS['ISO_LANG']['CCT']['uatp']					= 'Universal Air Travel Program';
$GLOBALS['ISO_LANG']['CCT']['maestro_intl']			= 'Maestro International';
$GLOBALS['ISO_LANG']['CCT']['ge_money_uk']			= 'GE Money UK';


/**
 * Weight Units
 * http://www.metric-conversions.org/weight/weight-conversions.htm
 */
$GLOBALS['ISO_LANG']['WGT']['mg']					= array('Milligram (mg)', 'A unit of mass equal to one-thousandth of a gram.');
$GLOBALS['ISO_LANG']['WGT']['g']					= array('Gram (g)', 'A metric unit of weight equal to one thousandth of a kilogram.');
$GLOBALS['ISO_LANG']['WGT']['kg']					= array('Kilogram (kg)', 'One kilogram is equivalent to 1,000 grams or 2.2 pounds; the mass of a liter of water.');
$GLOBALS['ISO_LANG']['WGT']['t']					= array('Metric Ton (t)', 'A unit of weight equal to 1,000 kilograms, or 2,204.6 pounds.');
$GLOBALS['ISO_LANG']['WGT']['ct']					= array('Carats (ct)', 'A measure of weight used for gemstones. One carat is equal to 1/5 of a gram (200 milligrams). Note that karat with a "K" is a measure of the purity of a gold alloy.');
$GLOBALS['ISO_LANG']['WGT']['oz']					= array('Ounce (oz)', 'A unit of weight equal to one sixteenth of a pound or 28.35 grams.');
$GLOBALS['ISO_LANG']['WGT']['lb']					= array('Pound (lb)', 'A unit of mass equal to 16 ounces');
$GLOBALS['ISO_LANG']['WGT']['st']					= array('Stone (st)', 'A British measurement of mass that equals fourteen pounds.');
$GLOBALS['ISO_LANG']['WGT']['grain']				= array('Grain', '1/7000 pound; equals a troy grain or 64.799 milligrams.');


/**
 * Attributes
 */
$GLOBALS['ISO_LANG']['ATTR']['text']				= array('Text field', 'A single-line input field for a short or medium text.');
$GLOBALS['ISO_LANG']['ATTR']['textarea']			= array('Textarea', 'A multi-line input field for a medium or long text.');
$GLOBALS['ISO_LANG']['ATTR']['select']				= array('Select menu', 'A single- or multi-line drop-down menu.<br /><i>This field type is suitable for product variants.</i>');
$GLOBALS['ISO_LANG']['ATTR']['radio']				= array('Radio button menu', 'A list of multiple options from which one can be selected.<br /><i>This field type is suitable for product variants.</i>');
$GLOBALS['ISO_LANG']['ATTR']['checkbox']			= array('Checkbox menu', 'A list of multiple options from which any can be selected.');
$GLOBALS['ISO_LANG']['ATTR']['mediaManager']		= array('Media Manager', 'Upload images and other files to the Isotope eCommerce file system. Output is processed trough an IsotopeGallery class.');
$GLOBALS['ISO_LANG']['ATTR']['conditionalselect']	= array('Conditional Select-Menu', 'show select-options based on another select menu.');
$GLOBALS['ISO_LANG']['ATTR']['fileTree']			= array('File tree', 'A file tree for single or multiple files and folders.');
$GLOBALS['ISO_LANG']['ATTR']['downloads']			= array('Downloads', 'Download files from the product, e.g. manuals, data sheets etc.');
$GLOBALS['ISO_LANG']['ATTR']['upload']				= array('File upload', 'A single-line input field to upload a local file to the server.');


/**
 * Currencies
 */
$GLOBALS['ISO_LANG']['CUR']['AED'] = 'AED - United Arab Emirates Dirham';
$GLOBALS['ISO_LANG']['CUR']['AFN'] = 'AFN - Afghani';
$GLOBALS['ISO_LANG']['CUR']['ALL'] = 'ALL - Lek';
$GLOBALS['ISO_LANG']['CUR']['AMD'] = 'AMD - Dram';
$GLOBALS['ISO_LANG']['CUR']['ANG'] = 'ANG - Netherlands Antilles Guilder';
$GLOBALS['ISO_LANG']['CUR']['AOA'] = 'AOA - Kwanza';
$GLOBALS['ISO_LANG']['CUR']['ARS'] = 'ARS - Argentinian Nuevo Peso';
$GLOBALS['ISO_LANG']['CUR']['AUD'] = 'AUD - Australian Dollar';
$GLOBALS['ISO_LANG']['CUR']['AWG'] = 'AWG - Aruban Guilder';
$GLOBALS['ISO_LANG']['CUR']['AZN'] = 'AZN - Azerbaijani Manat';
$GLOBALS['ISO_LANG']['CUR']['BAM'] = 'BAM - Convertible Mark';
$GLOBALS['ISO_LANG']['CUR']['BBD'] = 'BBD - Barbados Dollar';
$GLOBALS['ISO_LANG']['CUR']['BDT'] = 'BDT - Taka';
$GLOBALS['ISO_LANG']['CUR']['BGN'] = 'BGN - Bulgarian Lev';
$GLOBALS['ISO_LANG']['CUR']['BHD'] = 'BHD - Bahraini Dinar';
$GLOBALS['ISO_LANG']['CUR']['BIF'] = 'BIF - Burundi Franc';
$GLOBALS['ISO_LANG']['CUR']['BMD'] = 'BMD - Bermudian Dollar';
$GLOBALS['ISO_LANG']['CUR']['BND'] = 'BND - Brunei Dollar';
$GLOBALS['ISO_LANG']['CUR']['BOB'] = 'BOB - Boliviano';
$GLOBALS['ISO_LANG']['CUR']['BRL'] = 'BRL - Brazilian real';
$GLOBALS['ISO_LANG']['CUR']['BSD'] = 'BSD - Bahamian Dollar';
$GLOBALS['ISO_LANG']['CUR']['BTN'] = 'BTN - Ngultrum';
$GLOBALS['ISO_LANG']['CUR']['BWP'] = 'BWP - Pula';
$GLOBALS['ISO_LANG']['CUR']['BYR'] = 'BYR - Belarussian Rouble';
$GLOBALS['ISO_LANG']['CUR']['BZD'] = 'BZD - Belize Dollar';
$GLOBALS['ISO_LANG']['CUR']['CAD'] = 'CAD - Canadian Dollar';
$GLOBALS['ISO_LANG']['CUR']['CDF'] = 'CDF - Congolese Franc';
$GLOBALS['ISO_LANG']['CUR']['CHF'] = 'CHF - Swiss Franc';
$GLOBALS['ISO_LANG']['CUR']['CLF'] = 'CLF - Unidades de Fomento';
$GLOBALS['ISO_LANG']['CUR']['CLP'] = 'CLP - Chilean Peso';
$GLOBALS['ISO_LANG']['CUR']['CNY'] = 'CNY - Yuan Renminbi';
$GLOBALS['ISO_LANG']['CUR']['COP'] = 'COP - Colombian Peso';
$GLOBALS['ISO_LANG']['CUR']['CRC'] = 'CRC - Costa Rican Colón';
$GLOBALS['ISO_LANG']['CUR']['CUC'] = 'CUC - Peso Convertible';
$GLOBALS['ISO_LANG']['CUR']['CUP'] = 'CUP - Cuban Peso';
$GLOBALS['ISO_LANG']['CUR']['CVE'] = 'CVE - Escudo Caboverdiano';
$GLOBALS['ISO_LANG']['CUR']['CZK'] = 'CZK - Czech Koruna';
$GLOBALS['ISO_LANG']['CUR']['DJF'] = 'DJF - Djibouti Franc';
$GLOBALS['ISO_LANG']['CUR']['DKK'] = 'DKK - Danish Krone';
$GLOBALS['ISO_LANG']['CUR']['DOP'] = 'DOP - Dominican Republic Peso';
$GLOBALS['ISO_LANG']['CUR']['DZD'] = 'DZD - Algerian Dinar';
$GLOBALS['ISO_LANG']['CUR']['EGP'] = 'EGP - Egyptian Pound';
$GLOBALS['ISO_LANG']['CUR']['ERN'] = 'ERN - Eritrean Nakfa';
$GLOBALS['ISO_LANG']['CUR']['ETB'] = 'ETB - Ethiopian Birr';
$GLOBALS['ISO_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['ISO_LANG']['CUR']['FJD'] = 'FJD - Fiji Dollar';
$GLOBALS['ISO_LANG']['CUR']['FKP'] = 'FKP - Falkland Pound';
$GLOBALS['ISO_LANG']['CUR']['GBP'] = 'GBP - Pound Sterling';
$GLOBALS['ISO_LANG']['CUR']['GEL'] = 'GEL - Lari';
$GLOBALS['ISO_LANG']['CUR']['GHS'] = 'GHS - Ghanaian cedi';
$GLOBALS['ISO_LANG']['CUR']['GIP'] = 'GIP - Gibraltar Pound';
$GLOBALS['ISO_LANG']['CUR']['GMD'] = 'GMD - Dalasi';
$GLOBALS['ISO_LANG']['CUR']['GNF'] = 'GNF - Guinea Franc';
$GLOBALS['ISO_LANG']['CUR']['GTQ'] = 'GTQ - Quetzal';
$GLOBALS['ISO_LANG']['CUR']['GYD'] = 'GYD - Guyana Dollar';
$GLOBALS['ISO_LANG']['CUR']['HKD'] = 'HKD - Hong Kong Dollar';
$GLOBALS['ISO_LANG']['CUR']['HNL'] = 'HNL - Lempira';
$GLOBALS['ISO_LANG']['CUR']['HRD'] = 'HRD - Croatian Dinar';
$GLOBALS['ISO_LANG']['CUR']['HRK'] = 'HRK - Croatian Kuna';
$GLOBALS['ISO_LANG']['CUR']['HTG'] = 'HTG - Gourde';
$GLOBALS['ISO_LANG']['CUR']['HUF'] = 'HUF - Forint';
$GLOBALS['ISO_LANG']['CUR']['IDR'] = 'IDR - Rupiah';
$GLOBALS['ISO_LANG']['CUR']['ILS'] = 'ILS - Shekel';
$GLOBALS['ISO_LANG']['CUR']['INR'] = 'INR - Indian Rupee';
$GLOBALS['ISO_LANG']['CUR']['IQD'] = 'IQD - Iraqi Dinar';
$GLOBALS['ISO_LANG']['CUR']['IRR'] = 'IRR - Iranian Rial';
$GLOBALS['ISO_LANG']['CUR']['ISK'] = 'ISK - Icelandic Króna';
$GLOBALS['ISO_LANG']['CUR']['JMD'] = 'JMD - Jamaican Dollar';
$GLOBALS['ISO_LANG']['CUR']['JOD'] = 'JOD - Jordanian Dinar';
$GLOBALS['ISO_LANG']['CUR']['JPY'] = 'JPY - Yen';
$GLOBALS['ISO_LANG']['CUR']['KES'] = 'KES - Kenyan Shilling';
$GLOBALS['ISO_LANG']['CUR']['KGS'] = 'KGS - Kyrgyzstani Som';
$GLOBALS['ISO_LANG']['CUR']['KHR'] = 'KHR - Riel';
$GLOBALS['ISO_LANG']['CUR']['KMF'] = 'KMF - Comorian Franc';
$GLOBALS['ISO_LANG']['CUR']['KPW'] = 'KPW - Democratic People\'s Republic of Korean Won';
$GLOBALS['ISO_LANG']['CUR']['KRW'] = 'KRW - Republic of Korean Won';
$GLOBALS['ISO_LANG']['CUR']['KWD'] = 'KWD - Kuwaiti Dinar';
$GLOBALS['ISO_LANG']['CUR']['KYD'] = 'KYD - Cayman Islands Dollar';
$GLOBALS['ISO_LANG']['CUR']['KZT'] = 'KZT - Tenge';
$GLOBALS['ISO_LANG']['CUR']['LAK'] = 'LAK - Kip';
$GLOBALS['ISO_LANG']['CUR']['LBP'] = 'LBP - Lebanese Pound';
$GLOBALS['ISO_LANG']['CUR']['LKR'] = 'LKR - Sri Lankan Rupee';
$GLOBALS['ISO_LANG']['CUR']['LRD'] = 'LRD - Liberian Dollar';
$GLOBALS['ISO_LANG']['CUR']['LSL'] = 'LSL - Loti';
$GLOBALS['ISO_LANG']['CUR']['LTL'] = 'LTL - Litas';
$GLOBALS['ISO_LANG']['CUR']['LVL'] = 'LVL - Lats';
$GLOBALS['ISO_LANG']['CUR']['LYD'] = 'LYD - Libyan Dinar';
$GLOBALS['ISO_LANG']['CUR']['MAD'] = 'MAD - Moroccan Dirham';
$GLOBALS['ISO_LANG']['CUR']['MDL'] = 'MDL - Moldavian Leu';
$GLOBALS['ISO_LANG']['CUR']['MGA'] = 'MGA - Malagasy ariary';
$GLOBALS['ISO_LANG']['CUR']['MKD'] = 'MKD - Macedonian Dinar';
$GLOBALS['ISO_LANG']['CUR']['MMK'] = 'MMK - Kyat';
$GLOBALS['ISO_LANG']['CUR']['MNT'] = 'MNT - Tugrik';
$GLOBALS['ISO_LANG']['CUR']['MOP'] = 'MOP - Pataca';
$GLOBALS['ISO_LANG']['CUR']['MRO'] = 'MRO - Ouguiya';
$GLOBALS['ISO_LANG']['CUR']['MUR'] = 'MUR - Mauritius Rupee';
$GLOBALS['ISO_LANG']['CUR']['MVR'] = 'MVR - Rufiyaa';
$GLOBALS['ISO_LANG']['CUR']['MWK'] = 'MWK - Malawian Kwacha';
$GLOBALS['ISO_LANG']['CUR']['MXN'] = 'MXN - Mexican Peso';
$GLOBALS['ISO_LANG']['CUR']['MYR'] = 'MYR - Ringgit (Malaysian Dollar)';
$GLOBALS['ISO_LANG']['CUR']['MZN'] = 'MZN - Mozambican metical';
$GLOBALS['ISO_LANG']['CUR']['NAD'] = 'NAD - Namibia Dollar';
$GLOBALS['ISO_LANG']['CUR']['NGN'] = 'NGN - Naira';
$GLOBALS['ISO_LANG']['CUR']['NIO'] = 'NIO - Cordoba Oro';
$GLOBALS['ISO_LANG']['CUR']['NOK'] = 'NOK - Norwegian Krone';
$GLOBALS['ISO_LANG']['CUR']['NPR'] = 'NPR - Nepalese Rupee';
$GLOBALS['ISO_LANG']['CUR']['NZD'] = 'NZD - New Zealand Dollar';
$GLOBALS['ISO_LANG']['CUR']['OMR'] = 'OMR - Omani Rial';
$GLOBALS['ISO_LANG']['CUR']['PAB'] = 'PAB - Balboa';
$GLOBALS['ISO_LANG']['CUR']['PEN'] = 'PEN - New Sol';
$GLOBALS['ISO_LANG']['CUR']['PGK'] = 'PGK - Kina';
$GLOBALS['ISO_LANG']['CUR']['PHP'] = 'PHP - Philippines Peso';
$GLOBALS['ISO_LANG']['CUR']['PKR'] = 'PKR - Pakistani Rupee';
$GLOBALS['ISO_LANG']['CUR']['PLN'] = 'PLN - Zloty';
$GLOBALS['ISO_LANG']['CUR']['PYG'] = 'PYG - Guarani';
$GLOBALS['ISO_LANG']['CUR']['QAR'] = 'QAR - Qatari Riyal';
$GLOBALS['ISO_LANG']['CUR']['RON'] = 'RON - Romanian New Leu';
$GLOBALS['ISO_LANG']['CUR']['RSD'] = 'RSD - Serbian Dinar';
$GLOBALS['ISO_LANG']['CUR']['RUB'] = 'RUB - Russian Federation Rouble';
$GLOBALS['ISO_LANG']['CUR']['RWF'] = 'RWF - Rwandan Franc';
$GLOBALS['ISO_LANG']['CUR']['SAR'] = 'SAR - Saudi Riyal';
$GLOBALS['ISO_LANG']['CUR']['SBD'] = 'SBD - Solomon Islands Dollar';
$GLOBALS['ISO_LANG']['CUR']['SCR'] = 'SCR - Seychelles Rupee';
$GLOBALS['ISO_LANG']['CUR']['SDG'] = 'SDG - Sudanese Pound';
$GLOBALS['ISO_LANG']['CUR']['SEK'] = 'SEK - Swedish Krona';
$GLOBALS['ISO_LANG']['CUR']['SGD'] = 'SGD - Singapore Dollar';
$GLOBALS['ISO_LANG']['CUR']['SHP'] = 'SHP - St. Helena Pound';
$GLOBALS['ISO_LANG']['CUR']['SLL'] = 'SLL - Leone';
$GLOBALS['ISO_LANG']['CUR']['SOS'] = 'SOS - Somali Shilling';
$GLOBALS['ISO_LANG']['CUR']['SRD'] = 'SRD - Surinamese Dollar';
$GLOBALS['ISO_LANG']['CUR']['SSP'] = 'SSP - South Sudanese Pound';
$GLOBALS['ISO_LANG']['CUR']['STD'] = 'STD - Dobra';
$GLOBALS['ISO_LANG']['CUR']['SVC'] = 'SVC - El Salvador Colon';
$GLOBALS['ISO_LANG']['CUR']['SYP'] = 'SYP - Syrian Pound';
$GLOBALS['ISO_LANG']['CUR']['SZL'] = 'SZL - Lilangeni';
$GLOBALS['ISO_LANG']['CUR']['THB'] = 'THB - Baht';
$GLOBALS['ISO_LANG']['CUR']['TJS'] = 'TJS - Tajikistani Somoni';
$GLOBALS['ISO_LANG']['CUR']['TMT'] = 'TMT - Turkmenistani Manat';
$GLOBALS['ISO_LANG']['CUR']['TND'] = 'TND - Tunisian Dinar';
$GLOBALS['ISO_LANG']['CUR']['TOP'] = 'TOP - Pa\'anga';
$GLOBALS['ISO_LANG']['CUR']['TRY'] = 'TRY - New Turkish Lira';
$GLOBALS['ISO_LANG']['CUR']['TTD'] = 'TTD - Trinidad and Tobago Dollar';
$GLOBALS['ISO_LANG']['CUR']['TWD'] = 'TWD - Taiwan Dollar';
$GLOBALS['ISO_LANG']['CUR']['TZS'] = 'TZS - Tanzanian Shilling';
$GLOBALS['ISO_LANG']['CUR']['UAH'] = 'UAH - Hryvna';
$GLOBALS['ISO_LANG']['CUR']['UGX'] = 'UGX - Ugandan Shilling';
$GLOBALS['ISO_LANG']['CUR']['USD'] = 'USD - United States Dollar';
$GLOBALS['ISO_LANG']['CUR']['UYU'] = 'UYU - Uruguayan Peso';
$GLOBALS['ISO_LANG']['CUR']['UZS'] = 'UZS - Uzbekistani Som';
$GLOBALS['ISO_LANG']['CUR']['VEF'] = 'VEF - Bolivar Fuerte';
$GLOBALS['ISO_LANG']['CUR']['VND'] = 'VND - Viet Nam Dông';
$GLOBALS['ISO_LANG']['CUR']['VUV'] = 'VUV - Vatu';
$GLOBALS['ISO_LANG']['CUR']['WST'] = 'WST - Tala';
$GLOBALS['ISO_LANG']['CUR']['XCD'] = 'XCD - East Caribbean Dollar';
$GLOBALS['ISO_LANG']['CUR']['YER'] = 'YER - Yemeni Riyal';
$GLOBALS['ISO_LANG']['CUR']['ZAR'] = 'ZAR - Rand';
$GLOBALS['ISO_LANG']['CUR']['ZMK'] = 'ZMK - Zambian Kwacha';
$GLOBALS['ISO_LANG']['CUR']['ZWL'] = 'ZWL - Zimbabwe Dollar';


/**
 * Currency symbols
 */
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['USD'] = '$';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['GBP'] = '£';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['JPY'] = '¥';

