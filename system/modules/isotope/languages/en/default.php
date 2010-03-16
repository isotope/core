<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Content Elements
 */
$GLOBALS['TL_LANG']['CTE']['attributeLinkRepeater']   = array('Attribute Filter Lister', 'This element generates a collection of hyperlinks from a selected product attribute filter.');


/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['systemColumn'] = 'Name `%s` is reserved for system use. Please choose different name.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate'] = 'You must specify a template for the button "%s".';
$GLOBALS['TL_LANG']['ERR']['noStoreIdFound'] = 'No store id was found associated with this module!';

$GLOBALS['TL_LANG']['ERR']['order_conditions'] = 'You must accept the terms & conditions to continue';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'] = 'Please create a store configuration';

$GLOBALS['TL_LANG']['ERR']['productNameMissing'] = '<no product name found>';
$GLOBALS['TL_LANG']['ERR']['noSubProducts'] = 'no sub-products found';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory']			= 'You have not yet placed any orders.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound']				= 'The requested order was not found.';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat']		= 'Currency formatting not found';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled'] = 'Search functionality is not enabled!';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired'] = 'You must be logged in to checkout.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption'] = 'Please select an option.';

$GLOBALS['TL_LANG']['ERR']['parentProductNotFound'] = 'The parent product id %s does not exist!';

$GLOBALS['TL_LANG']['MSC']['missingGoogleAnalyticsExtension'] = 'E-commerce tracking for Google Analytics has a dependency you have not installed. Please install the "Google Analytics" extension by Andreas Schempp to take advantage of google analytics e-commerce tracking. It is available through the extension repository or online from www.typolight.org';

//Checkout Errors
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] = 'A billing address was not fuond.  Please specify a billing address.';
$GLOBALS['TL_LANG']['ERR']['cc_num']				= 'Please provide a valid credit card number.';
$GLOBALS['TL_LANG']['ERR']['cc_type']				= 'Please select a credit card type.';
$GLOBALS['TL_LANG']['ERR']['cc_exp']				= 'Please provide a credit card expiration date in the mm/yy format.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv']				= 'Please provide a card code verification number (3 or 4 digits found on the front or back of the card).';
$GLOBALS['TL_LANG']['ERR']['cc_match']				= 'Your credit card number does not match the selected credit card type.';
$GLOBALS['TL_LANG']['ERR']['cc_exp_paypal']			= 'Please provide a credit card expiration date in the mm/yyyy format.';

//Address Book Errors
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist'] = 'This address does not exist in your address book.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'] = 'You have no address book entries.';


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['labelLanguage']			= 'Language';
$GLOBALS['TL_LANG']['MSC']['editLanguage']			= 'Edit';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage']		= 'Delete';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage']		= 'Generic';
$GLOBALS['TL_LANG']['MSC']['editingLanguage']		= 'ATTENTION: You are editing language-specific data!';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm']	= 'Are you sure you want to delete this language? There is no undo!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage']		= 'undefined';

$GLOBALS['TL_LANG']['MSC']['priceRangeLabel'] = 'From';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage'] = 'Loading...';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'] = 'Please select...';
$GLOBALS['TL_LANG']['MSC']['scalingImageWidth'] = '1200';
$GLOBALS['TL_LANG']['MSC']['scalingImageHeight'] = '1200'; 
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline']		= 'Order no %s / %s';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel']			= 'Your downloadable products';
$GLOBALS['TL_LANG']['MSC']['paypal_processing'] = 'Your PayPal payment is being processed. Please be patient...';
$GLOBALS['TL_LANG']['MSC']['paypal_processing_failed'] = 'Your PayPal payment could not be processed.';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed'] = 'Your payment count not be processed.<br /><br />Reason: %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel'] = 'View Details';
$GLOBALS['TL_LANG']['MSC']['mmNoImagesUploaded'] = 'No images uploaded.';
$GLOBALS['TL_LANG']['MSC']['mmUploadImage'] = 'Upload additional image';
$GLOBALS['TL_LANG']['MSC']['quantity'] = 'Quantity';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel'] = 'Search Term: ';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel'] = 'Search Fields: ';
$GLOBALS['TL_LANG']['MSC']['order_conditions'] = 'I agree to the terms & conditions';
$GLOBALS['TL_LANG']['MSC']['downloadCount'] = '%s download(s)';
$GLOBALS['TL_LANG']['MSC']['perPage'] = 'Products Per Page';
$GLOBALS['TL_LANG']['MSC']['searchTerms'] = 'Keywords';
$GLOBALS['TL_LANG']['MSC']['search'] = 'Search';
$GLOBALS['TL_LANG']['MSC']['clearFilters'] = 'Clear Filters';

$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Add To Cart';
$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = 'Add product %s to your cart';
		
$GLOBALS['TL_LANG']['MSC']['labelPagerSectionTitle'] = 'Page:';
$GLOBALS['TL_LANG']['MSC']['labelOrderBy'] = 'Order By:';
$GLOBALS['TL_LANG']['MSC']['noProducts'] = 'No products have been found.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation'] = "We're sorry, the product information you have requested is not showing up in our store. For further assistance please contact us.";
$GLOBALS['TL_LANG']['MSC']['productDescriptionLabel'] = 'Description';

$GLOBALS['TL_LANG']['MSC']['productDetailLabel'] = 'Details';
$GLOBALS['TL_LANG']['MSC']['productMediaLabel'] = 'Audio and Video';
$GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] = 'Options';

$GLOBALS['TL_LANG']['MSC']['previousStep']	= 'Back';
$GLOBALS['TL_LANG']['MSC']['nextStep']		= 'Continue';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']	= 'Order';

$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'No categories are associated with this product.';
$GLOBALS['TL_LANG']['MSC']['labelPerPage'] = 'Per Page';
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Sort By';
$GLOBALS['TL_LANG']['MSC']['labelSubmit'] = 'Submit';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants'] = 'Please Select';
$GLOBALS['TL_LANG']['MSC']['deleteImage'] = 'Remove';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'There are no items in your cart';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = 'Remove %s from your cart';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel'] = 'Order Subtotal: ';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Shipping';
$GLOBALS['TL_LANG']['MSC']['taxLabel'] = '%s Tax: ';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Order Total: ';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel'] = 'Selected Shipping Options: ';
$GLOBALS['TL_LANG']['MSC']['noVariants'] = 'No product variants found.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts'] = 'Generate Subproducts';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt'] = "(select)";
$GLOBALS['TL_LANG']['MSC']['actualPrice'] = 'Actual Price';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'No payment options are currently available';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'No shipping options are currently available.';

$GLOBALS['TL_LANG']['MSSC']['cartBT']					= 'Cart';
$GLOBALS['TL_LANG']['MSC']['checkoutBT']				= 'Proceed to Checkout';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT']		= 'Continue shopping';
$GLOBALS['TL_LANG']['MSC']['updateCartBT']				= 'Update Cart';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline']		= 'Order Status: %s';


$GLOBALS['TL_LANG']['MSC']['labelDonations']			= 'Pledge a gift of';
$GLOBALS['TL_LANG']['MSC']['addDonation']				= 'Donate';

//Registry language entries 
$GLOBALS['TL_LANG']['MSC']['registry']['lastname'] = 'Search by Last Name:';
$GLOBALS['TL_LANG']['MSC']['registry']['datestr'] = 'Enter Date of Event:';
$GLOBALS['TL_LANG']['MSC']['registry']['registrySearch'] = 'Search Gift Registries';
$GLOBALS['TL_LANG']['MSC']['registry']['registryManage'] = 'Save Your Gift Registry';
$GLOBALS['TL_LANG']['MSC']['registry']['emptyField'] = 'Please fill in both fields';
$GLOBALS['TL_LANG']['MSC']['registry']['noItemsInRegistry'] = 'There are no items in this registry available for purchase.';
$GLOBALS['TL_LANG']['MSC']['registry']['noresultsText'] = 'There are no registries matching your search: %s';
$GLOBALS['TL_LANG']['MSC']['registry']['sResults'] = '%s gift registries were found for %s on or around %s';
$GLOBALS['TL_LANG']['MSC']['notAvailableOnline'] = 'Call for Price';

//Addresses
$GLOBALS['TL_LANG']['addressBookLabel'] = 'Addresses';
$GLOBALS['TL_LANG']['editAddressLabel'] = 'Edit';
$GLOBALS['TL_LANG']['deleteAddressLabel'] = 'Delete';
$GLOBALS['TL_LANG']['createNewAddressLabel'] = 'Create New Address';
$GLOBALS['TL_LANG']['useBillingAddress'] = 'Use billing address';
$GLOBALS['TL_LANG']['differentShippingAddress'] = 'Different shipping address';


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
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header'] = 'Quantity';
$GLOBALS['TL_LANG']['MSC']['iso_price_header'] = 'Price';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header'] = 'SKU';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header'] = 'Product Name';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] = 'Name on Credit Card';


// Order status options
$GLOBALS['TL_LANG']['ORDER']['pending']		= 'Pending';
$GLOBALS['TL_LANG']['ORDER']['processing']	= 'Processing';
$GLOBALS['TL_LANG']['ORDER']['shipped']		= 'Shipped';
$GLOBALS['TL_LANG']['ORDER']['complete']	= 'Complete';
$GLOBALS['TL_LANG']['ORDER']['on_hold']		= 'On Hold';
$GLOBALS['TL_LANG']['ORDER']['cancelled']	= 'Cancelled';
$GLOBALS['TL_LANG']['ORDER']['test'] 		= 'Test Transaction';

// Payment status options
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['pending']		= 'Pending';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['processing']	= 'Processing';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['paid']			= 'Paid';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['failed']		= 'Failed';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['cancelled']	= 'Cancelled';

//Shipping language entries
$GLOBALS['TL_LANG']['MSC']['noItemsEligibleForShipping'] = 'This order consists solely of items that are not shipped.';


$GLOBALS['TL_LANG']['MSC']['low_to_high'] = 'lo to hi';
$GLOBALS['TL_LANG']['MSC']['high_to_low'] = 'hi to lo';
$GLOBALS['TL_LANG']['MSC']['a_to_z'] = 'A to Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a'] = 'Z to A';
$GLOBALS['TL_LANG']['MSC']['old_to_new'] = 'earlier to later';
$GLOBALS['TL_LANG']['MSC']['new_to_old'] = 'later to earlier';



/**
 * Isotope module labels
 */
$GLOBALS['TL_LANG']['ISO']['productSingle']		= '1 Product';
$GLOBALS['TL_LANG']['ISO']['productMultiple']	= '%s Products';

$GLOBALS['TL_LANG']['ISO']['shipping_address_message']	= 'Enter your shipping information or select an existing address.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message']	= 'Enter your billing information or select an existing address.';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message'] = 'Enter your billing information';
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
$GLOBALS['TL_LANG']['ISO']['shipping_method']				= 'Shipping Method';
$GLOBALS['TL_LANG']['ISO']['payment_method']				= 'Payment Method';
$GLOBALS['TL_LANG']['ISO']['order_conditions']				= 'Order Conditions';
$GLOBALS['TL_LANG']['ISO']['order_review']					= 'Order Review';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo']			= 'Change';
$GLOBALS['TL_LANG']['ISO']['cc_num']						= 'Credit Card Number';
$GLOBALS['TL_LANG']['ISO']['cc_type']						= 'Credit Card Type';
$GLOBALS['TL_LANG']['ISO']['cc_exp']						= 'Credit Card Expiration (mm/yy)';
$GLOBALS['TL_LANG']['ISO']['cc_ccv']						= 'CCV Number (3 or 4 digit code)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_paypal']					= 'Credit Card Expiration (mm/yyyy)';
$GLOBALS['TL_LANG']['ISO']['pay_with_paypal']				= array('Pay with PayPal', 'You will be redirected to the PayPal payment website. If you are not automatically redirected, please click on the "Pay now" button.', 'Pay now');


/**
 * Shipping modules
 */
$GLOBALS['TL_LANG']['SHIP']['collection']		= array('Collection');
$GLOBALS['TL_LANG']['SHIP']['order_total']		= array('Order total-based shipping');
$GLOBALS['TL_LANG']['SHIP']['flat']				= array('Flat-price shipping');
$GLOBALS['TL_LANG']['SHIP']['ups']				= array('UPS Live Rates and Service shipping');
$GLOBALS['TL_LANG']['SHIP']['usps']				= array('USPS Live Rates and Service shipping');

/** 
 * USPS-specific response rate codes
 */
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['FIRST CLASS'] = '0';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['PRIORITY'] = '1';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['EXPRESS HFP'] = '2';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['EXPRESS'] = '3';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['PARCEL'] = '4';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['EXPRESS SH'] = '23';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['BPM'] = '5';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['MEDIA'] = '6';
$GLOBALS['ISO']['MSC']['USPS']['DOMESTIC']['RRC']['LIBRARY'] = '7';

/*$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC']['EXPRESS'] = '1';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC']['PRIORITY'] = '2';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';
$GLOBALS['ISO']['MSC']['USPS']['INTERNATIONAL']['RRC'][''] = '';*/

//INTERNATIONAL
/*1Express Mail International
2Priority Mail International
4Global Express Guaranteed (Document and Non-document)
5Global Express Guaranteed Document  cx6Global Express Guaranteed Non-Document Rectangular shape
7Global Express Guaranteed Non-Document Non-Rectangular
8Priority Mail Flat Rate Envelope
9Priority Mail Flat Rate Box
10Express Mail International Flat Rate Envelope
11Priority Mail Large Flat Rate Box
12Global Express Guaranteed Envelope
13First Class Mail International Letters
14First Class Mail International Flats
15First Class Mail International Parcels
16Priority Mail Small Flat Rate Box
21PostCards
*/

/**
 * Payment modules
 */
$GLOBALS['TL_LANG']['PAY']['cash']				= array('Cash', 'Use this for all offline processed payment.');
$GLOBALS['TL_LANG']['PAY']['paypal']			= array('PayPal Standard Checkout', 'This PayPal module supports IPN (Instant Payment Notifications).');
$GLOBALS['TL_LANG']['PAY']['paypalpro']			= array('PayPal Website Payments Pro', 'This PayPal module is a full service credit card gateway using Paypals own Website Payments Pro gateway.  Recommended only for low-traffic situations with no transactions over $10,000.');
$GLOBALS['TL_LANG']['PAY']['paypalpayflowpro']	= array('PayPal Payflow Pro', 'The PayPal Payflow module is a full service credit card gateway, a more robust solution for most e-commerce sites.');
$GLOBALS['TL_LANG']['PAY']['postfinance']		= array('Postfinance', 'Payment gateway for the swiss post payment system that supports various card types. The store will be instantly notified about successfull transactions.');
$GLOBALS['TL_LANG']['PAY']['authorizedotnet']	= array('Authorize.net', 'An Authorize.net payment gateway.');


/**
 * Product types
 */
$GLOBALS['TL_LANG']['ISO_PRODUCT']['simple']	= array('Simple product', 'A regular product. Select this if nothing else matches.');
$GLOBALS['TL_LANG']['ISO_PRODUCT']['variant']	= array('Product with variant data', 'A product with variant data.');


/**
 * Credit card types
 */
$GLOBALS['TL_LANG']['CCT']['mc']				= 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['visa']				= 'Visa';
$GLOBALS['TL_LANG']['CCT']['amex']				= 'American Express';
$GLOBALS['TL_LANG']['CCT']['discover']			= 'Discover';
$GLOBALS['TL_LANG']['CCT']['jcb']				= 'JCB';
$GLOBALS['TL_LANG']['CCT']['diners']			= 'Diner\'s Club';
$GLOBALS['TL_LANG']['CCT']['enroute']			= 'EnRoute';

/**
 * Currencies
 */
$GLOBALS['TL_LANG']['CUR']['USD'] = 'USD - US Dollar';
$GLOBALS['TL_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['TL_LANG']['CUR']['CHF'] = 'CHF - Swiss Franc';
$GLOBALS['TL_LANG']['CUR']['GBP'] = 'GBP - Pound Sterling';


/**
 * Currency symbols
 */
$GLOBALS['TL_LANG']['CUR_SYMBOL']['USD'] = '$';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['GBP'] = '£';

/** 
 * Weight Units 
 */
$GLOBALS['ISO_WGT']['gram'] 	= 'grams';
$GLOBALS['ISO_WGT']['kg']		= 'kilograms';
$GLOBALS['ISO_WGT']['oz'] 		= 'ounces';
$GLOBALS['ISO_WGT']['lbs']		= 'pounds';


/**
 * Attributes
 */
$GLOBALS['TL_LANG']['ATTR']['text']		= 'Text (up to 255 characters)';
$GLOBALS['TL_LANG']['ATTR']['integer']	= 'Integer/Whole Numbers';
$GLOBALS['TL_LANG']['ATTR']['decimal']	= 'Decimal';
//$GLOBALS['TL_LANG']['tl_product_attributes']['shorttext'] = 'Short Text (up to 128 characters)';
$GLOBALS['TL_LANG']['ATTR']['textarea']	= 'Long Text (more than 255 characters)';
$GLOBALS['TL_LANG']['ATTR']['datetime']	= 'Date/Time value';
$GLOBALS['TL_LANG']['ATTR']['select']	= 'Select List';
$GLOBALS['TL_LANG']['ATTR']['checkbox']	= 'Checkbox';
$GLOBALS['TL_LANG']['ATTR']['options']	= 'Option List';
$GLOBALS['TL_LANG']['ATTR']['file']		= 'File Attachment';
$GLOBALS['TL_LANG']['ATTR']['media']	= 'Media (Images, Movies, Mp3s, etc.)';
$GLOBALS['TL_LANG']['ATTR']['label']	= 'Label/Fixed Display';
$GLOBALS['TL_LANG']['ATTR']['input']	= 'Accept Input From Customer';


