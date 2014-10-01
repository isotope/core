<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['systemColumn']                      = 'Name "%s" is reserved for system use. Please choose a different name.';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']           = 'No store configuration available';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration']       = 'Please create a default store configuration.';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory']                 = 'You have not yet placed any orders.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound']                     = 'The requested order was not found.';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired']                  = 'You must be logged in to checkout.';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate']                  = 'A variant with this attributes is already available. Please select another combination.';
$GLOBALS['TL_LANG']['ERR']['discount']                          = 'Please enter whole numbers or decimals signed with + or - and optionally with a percentage.';
$GLOBALS['TL_LANG']['ERR']['surcharge']                         = 'Please enter whole numbers or decimals optionally with a percentage.';
$GLOBALS['TL_LANG']['ERR']['orderFailed']                       = 'Checkout failed. Please try again or choose another payment method.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries']              = 'You have no address book entries.';
$GLOBALS['TL_LANG']['ERR']['cartMinSubtotal']                   = 'The minimum order amount is %s. Please add more products before checkout.';
$GLOBALS['TL_LANG']['ERR']['productMinimumQuantity']            = 'The minimum quantity for "%s" is %s items. Please check your shopping cart.';
$GLOBALS['TL_LANG']['ERR']['imageInFallback']                   = 'This image has already been assigned to the fallback language.';
$GLOBALS['TL_LANG']['ERR']['datepickerRgxp']                    = 'Select an appropriate field validation (date, time, date and time) to enable the date picker.';
$GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder']              = 'The selected folder is empty.';
$GLOBALS['TL_LANG']['ERR']['checkoutNotAllowed']                = 'User checkout not allowed';
$GLOBALS['TL_LANG']['ERR']['collectionItemNotAvailable']        = 'This product is no longer available.';
$GLOBALS['TL_LANG']['ERR']['collectionErrorInItems']            = 'There are errors in your products.';
$GLOBALS['TL_LANG']['ERR']['cartErrorInItems']                  = 'Please resolve the errors in your cart before checking out.';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['editLanguage']                      = 'Edit';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage']                    = 'Delete';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage']                   = 'Fallback language';
$GLOBALS['TL_LANG']['MSC']['editingLanguage']                   = 'ATTENTION: You are editing language-specific data!';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm']             = 'Are you sure you want to delete this language? There is no undo!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage']                 = 'undefined';
$GLOBALS['TL_LANG']['MSC']['selectGroup']                       = 'Only show this group';
$GLOBALS['TL_LANG']['MSC']['filterByGroups']                    = 'Groups';
$GLOBALS['TL_LANG']['MSC']['filterByPages']                     = 'Pages';
$GLOBALS['TL_LANG']['MSC']['groupPickerHome']                   = 'Back to the group picker';
$GLOBALS['TL_LANG']['MSC']['allGroups']                         = 'Show all groups';
$GLOBALS['TL_LANG']['MSC']['noVariants']                        = 'There are no variants for this product.';
$GLOBALS['TL_LANG']['MSC']['copyFallback']                      = 'Duplicate Fallback';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline']              = 'Order no %s / %s';
$GLOBALS['TL_LANG']['MSC']['payment_processing']                = 'Your payment is being processed. Please be patient...';
$GLOBALS['TL_LANG']['MSC']['mmNoUploads']                       = 'No files uploaded.';
$GLOBALS['TL_LANG']['MSC']['mmUnknownError']                    = 'The file could not be uploaded for unknown reason. Please check the system log.';
$GLOBALS['TL_LANG']['MSC']['mmDrop']                            = 'Drop files here to upload';
$GLOBALS['TL_LANG']['MSC']['mmUpload']                          = 'Upload files';
$GLOBALS['TL_LANG']['MSC']['mmProcessing']                      = 'Processing dropped files...';
$GLOBALS['TL_LANG']['MSC']['undeletableRecords']                = 'Some records could not be deleted.';
$GLOBALS['TL_LANG']['MSC']['undeletableUnpublished']            = 'Some records could not be deleted and have been unpublished instead.';
$GLOBALS['TL_LANG']['MSC']['quantity']                          = 'Quantity';
$GLOBALS['TL_LANG']['MSC']['defaultSearchText']                 = 'search products';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel']                    = 'Your downloadable products';
$GLOBALS['TL_LANG']['MSC']['priceRangeLabel']                   = '<span class="from">From</span> %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel']                       = 'View Details';
$GLOBALS['TL_LANG']['MSC']['perPageLabel']                      = 'Products Per Page';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel']                  = 'Keywords';
$GLOBALS['TL_LANG']['MSC']['submitLabel']                       = 'Submit';
$GLOBALS['TL_LANG']['MSC']['clearFiltersLabel']                 = 'Clear Filters';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']             = 'Update';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart']        = 'Add To Cart';
$GLOBALS['TL_LANG']['MSC']['orderByLabel']                      = 'Order By:';
$GLOBALS['TL_LANG']['MSC']['noProducts']                        = 'No products have been found.';
$GLOBALS['TL_LANG']['MSC']['previousStep']                      = 'Back';
$GLOBALS['TL_LANG']['MSC']['nextStep']                          = 'Continue';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']                      = 'Order';
$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated']            = 'No categories are associated with this product.';
$GLOBALS['TL_LANG']['MSC']['labelSubmit']                       = 'Submit';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText']             = 'Remove';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart']                     = 'There are no items in your cart.';
$GLOBALS['TL_LANG']['MSC']['addedToCart']                       = 'The selected product has been added to your shopping cart.';
$GLOBALS['TL_LANG']['MSC']['cartMerged']                        = 'The products from your last visit have been readded. Please review your shopping cart items.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle']            = 'Remove %s from your cart';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel']                     = 'Order Subtotal: ';
$GLOBALS['TL_LANG']['MSC']['shippingLabel']                     = 'Shipping';
$GLOBALS['TL_LANG']['MSC']['paymentLabel']                      = 'Payment';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel']                   = 'Order Total: ';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules']                  = 'No payment options are currently available';
$GLOBALS['TL_LANG']['MSC']['noShippingModules']                 = 'No shipping options are currently available.';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails']                     = 'No orders emails found.';
$GLOBALS['TL_LANG']['MSC']['noOrders']                          = 'No orders found.';
$GLOBALS['TL_LANG']['MSC']['downloadsRemaining']                = '<br />%s downloads remaining';
$GLOBALS['TL_LANG']['MSC']['cartBT']                            = 'Shopping Cart';
$GLOBALS['TL_LANG']['MSC']['checkoutBT']                        = 'Proceed to Checkout';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT']                = 'Continue shopping';
$GLOBALS['TL_LANG']['MSC']['updateCartBT']                      = 'Update Cart';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline']               = 'Order Status: %s';
$GLOBALS['TL_LANG']['MSC']['checkboutStepBack']                 = 'Go back to step "%s"';
$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel']             = 'Create New Address';
$GLOBALS['TL_LANG']['MSC']['useBillingAddress']                 = 'Use billing address';
$GLOBALS['TL_LANG']['MSC']['useCustomerAddress']                = 'Use customer address';
$GLOBALS['TL_LANG']['MSC']['differentShippingAddress']          = 'Different shipping address';
$GLOBALS['TL_LANG']['MSC']['editAddressLabel']                  = 'Edit';
$GLOBALS['TL_LANG']['MSC']['deleteAddressLabel']                = 'Delete';
$GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm']              = 'Do you really want to delete this address? This cannot be undone.';
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title']                 = 'Invoice';
$GLOBALS['TL_LANG']['MSC']['iso_order_status']                  = 'Status';
$GLOBALS['TL_LANG']['MSC']['iso_order_date']                    = 'Order date';
$GLOBALS['TL_LANG']['MSC']['iso_order_total']                   = 'Total';
$GLOBALS['TL_LANG']['MSC']['iso_order_document_number']         = 'Order number';
$GLOBALS['TL_LANG']['MSC']['low_to_high']                       = 'lo to hi';
$GLOBALS['TL_LANG']['MSC']['high_to_low']                       = 'hi to lo';
$GLOBALS['TL_LANG']['MSC']['a_to_z']                            = 'A to Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a']                            = 'Z to A';
$GLOBALS['TL_LANG']['MSC']['old_to_new']                        = 'earlier to later';
$GLOBALS['TL_LANG']['MSC']['new_to_old']                        = 'later to earlier';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]              = 'Processing payment';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]              = 'You will be redirected to the payment gateway website.';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]              = 'Pay now';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]              = 'Please click on the "Pay now" button to continue.';
$GLOBALS['TL_LANG']['MSC']['paypalTransactionOnline']           = 'Click here to view this PayPal transaction online';
$GLOBALS['TL_LANG']['MSC']['productSingle']                     = '1 Product';
$GLOBALS['TL_LANG']['MSC']['productMultiple']                   = '%s Products';
$GLOBALS['TL_LANG']['MSC']['shipping_address_message']          = 'Enter your shipping information or select an existing address.';
$GLOBALS['TL_LANG']['MSC']['billing_address_message']           = 'Enter your billing information or select an existing address.';
$GLOBALS['TL_LANG']['MSC']['billing_address_guest_message']     = 'Enter your billing information';
$GLOBALS['TL_LANG']['MSC']['customer_address_message']          = 'Enter your customer information or select an existing address.';
$GLOBALS['TL_LANG']['MSC']['customer_address_guest_message']    = 'Enter your customer information';
$GLOBALS['TL_LANG']['MSC']['shipping_method_message']           = 'Select a shipping method.';
$GLOBALS['TL_LANG']['MSC']['shipping_method_missing']           = 'Please select a shipping method.';
$GLOBALS['TL_LANG']['MSC']['payment_method_message']            = 'Enter your payment information.';
$GLOBALS['TL_LANG']['MSC']['payment_method_missing']            = 'Please select a payment method.';
$GLOBALS['TL_LANG']['MSC']['order_review_message']              = 'Review and confirm your order details.';
$GLOBALS['TL_LANG']['MSC']['checkout_address']                  = 'Address';
$GLOBALS['TL_LANG']['MSC']['checkout_shipping']                 = 'Shipping';
$GLOBALS['TL_LANG']['MSC']['checkout_payment']                  = 'Payment';
$GLOBALS['TL_LANG']['MSC']['checkout_review']                   = 'Review';
$GLOBALS['TL_LANG']['MSC']['billing_address']                   = 'Billing Address';
$GLOBALS['TL_LANG']['MSC']['shipping_address']                  = 'Shipping Address';
$GLOBALS['TL_LANG']['MSC']['billing_shipping_address']          = 'Billing & Shipping Address';
$GLOBALS['TL_LANG']['MSC']['customer_address']                  = 'Customer Address';
$GLOBALS['TL_LANG']['MSC']['shipping_method']                   = 'Shipping Method';
$GLOBALS['TL_LANG']['MSC']['payment_method']                    = 'Payment Method';
$GLOBALS['TL_LANG']['MSC']['order_review']                      = 'Order Review';
$GLOBALS['TL_LANG']['MSC']['changeCheckoutInfo']                = 'Change';
$GLOBALS['TL_LANG']['MSC']['cc_num']                            = 'Credit card number';
$GLOBALS['TL_LANG']['MSC']['cc_type']                           = 'Credit card type';
$GLOBALS['TL_LANG']['MSC']['cc_ccv']                            = 'CCV number (3 or 4 digit code)';
$GLOBALS['TL_LANG']['MSC']['cc_exp_month']                      = 'Expiration month';
$GLOBALS['TL_LANG']['MSC']['cc_exp_year']                       = 'Expiration year';
$GLOBALS['TL_LANG']['MSC']['backendPaymentNotFound']            = 'Payment module not found!';
$GLOBALS['TL_LANG']['MSC']['backendShippingNotFound']           = 'Shipping module not found!';
$GLOBALS['TL_LANG']['MSC']['backendPaymentNoInfo']              = 'This payment module does not provide additional information.';
$GLOBALS['TL_LANG']['MSC']['backendShippingNoInfo']             = 'This shipping module does not provide additional information.';
$GLOBALS['TL_LANG']['MSC']['useDefault']                        = 'Use default value';
$GLOBALS['TL_LANG']['MSC']['activeStep']                        = 'active step: ';
$GLOBALS['TL_LANG']['MSC']['productcacheLoading']               = 'Loading products...';
$GLOBALS['TL_LANG']['MSC']['productcacheNoscript']              = 'Your browser does not support JavaScript. Please <a href="%s">click here</a> to load the product list.';
$GLOBALS['TL_LANG']['MSC']['noFilesInFolder']                   = 'No files in this folder';
$GLOBALS['TL_LANG']['MSC']['assetImportConfirmation']           = 'Imported file %s for product "%s"';
$GLOBALS['TL_LANG']['MSC']['assetImportNoFilesFound']           = 'The folder did not contain any files to be imported.';
$GLOBALS['TL_LANG']['MSC']['loadingProductData']                = 'Loading product data …';
$GLOBALS['TL_LANG']['MSC']['templatesConfig']                   = 'Store Config "%s"';
$GLOBALS['TL_LANG']['MSC']['splittedTaxRate']                   = 'Splitted';
$GLOBALS['TL_LANG']['MSC']['newOrders']                         = 'You have %s order(s) with status "%s"';
$GLOBALS['TL_LANG']['MSC']['checkoutStep']                      = 'Step %s of %s (%s) - ';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['minutes']             = 'Minutes';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['hours']               = 'Hours';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['days']                = 'Days';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['weeks']               = 'Weeks';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['months']              = 'Months';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['years']               = 'Years';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroductionLegend']         = 'Introduction';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroduction'][0]            = 'Welcome to Isotope eCommerce';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroduction'][1]            = 'Isotope is the premier open source eCommerce solution for Contao.';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroduction'][2]            = 'This is the page where most of the configuration will take place. <strong>You should start from the bottom of this page and work your way upward from right to left.</strong>';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroduction'][3]            = 'Visit the official <a href="http://isotopeecommerce.org" target="_blank">Isotope eCommerce website</a> for the latest news, configuration manuals and our fundraising campaigns. Thank you so much for considering giving something back!';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroduction'][4]            = 'The Isotope eCommerce Team';
$GLOBALS['TL_LANG']['MSC']['isotopeIntroduction'][5]            = 'Isotope is a product of %s in association with <a href="http://isotopeecommerce.org/de/team.html" target="_blank">the Isotope eCommerce Team</a>.';

$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['asc_id']       = 'by date added (ascending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['desc_id']      = 'by date added (descending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['asc_tstamp']   = 'by date updated (ascending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['desc_tstamp']  = 'by date updated (descending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['asc_name']     = 'by product name (ascending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['desc_name']    = 'by product name (descending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['asc_price']    = 'by price (ascending)';
$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy']['desc_price']   = 'by price (descending)';

$GLOBALS['TL_LANG']['MSC']['noShippingAddress']                 = 'Please fill in the shipping address.';
$GLOBALS['TL_LANG']['MSC']['noShippingRequiredMsg']             = 'None of the products in your shopping cart require shipping!';
$GLOBALS['TL_LANG']['MSC']['availableShippingMethodsMsg']       = 'The following shipping methods are available for your current shopping cart and the shipping address details you provided:';
$GLOBALS['TL_LANG']['MSC']['saveAddressButton']                 = 'Save address';
$GLOBALS['TL_LANG']['MSC']['billpay_agb_de'] = 'Mit der &Uuml;bermittlung der für die Abwicklung des Rechnungskaufs und einer Identitäts und Bonitätsprüfung erforderlichen Daten an die <a href="https://billpay.de/endkunden" target="_blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="https://www.billpay.de/kunden/agb#datenschutz" target="_blank">Datenschutzbestimmungen</a> von Billpay.';
$GLOBALS['TL_LANG']['MSC']['billpay_agb_ch'] = 'Mit der &Uuml;bermittlung der für die Abwicklung des Rechnungskaufs und einer Identitäts und Bonitätsprüfung erforderlichen Daten an die <a href="https://billpay.de/endkunden" target="_blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="https://www.billpay.de/kunden/agb-ch#datenschutz" target="_blank">Datenschutzbestimmungen</a> von Billpay.';
$GLOBALS['TL_LANG']['MSC']['billpay_agb_at'] = 'Mit der &Uuml;bermittlung der für die Abwicklung des Rechnungskaufs und einer Identitäts und Bonitätsprüfung erforderlichen Daten an die <a href="https://billpay.de/endkunden" target="_blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="https://www.billpay.de/kunden/agb-at#datenschutz" target="_blank">Datenschutzbestimmungen</a> von Billpay.';


/**
 * Content elements
 */
$GLOBALS['TL_LANG']['CTE']['isotope']                           = 'Isotope eCommerce';

/**
 * Shipping methods
 */
$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping.flat']    = array('Flat-price shipping', 'This is the default shipping method for regular shipping.');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping.group']   = array('Shipping group', 'With this method, you can group multiple shipping methods into one and calculate the price based on them.');

/**
 * Payment methods
 */
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.cash']                 = array('Cash', 'Use this for all offline processed payment.');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.paybyway']             = array('Paybyway', 'Implements payment provider <a href="https://www.paybyway.com/en/" target="_blank">Paybyway</a>.');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.paypal']               = array('PayPal Standard Checkout', 'This PayPal module supports IPN (Instant Payment Notifications).');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.postfinance']          = array('PostFinance', 'Payment gateway for the swiss post payment system that supports various card types. The store will be instantly notified about successfull transactions.');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.viveum']               = array('VIVEUM', 'Payment gateway for VIVEUM Zahlungssysteme GmbH.');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.datatrans']            = array('Datatrans', 'A payment module for the swiss payment gateway "Datatrans".');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.sparkasse']            = array('Sparkasse');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.saferpay']             = array('Saferpay');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.billpay_saferpay']     = array('BillPay (using Saferpay)');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.sofortueberweisung']   = array('sofortüberweisung.de');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.expercash']            = array('ExperCash');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.epay']                 = array('ePay', 'Implements payment provider <a href="http://www.epay.eu" target="_blank">ePay</a>.');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.payone']               = array('PayOne');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.worldpay']             = array('WorldPay');

/**
 * Documents
 */
$GLOBALS['TL_LANG']['MODEL']['tl_iso_document.standard']            = array('Standard');

/**
 * Galleries
 */
$GLOBALS['TL_LANG']['MODEL']['tl_iso_gallery.standard'] = array('Default gallery', '<p>Uses the lightbox/mediabox for full size images. Make sure you select the appropriate "moo_" template in your page layout configuration.</p><p>You can supply a "rel" attribute to the link target by using a pipe (e.g. "tl_files/video.mov|lightbox[400 300]"). If no "rel" attribute is supplied, the link will be opened in a new window.</p>');
$GLOBALS['TL_LANG']['MODEL']['tl_iso_gallery.inline']   = array('Inline gallery', 'Clicking on a gallery image will replace the main image.');

/**
 * Product types
 */
$GLOBALS['TL_LANG']['MODEL']['tl_iso_product.standard'] = array('Standard product', 'A default product. Select this if nothing else matches.');

/**
 * Credit card types
 */
$GLOBALS['TL_LANG']['CCT']['mc']                    = 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['visa']                  = 'Visa';
$GLOBALS['TL_LANG']['CCT']['amex']                  = 'American Express';
$GLOBALS['TL_LANG']['CCT']['discover']              = 'Discover';
$GLOBALS['TL_LANG']['CCT']['jcb']                   = 'JCB';
$GLOBALS['TL_LANG']['CCT']['diners']                = 'Diner\'s Club';
$GLOBALS['TL_LANG']['CCT']['enroute']               = 'EnRoute';
$GLOBALS['TL_LANG']['CCT']['carte_blanche']         = 'Carte Blanche';
$GLOBALS['TL_LANG']['CCT']['jal']                   = 'JAL';
$GLOBALS['TL_LANG']['CCT']['maestro']               = 'Maestro UK';
$GLOBALS['TL_LANG']['CCT']['delta']                 = 'Delta';
$GLOBALS['TL_LANG']['CCT']['solo']                  = 'Solo';
$GLOBALS['TL_LANG']['CCT']['visa_electron']         = 'Visa Electron';
$GLOBALS['TL_LANG']['CCT']['dankort']               = 'Dankort';
$GLOBALS['TL_LANG']['CCT']['laser']                 = 'Laser';
$GLOBALS['TL_LANG']['CCT']['carte_bleue']           = 'Carte Bleue';
$GLOBALS['TL_LANG']['CCT']['carta_si']              = 'Carta Si';
$GLOBALS['TL_LANG']['CCT']['enc_acct_num']          = 'Encoded Account Number';
$GLOBALS['TL_LANG']['CCT']['uatp']                  = 'Universal Air Travel Program';
$GLOBALS['TL_LANG']['CCT']['maestro_intl']          = 'Maestro International';
$GLOBALS['TL_LANG']['CCT']['ge_money_uk']           = 'GE Money UK';

/**
 * Attributes
 */
$GLOBALS['TL_LANG']['ATTR']['text']                 = array('Text field', 'A single-line input field for a short or medium text.');
$GLOBALS['TL_LANG']['ATTR']['textarea']             = array('Textarea', 'A multi-line input field for a medium or long text.');
$GLOBALS['TL_LANG']['ATTR']['select']               = array('Select menu', 'A single- or multi-line drop-down menu.<br /><i>This field type is suitable for product variants.</i>');
$GLOBALS['TL_LANG']['ATTR']['radio']                = array('Radio button menu', 'A list of multiple options from which one can be selected.<br /><i>This field type is suitable for product variants.</i>');
$GLOBALS['TL_LANG']['ATTR']['checkbox']             = array('Checkbox menu', 'A list of multiple options from which any can be selected.');
$GLOBALS['TL_LANG']['ATTR']['mediaManager']         = array('Media Manager', 'Upload images and other files to the Isotope eCommerce file system. Output is processed trough an IsotopeGallery class.');
$GLOBALS['TL_LANG']['ATTR']['conditionalselect']    = array('Conditional Select-Menu', 'show select-options based on another select menu.');
$GLOBALS['TL_LANG']['ATTR']['fileTree']             = array('File tree', 'A file tree for single or multiple files and folders.');
$GLOBALS['TL_LANG']['ATTR']['downloads']            = array('Downloads', 'Download files from the product, e.g. manuals, data sheets etc.');
$GLOBALS['TL_LANG']['ATTR']['upload']               = array('File upload', 'A single-line input field to upload a local file to the server.');
$GLOBALS['TL_LANG']['ATTR']['media']                = array('Video/Audio', 'Generates a video or audio player.');

/**
 * Currencies
 */
$GLOBALS['TL_LANG']['CUR']['AED'] = 'AED - United Arab Emirates Dirham';
$GLOBALS['TL_LANG']['CUR']['AFN'] = 'AFN - Afghani';
$GLOBALS['TL_LANG']['CUR']['ALL'] = 'ALL - Lek';
$GLOBALS['TL_LANG']['CUR']['AMD'] = 'AMD - Dram';
$GLOBALS['TL_LANG']['CUR']['ANG'] = 'ANG - Netherlands Antilles Guilder';
$GLOBALS['TL_LANG']['CUR']['AOA'] = 'AOA - Kwanza';
$GLOBALS['TL_LANG']['CUR']['ARS'] = 'ARS - Argentinian Nuevo Peso';
$GLOBALS['TL_LANG']['CUR']['AUD'] = 'AUD - Australian Dollar';
$GLOBALS['TL_LANG']['CUR']['AWG'] = 'AWG - Aruban Guilder';
$GLOBALS['TL_LANG']['CUR']['AZN'] = 'AZN - Azerbaijani Manat';
$GLOBALS['TL_LANG']['CUR']['BAM'] = 'BAM - Convertible Mark';
$GLOBALS['TL_LANG']['CUR']['BBD'] = 'BBD - Barbados Dollar';
$GLOBALS['TL_LANG']['CUR']['BDT'] = 'BDT - Taka';
$GLOBALS['TL_LANG']['CUR']['BGN'] = 'BGN - Bulgarian Lev';
$GLOBALS['TL_LANG']['CUR']['BHD'] = 'BHD - Bahraini Dinar';
$GLOBALS['TL_LANG']['CUR']['BIF'] = 'BIF - Burundi Franc';
$GLOBALS['TL_LANG']['CUR']['BMD'] = 'BMD - Bermudian Dollar';
$GLOBALS['TL_LANG']['CUR']['BND'] = 'BND - Brunei Dollar';
$GLOBALS['TL_LANG']['CUR']['BOB'] = 'BOB - Boliviano';
$GLOBALS['TL_LANG']['CUR']['BRL'] = 'BRL - Brazilian real';
$GLOBALS['TL_LANG']['CUR']['BSD'] = 'BSD - Bahamian Dollar';
$GLOBALS['TL_LANG']['CUR']['BTN'] = 'BTN - Ngultrum';
$GLOBALS['TL_LANG']['CUR']['BWP'] = 'BWP - Pula';
$GLOBALS['TL_LANG']['CUR']['BYR'] = 'BYR - Belarussian Rouble';
$GLOBALS['TL_LANG']['CUR']['BZD'] = 'BZD - Belize Dollar';
$GLOBALS['TL_LANG']['CUR']['CAD'] = 'CAD - Canadian Dollar';
$GLOBALS['TL_LANG']['CUR']['CDF'] = 'CDF - Congolese Franc';
$GLOBALS['TL_LANG']['CUR']['CHF'] = 'CHF - Swiss Franc';
$GLOBALS['TL_LANG']['CUR']['CLF'] = 'CLF - Unidades de Fomento';
$GLOBALS['TL_LANG']['CUR']['CLP'] = 'CLP - Chilean Peso';
$GLOBALS['TL_LANG']['CUR']['CNY'] = 'CNY - Yuan Renminbi';
$GLOBALS['TL_LANG']['CUR']['COP'] = 'COP - Colombian Peso';
$GLOBALS['TL_LANG']['CUR']['CRC'] = 'CRC - Costa Rican Colón';
$GLOBALS['TL_LANG']['CUR']['CUC'] = 'CUC - Peso Convertible';
$GLOBALS['TL_LANG']['CUR']['CUP'] = 'CUP - Cuban Peso';
$GLOBALS['TL_LANG']['CUR']['CVE'] = 'CVE - Escudo Caboverdiano';
$GLOBALS['TL_LANG']['CUR']['CZK'] = 'CZK - Czech Koruna';
$GLOBALS['TL_LANG']['CUR']['DJF'] = 'DJF - Djibouti Franc';
$GLOBALS['TL_LANG']['CUR']['DKK'] = 'DKK - Danish Krone';
$GLOBALS['TL_LANG']['CUR']['DOP'] = 'DOP - Dominican Republic Peso';
$GLOBALS['TL_LANG']['CUR']['DZD'] = 'DZD - Algerian Dinar';
$GLOBALS['TL_LANG']['CUR']['EGP'] = 'EGP - Egyptian Pound';
$GLOBALS['TL_LANG']['CUR']['ERN'] = 'ERN - Eritrean Nakfa';
$GLOBALS['TL_LANG']['CUR']['ETB'] = 'ETB - Ethiopian Birr';
$GLOBALS['TL_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['TL_LANG']['CUR']['FJD'] = 'FJD - Fiji Dollar';
$GLOBALS['TL_LANG']['CUR']['FKP'] = 'FKP - Falkland Pound';
$GLOBALS['TL_LANG']['CUR']['GBP'] = 'GBP - Pound Sterling';
$GLOBALS['TL_LANG']['CUR']['GEL'] = 'GEL - Lari';
$GLOBALS['TL_LANG']['CUR']['GHS'] = 'GHS - Ghanaian cedi';
$GLOBALS['TL_LANG']['CUR']['GIP'] = 'GIP - Gibraltar Pound';
$GLOBALS['TL_LANG']['CUR']['GMD'] = 'GMD - Dalasi';
$GLOBALS['TL_LANG']['CUR']['GNF'] = 'GNF - Guinea Franc';
$GLOBALS['TL_LANG']['CUR']['GTQ'] = 'GTQ - Quetzal';
$GLOBALS['TL_LANG']['CUR']['GYD'] = 'GYD - Guyana Dollar';
$GLOBALS['TL_LANG']['CUR']['HKD'] = 'HKD - Hong Kong Dollar';
$GLOBALS['TL_LANG']['CUR']['HNL'] = 'HNL - Lempira';
$GLOBALS['TL_LANG']['CUR']['HRD'] = 'HRD - Croatian Dinar';
$GLOBALS['TL_LANG']['CUR']['HRK'] = 'HRK - Croatian Kuna';
$GLOBALS['TL_LANG']['CUR']['HTG'] = 'HTG - Gourde';
$GLOBALS['TL_LANG']['CUR']['HUF'] = 'HUF - Forint';
$GLOBALS['TL_LANG']['CUR']['IDR'] = 'IDR - Rupiah';
$GLOBALS['TL_LANG']['CUR']['ILS'] = 'ILS - Shekel';
$GLOBALS['TL_LANG']['CUR']['INR'] = 'INR - Indian Rupee';
$GLOBALS['TL_LANG']['CUR']['IQD'] = 'IQD - Iraqi Dinar';
$GLOBALS['TL_LANG']['CUR']['IRR'] = 'IRR - Iranian Rial';
$GLOBALS['TL_LANG']['CUR']['ISK'] = 'ISK - Icelandic Króna';
$GLOBALS['TL_LANG']['CUR']['JMD'] = 'JMD - Jamaican Dollar';
$GLOBALS['TL_LANG']['CUR']['JOD'] = 'JOD - Jordanian Dinar';
$GLOBALS['TL_LANG']['CUR']['JPY'] = 'JPY - Yen';
$GLOBALS['TL_LANG']['CUR']['KES'] = 'KES - Kenyan Shilling';
$GLOBALS['TL_LANG']['CUR']['KGS'] = 'KGS - Kyrgyzstani Som';
$GLOBALS['TL_LANG']['CUR']['KHR'] = 'KHR - Riel';
$GLOBALS['TL_LANG']['CUR']['KMF'] = 'KMF - Comorian Franc';
$GLOBALS['TL_LANG']['CUR']['KPW'] = 'KPW - Democratic People\'s Republic of Korean Won';
$GLOBALS['TL_LANG']['CUR']['KRW'] = 'KRW - Republic of Korean Won';
$GLOBALS['TL_LANG']['CUR']['KWD'] = 'KWD - Kuwaiti Dinar';
$GLOBALS['TL_LANG']['CUR']['KYD'] = 'KYD - Cayman Islands Dollar';
$GLOBALS['TL_LANG']['CUR']['KZT'] = 'KZT - Tenge';
$GLOBALS['TL_LANG']['CUR']['LAK'] = 'LAK - Kip';
$GLOBALS['TL_LANG']['CUR']['LBP'] = 'LBP - Lebanese Pound';
$GLOBALS['TL_LANG']['CUR']['LKR'] = 'LKR - Sri Lankan Rupee';
$GLOBALS['TL_LANG']['CUR']['LRD'] = 'LRD - Liberian Dollar';
$GLOBALS['TL_LANG']['CUR']['LSL'] = 'LSL - Loti';
$GLOBALS['TL_LANG']['CUR']['LTL'] = 'LTL - Litas';
$GLOBALS['TL_LANG']['CUR']['LVL'] = 'LVL - Lats';
$GLOBALS['TL_LANG']['CUR']['LYD'] = 'LYD - Libyan Dinar';
$GLOBALS['TL_LANG']['CUR']['MAD'] = 'MAD - Moroccan Dirham';
$GLOBALS['TL_LANG']['CUR']['MDL'] = 'MDL - Moldavian Leu';
$GLOBALS['TL_LANG']['CUR']['MGA'] = 'MGA - Malagasy ariary';
$GLOBALS['TL_LANG']['CUR']['MKD'] = 'MKD - Macedonian Dinar';
$GLOBALS['TL_LANG']['CUR']['MMK'] = 'MMK - Kyat';
$GLOBALS['TL_LANG']['CUR']['MNT'] = 'MNT - Tugrik';
$GLOBALS['TL_LANG']['CUR']['MOP'] = 'MOP - Pataca';
$GLOBALS['TL_LANG']['CUR']['MRO'] = 'MRO - Ouguiya';
$GLOBALS['TL_LANG']['CUR']['MUR'] = 'MUR - Mauritius Rupee';
$GLOBALS['TL_LANG']['CUR']['MVR'] = 'MVR - Rufiyaa';
$GLOBALS['TL_LANG']['CUR']['MWK'] = 'MWK - Malawian Kwacha';
$GLOBALS['TL_LANG']['CUR']['MXN'] = 'MXN - Mexican Peso';
$GLOBALS['TL_LANG']['CUR']['MYR'] = 'MYR - Ringgit (Malaysian Dollar)';
$GLOBALS['TL_LANG']['CUR']['MZN'] = 'MZN - Mozambican metical';
$GLOBALS['TL_LANG']['CUR']['NAD'] = 'NAD - Namibia Dollar';
$GLOBALS['TL_LANG']['CUR']['NGN'] = 'NGN - Naira';
$GLOBALS['TL_LANG']['CUR']['NIO'] = 'NIO - Cordoba Oro';
$GLOBALS['TL_LANG']['CUR']['NOK'] = 'NOK - Norwegian Krone';
$GLOBALS['TL_LANG']['CUR']['NPR'] = 'NPR - Nepalese Rupee';
$GLOBALS['TL_LANG']['CUR']['NZD'] = 'NZD - New Zealand Dollar';
$GLOBALS['TL_LANG']['CUR']['OMR'] = 'OMR - Omani Rial';
$GLOBALS['TL_LANG']['CUR']['PAB'] = 'PAB - Balboa';
$GLOBALS['TL_LANG']['CUR']['PEN'] = 'PEN - New Sol';
$GLOBALS['TL_LANG']['CUR']['PGK'] = 'PGK - Kina';
$GLOBALS['TL_LANG']['CUR']['PHP'] = 'PHP - Philippines Peso';
$GLOBALS['TL_LANG']['CUR']['PKR'] = 'PKR - Pakistani Rupee';
$GLOBALS['TL_LANG']['CUR']['PLN'] = 'PLN - Zloty';
$GLOBALS['TL_LANG']['CUR']['PYG'] = 'PYG - Guarani';
$GLOBALS['TL_LANG']['CUR']['QAR'] = 'QAR - Qatari Riyal';
$GLOBALS['TL_LANG']['CUR']['RON'] = 'RON - Romanian New Leu';
$GLOBALS['TL_LANG']['CUR']['RSD'] = 'RSD - Serbian Dinar';
$GLOBALS['TL_LANG']['CUR']['RUB'] = 'RUB - Russian Federation Rouble';
$GLOBALS['TL_LANG']['CUR']['RWF'] = 'RWF - Rwandan Franc';
$GLOBALS['TL_LANG']['CUR']['SAR'] = 'SAR - Saudi Riyal';
$GLOBALS['TL_LANG']['CUR']['SBD'] = 'SBD - Solomon Islands Dollar';
$GLOBALS['TL_LANG']['CUR']['SCR'] = 'SCR - Seychelles Rupee';
$GLOBALS['TL_LANG']['CUR']['SDG'] = 'SDG - Sudanese Pound';
$GLOBALS['TL_LANG']['CUR']['SEK'] = 'SEK - Swedish Krona';
$GLOBALS['TL_LANG']['CUR']['SGD'] = 'SGD - Singapore Dollar';
$GLOBALS['TL_LANG']['CUR']['SHP'] = 'SHP - St. Helena Pound';
$GLOBALS['TL_LANG']['CUR']['SLL'] = 'SLL - Leone';
$GLOBALS['TL_LANG']['CUR']['SOS'] = 'SOS - Somali Shilling';
$GLOBALS['TL_LANG']['CUR']['SRD'] = 'SRD - Surinamese Dollar';
$GLOBALS['TL_LANG']['CUR']['SSP'] = 'SSP - South Sudanese Pound';
$GLOBALS['TL_LANG']['CUR']['STD'] = 'STD - Dobra';
$GLOBALS['TL_LANG']['CUR']['SVC'] = 'SVC - El Salvador Colon';
$GLOBALS['TL_LANG']['CUR']['SYP'] = 'SYP - Syrian Pound';
$GLOBALS['TL_LANG']['CUR']['SZL'] = 'SZL - Lilangeni';
$GLOBALS['TL_LANG']['CUR']['THB'] = 'THB - Baht';
$GLOBALS['TL_LANG']['CUR']['TJS'] = 'TJS - Tajikistani Somoni';
$GLOBALS['TL_LANG']['CUR']['TMT'] = 'TMT - Turkmenistani Manat';
$GLOBALS['TL_LANG']['CUR']['TND'] = 'TND - Tunisian Dinar';
$GLOBALS['TL_LANG']['CUR']['TOP'] = 'TOP - Pa\'anga';
$GLOBALS['TL_LANG']['CUR']['TRY'] = 'TRY - New Turkish Lira';
$GLOBALS['TL_LANG']['CUR']['TTD'] = 'TTD - Trinidad and Tobago Dollar';
$GLOBALS['TL_LANG']['CUR']['TWD'] = 'TWD - Taiwan Dollar';
$GLOBALS['TL_LANG']['CUR']['TZS'] = 'TZS - Tanzanian Shilling';
$GLOBALS['TL_LANG']['CUR']['UAH'] = 'UAH - Hryvna';
$GLOBALS['TL_LANG']['CUR']['UGX'] = 'UGX - Ugandan Shilling';
$GLOBALS['TL_LANG']['CUR']['USD'] = 'USD - United States Dollar';
$GLOBALS['TL_LANG']['CUR']['UYU'] = 'UYU - Uruguayan Peso';
$GLOBALS['TL_LANG']['CUR']['UZS'] = 'UZS - Uzbekistani Som';
$GLOBALS['TL_LANG']['CUR']['VEF'] = 'VEF - Bolivar Fuerte';
$GLOBALS['TL_LANG']['CUR']['VND'] = 'VND - Viet Nam Dông';
$GLOBALS['TL_LANG']['CUR']['VUV'] = 'VUV - Vatu';
$GLOBALS['TL_LANG']['CUR']['WST'] = 'WST - Tala';
$GLOBALS['TL_LANG']['CUR']['XCD'] = 'XCD - East Caribbean Dollar';
$GLOBALS['TL_LANG']['CUR']['YER'] = 'YER - Yemeni Riyal';
$GLOBALS['TL_LANG']['CUR']['ZAR'] = 'ZAR - Rand';
$GLOBALS['TL_LANG']['CUR']['ZMK'] = 'ZMK - Zambian Kwacha';
$GLOBALS['TL_LANG']['CUR']['ZWL'] = 'ZWL - Zimbabwe Dollar';

/**
 * Currency symbols
 */
$GLOBALS['TL_LANG']['CUR_SYMBOL']['CHF'] = 'Fr.';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['DKK'] = 'Kr.';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['GBP'] = '£';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['JPY'] = '¥';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['NOK'] = 'Kr.';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['SEK'] = 'Kr.';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['USD'] = '$';

