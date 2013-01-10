<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type']						= array('Type of Payment Gateway', 'Select a particular payment gateway (e.g. Authorize.net)');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name']						= array('Payment Method Name', 'Enter a name for this payment method. This will only be used in the backend.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label']						= array('Payment Method Label', 'The label will be shown to customers on checkout.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note']						= array('Payment Note', 'This note can be sent in confirmation mails (##payment_note##).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price']						= array('Price', '');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class']					= array('Tax Class', '');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status']			= array('Status for new orders', 'Choose a matching status for new orders.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total']				= array('Minimum total', 'Enter a number greater zero to exclude this payment method for lower priced orders.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total']				= array('Maximum total', 'Enter a number greater zero to exclude this payment method for higher priced orders.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries']					= array('Available countries', 'Select the countries where this payment method may be used (customer\'s billing address).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules']			= array('Shipping methods', 'You can restrict this payment method to certain shipping methods (e.g. Cash only when picking up).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types']				= array('Product types', 'You can restrict this payment method to certain product types. If the cart contains a product type you have not selected, the payment module is not available.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type']					= array('Transaction type', 'Select if you want to instantly capture the money or authorize (and hold) for a later transaction (e.g. when shipping).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account']				= array('PayPal Account', 'Enter your paypal account (email address).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_user']			= array('Paypal Payflow Pro username', '');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor']			= array('Paypal Payflow Pro vendor', 'An alphanumeric string of about 10 characters.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner']			= array('Paypal Payflow Pro partner', 'Case-sensitive! Usual partner Ids are either "PayPal" or "PayPalUK".');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password']		= array('Paypal Payflow Pro api password', 'An alphanumeric string of about 11 characters');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType']		= array('Paypal Payflow Pro transaction type', 'Please select a transaction type.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid']			= array('Postfinance PSPID', 'The PSPID is your unique identification for the Postfinance system.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret']			= array('Postfinance SHA-1-IN signature', 'This will be used to validate the server communication.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method']			= array('Postfinance method', 'Type of data transfer from postfinance.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button']						= array('Checkout button', 'You can show a custom checkout button instead of the default one.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'] 				= array('Require Card Code Verification (CCV) Number', 'Choose this option if you would like to increase transaction security by requiring the card code verification number.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id']	= array('Cybersource merchant id', 'Enter your Cybersource merchant id here.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key']		= array('Cybersource transaction key', 'Provided when you have completed signup for your gateway');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type']		= array('Cybersource transaction type', 'Authorize and Capture, for example - the first stage is authorizing by validating the data entered by the customer and the next step is submitting for settlement, which is called "capture".');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types']			= array('Allowed Credit Card Types', 'Select which credit cards the payment module accepts.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login']			= array('Authorize.net Login', 'Provided when you have completed signup for your gateway');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key']		= array('Authorize.net Transaction Key', 'Provided when you have completed signup for your gateway');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter']		= array('Authorize.net Delimiter', 'What character should be inserted as the data delimiter for the response?');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type']		= array('Authorize.net Transaction Type', 'Authorize and Capture, for example - the first stage is authorizing by validating the data entered by the customer and the next step is submitting for settlement, which is called "capture".');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_id']               = array('Merchant-ID', 'Please enter your merchant ID.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_sign']             = array('HMAC Key', 'Please enter your HMAC key from the Datatrans control panel.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']	= array('Payment method', 'Please select a payment method for this module.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_sslmerchant']		= array('Seller ID', 'Please enter your seller ID (Händlerkennung).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_sslpassword']		= array('Password', 'Please enter your SSL-Password.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_merchantref']		= array('Reference', 'A reference that will be shown on the seller details page instead of the cart ID.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupId']			= array('ExperCash Popup-ID', 'Geben Sie die Popup-ID aus Ihrem ExperCash Portal ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_profile']			= array('ExperCash Profile', 'Geben Sie die dreistellige Profilnummer ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupKey']			= array('ExperCash Popup-Key', 'Geben Sie den Popup-Key aus Ihrem ExperCash Portal ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']	= array('Transaktionsart', 'Sie können eine Transaktionsart vordefinieren oder den Kunden wählen lassen.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_css']				= array('CSS-Vorlage', 'Wählen Sie eine CSS-Datei für die Übergabe an ExperCash.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_clearingtype'] 		= array('Clearing type', 'Please choose a clearing type.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_aid'] 				= array('PAYONE Account-ID', 'Please enter your unique PAYONE account ID.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_portalid'] 			= array('PAYONE Portal-ID', 'Please enter your unique PAYONE portal ID.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_key'] 				= array('Secret key', 'Enter the secret key you specified for this portal.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups']						= array('Member Groups', 'Restrict this payment method to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected']      			= array('Protect module', 'Show the payment method to certain member groups only.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests']         			= array('Show to guests only', 'Hide the payment method if a member is logged in.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug']						= array('Debug mode', 'For testing without actually capturing for payment.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled']					= array('Enabled', 'Check here if the payment module should be enabled in the store.');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['capture']	= 'Authorize and Capture';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['auth']		= 'Authorize Only';

$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_CAPTURE']	= 'Authorize and Capture';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_ONLY']		= 'Authorize Only';

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping']			= 'Orders without shipping';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE']			= array('Authorize and Capture', 'Transactions of this type will be sent for authorization. The transaction will be automatically picked up for settlement if approved. This is the default transaction type in the gateway. If no type is indicated when submitting transactions to the gateway, the gateway will assume that the transaction is of the type');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY']				= array('Authorize Only', 'Transactions of this type are submitted if the merchant wishes to validate the credit card for the amount of the goods sold. If the merchant does not have goods in stock or wishes to review orders before shipping the goods, this transaction type should be submitted. The gateway will send this type of transaction to the financial institution for approval. However this transaction will not be sent for settlement. If the merchant does not act on the transaction within 30 days, the transaction will no longer be available for capture.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY']			= array('Capture Only', 'This is a request to settle a transaction that was not submitted for authorization through the payment gateway. The gateway will accept this transaction if an authorization code is submitted. x_auth_code is a required field for CAPTURE_ONLY type transactions.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'] 				= array('Credit', 'This transaction is also referred to as a "Refund" and indicates to the gateway that money should flow from the merchant to the customer. The gateway will accept a credit or a refund request if the transaction submitted meets the following conditions: <ul><li>The transaction is submitted with the ID of the original transaction against which the credit is being issued</li><li>The gateway has a record of the original transaction.</li><li>The original transaction has been settled.</li><li>The sum of the amount submitted in the Credit transaction and all credits submitted against the original transaction is less than the original transaction amount.</li><li>The full or last four digits of the credit card number submitted with the credit transaction match the full or last four digits of the credit card number used in the original transaction.</li><li>The transaction is submitted within 120 days of the settlement date and time of the original transaction.</li></ul> A transaction key is required to submit a credit to the system.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID']					= array('Void', 'This transaction is an action on a previous transaction and is used to cancel the previous transaction and ensure it does not get sent for settlement. It can be done on any type of transaction (i.e., CREDIT, AUTH_CAPTURE, CAPTURE_ONLY, and AUTH_ONLY). The transaction will be accepted by the gateway if the following conditions are met: <ul><li>The transaction is submitted with the ID of the transaction that has to be voided.</li><li>The gateway has a record of the transaction referenced by the ID.</li><li>The transaction has not been sent for settlement.</li></ul>');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE']		= array('Prior Authorization and Capture', 'This transaction is used to request settlement for a transaction that was previously submitted as an AUTH_ONLY. The gateway will accept this transaction and initiate settlement if the following conditions are met: <ul> <li>The transaction is submitted with the ID of the original authorization-only transaction, which needs to be settled.</li> <li>The transaction ID is valid and the system has a record of the original authorization-only transaction being submitted.</li> <li>The original transaction referred to is not already settled or expired or errored.</li><li>The amount being requested for settlement in this transaction is less than or equal to the original authorized amount.</li></ul>If no amount is submitted in this transaction, the gateway will initiate settlement for the amount of the originally authorized transaction. <em>Note: If extended line item, tax, freight, and/or duty information was submitted with the original transaction, adjusted information may be submitted in the event that the transaction amount changed. If no adjusted line item, tax, freight, and/or duty information is submitted, the information submitted with the original transaction will apply.</em>');

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']['creditcard'] = 'Credit card';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']['maestro'] = 'Debig card';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']['directdebit'] = 'Direct debit';

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['automatic_payment_method'] = 'Auswahl der Zahlart durch den Endkunden';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['elv_buy'] = 'Zahlung per Lastschrift (ELV)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['elv_authorize'] = 'Prüfung und Speicherung von Kontodaten zum späteren Einzug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['cc_buy'] = 'Kreditkartenzahlung';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['cc_authorize'] = 'verbindliche Reservierung auf eine Kreditkarte zum späteren Einzug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['giropay'] = 'Transaktion über giropay';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['sofortueberweisung'] = 'Transaktion über Sofortüberweisung';

//Paypal Payflow Pro
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Sale'] = 'Authorization and Capture';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Authorization'] = 'Authorize Only';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend']		= 'Name &amp; Type';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend']		= 'Additional Notes';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend']		= 'General Configuration';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend']		= 'Payment Gateway Configuration';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend']		= 'Price';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend']	= 'Template';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend']		= 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend']		= 'Enabled settings';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new']				= array('New payment method', 'Create a New payment method');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit']   			= array('Edit payment method', 'Edit payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy']   			= array('Copy payment method', 'Copy payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'] 			= array('Delete payment method', 'Delete payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show']   			= array('Payment Method Details', 'Show details of payment method ID %s');
