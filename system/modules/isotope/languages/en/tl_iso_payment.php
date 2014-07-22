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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment']['name']                                   = array('Payment Method Name', 'Enter a name for this payment method. This will only be used in the backend.');
$GLOBALS['TL_LANG']['tl_iso_payment']['label']                                  = array('Payment Method Label', 'The label will be shown to customers on checkout.');
$GLOBALS['TL_LANG']['tl_iso_payment']['type']                                   = array('Type of Payment Gateway', 'Select a particular payment gateway');
$GLOBALS['TL_LANG']['tl_iso_payment']['note']                                   = array('Payment Note', 'This note can be sent in confirmation mails (##payment_note##).');
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status']                       = array('Status for new orders', 'Choose a matching status for new orders.');
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total']                          = array('Minimum total', 'Enter a number greater zero to exclude this payment method for lower priced orders.');
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total']                          = array('Maximum total', 'Enter a number greater zero to exclude this payment method for higher priced orders.');
$GLOBALS['TL_LANG']['tl_iso_payment']['countries']                              = array('Available countries', 'Select the countries where this payment method may be used (customer\'s billing address).');
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules']                       = array('Shipping methods', 'You can restrict this payment method to certain shipping methods (e.g. Cash only when picking up).');
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types']                          = array('Product types', 'You can restrict this payment method to certain product types. If the cart contains a product type you have not selected, the payment method is not available.');
$GLOBALS['TL_LANG']['tl_iso_payment']['config_ids']                             = array('Store configurations', 'You can restrict this payment method to certain shop configurations.');
$GLOBALS['TL_LANG']['tl_iso_payment']['price']                                  = array('Price', 'Enter a price or percent value (e.g. "10" or "10%").');
$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class']                              = array('Tax Class', 'Please select a tax class for the price.');
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type']                             = array('Transaction type', 'Select if you want to instantly capture the money or authorize (and hold) for a later transaction (e.g. when shipping).');
$GLOBALS['TL_LANG']['tl_iso_payment']['paybyway_merchant_id']                   = array('Merchant ID', 'Please enter your Paybyway merchant ID. The sub-merchant ID is listed in the sub-merchants page in the merchant UI.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paybyway_private_key']                   = array('Private Key', 'Please enter your Paybyway private key.');
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_account']                         = array('PayPal Account', 'Enter the default email address from your PayPal-Account. Note: Check the correct spelling, and case-sensitive.');
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_user']                        = array('Paypal Payflow Pro username', '');
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_vendor']                      = array('Paypal Payflow Pro vendor', 'An alphanumeric string of about 10 characters.');
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_partner']                     = array('Paypal Payflow Pro partner', 'Case-sensitive! Usual partner Ids are either "PayPal" or "PayPalUK".');
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_password']                    = array('Paypal Payflow Pro api password', 'An alphanumeric string of about 11 characters');
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_transType']                   = array('Paypal Payflow Pro transaction type', 'Please select a transaction type.');
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_pspid']                              = array('PSPID', 'The PSPID is your unique identification for the payment method.');
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_http_method']                        = array('HTTP method', 'Type of HTTP data transfer from and to the servers.');
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']                        = array('Hash method', 'Hashing algorithm for data transfer from and to the servers.');
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_in']                            = array('SHA-IN signature', 'This will be used to validate the server to server communication.');
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_out']                           = array('SHA-OUT signature', 'This will be used to validate the server to server communication.');
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_dynamic_template']                   = array('Dynamic template URL', 'Enter a valid <strong>absolute</strong> URL to a dynamic template here.');
$GLOBALS['TL_LANG']['tl_iso_payment']['requireCCV']                             = array('Require Card Code Verification (CCV) Number', 'Choose this option if you would like to increase transaction security by requiring the card code verification number.');
$GLOBALS['TL_LANG']['tl_iso_payment']['allowed_cc_types']                       = array('Allowed Credit Card Types', 'Select which credit cards the payment method accepts.');
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_id']                           = array('Merchant-ID', 'Please enter your merchant ID.');
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_sign']                         = array('HMAC Key', 'Please enter your HMAC key from the Datatrans control panel.');
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']                = array('Payment method', 'Please select a payment method for this method.');
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslmerchant']                  = array('Seller ID', 'Please enter your seller ID (Händlerkennung).');
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword']                  = array('Password', 'Please enter your SSL-Password.');
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_merchantref']                  = array('Reference', 'A reference that will be shown on the seller details page instead of the cart ID.');
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_user_id']             = array('Customer ID', 'Your customer ID for sofortüberweisung.de');
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_id']          = array('Project ID', 'Your project ID for sofortüberweisung.de');
$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_password']    = array('Projekt password', 'Your project password for sofortüberweisung.de');
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_accountid']                     = array('Saferpay Account-ID', 'Please enter your unique Saferpay account id.');
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_description']                   = array('Checkout description', 'The customer will see this description on the Saferpay checkout page.');
$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_vtconfig']                      = array('Payment page configuration (VTCONFIG)', 'You can create different Payment Page configurations. If you want to use a specific one of them, enter its "Parameter for the request" value here.');
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupId']                      = array('ExperCash Popup-ID', 'Geben Sie die Popup-ID aus Ihrem ExperCash Portal ein.');
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_profile']                      = array('ExperCash Profile', 'Geben Sie die dreistellige Profilnummer ein.');
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupKey']                     = array('ExperCash Popup-Key', 'Geben Sie den Popup-Key aus Ihrem ExperCash Portal ein.');
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']                = array('Transaktionsart', 'Sie können eine Transaktionsart vordefinieren oder den Kunden wählen lassen.');
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_css']                          = array('CSS-Vorlage', 'Wählen Sie eine CSS-Datei für die Übergabe an ExperCash.');
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_clearingtype']                    = array('Clearing type', 'Please choose a clearing type.');
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_aid']                             = array('PAYONE subaccount ID (aid)', 'Please enter your unique PAYONE subaccount ID (aid).');
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_portalid']                        = array('PAYONE Portal ID', 'Please enter your unique PAYONE portal ID.');
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_key']                             = array('Secret key', 'Enter the secret key you specified for this portal.');
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_instId']                        = array('Installation ID', 'Please enter your WorldPay Installation ID');
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_callbackPW']                    = array('Transaction Password', 'Enter the same transaction password as in your WorldPay configuration.');
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_signatureFields']               = array('SignatureFields', 'Enter the same SignatureField value as in your WorldPay configuration.');
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_md5secret']                     = array('MD5 Secret', 'Enter the same MD5 secret value as in your WorldPay configuration.');
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_description']                   = array('Description', 'Enter a description for your store. It will be shown to the customer on the worldpay checkout process.');
$GLOBALS['TL_LANG']['tl_iso_payment']['groups']                                 = array('Member groups', 'Restrict this payment method to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_payment']['protected']                              = array('Protect payment method', 'Show the payment method to certain member groups only.');
$GLOBALS['TL_LANG']['tl_iso_payment']['guests']                                 = array('Show to guests only', 'Hide the payment method if a member is logged in.');
$GLOBALS['TL_LANG']['tl_iso_payment']['debug']                                  = array('Debug mode', 'For testing without actually capturing for payment.');
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled']                                = array('Enabled', 'Check here if the payment method should be enabled in the store.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_payment']['new']                        = array('New payment method', 'Create a new payment method');
$GLOBALS['TL_LANG']['tl_iso_payment']['edit']                       = array('Edit payment method', 'Edit payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment']['copy']                       = array('Copy payment method', 'Copy payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment']['delete']                     = array('Delete payment method', 'Delete payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment']['toggle']                     = array('Enable/disable payment method', 'Enable/disable payment method ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment']['show']                       = array('Payment method details', 'Show details of payment method ID %s');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_payment']['capture']                                                = array('Authorize and Capture', 'Transactions of this type will be sent for authorization. The transaction will be automatically picked up for settlement if approved.');
$GLOBALS['TL_LANG']['tl_iso_payment']['auth']                                                   = array('Authorize Only', 'Transactions of this type are submitted if the merchant wishes to validate the credit card for the amount of the goods sold. If the merchant does not have goods in stock or wishes to review orders before shipping the goods, this transaction type should be submitted.');
$GLOBALS['TL_LANG']['tl_iso_payment']['no_shipping']                                            = 'Orders without shipping';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['elv']                                          = 'Debit withdrawal';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['cc']                                           = 'Credit card';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['dc']                                           = 'Debit card';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['vor']                                          = 'Prepayment';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['rec']                                          = 'Invoice';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['sb']                                           = 'Online bank transfer';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['wlt']                                          = 'e-Wallet';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['creditcard']                  = 'Credit card';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['maestro']                     = 'Debig card';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['directdebit']                 = 'Direct debit';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['automatic_payment_method']    = 'Auswahl der Zahlart durch den Endkunden';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['elv_buy']                     = 'Zahlung per Lastschrift (ELV)';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['elv_authorize']               = 'Prüfung und Speicherung von Kontodaten zum späteren Einzug';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['cc_buy']                      = 'Kreditkartenzahlung';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['cc_authorize']                = 'verbindliche Reservierung auf eine Kreditkarte zum späteren Einzug';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['giropay']                     = 'Transaktion über giropay';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['sofortueberweisung']          = 'Transaktion über Sofortüberweisung';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha1']                                = 'SHA-1';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha256']                              = 'SHA-256';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha512']                              = 'SHA-512';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_payment']['type_legend']        = 'Name &amp; Type';
$GLOBALS['TL_LANG']['tl_iso_payment']['note_legend']        = 'Additional notes';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_legend']      = 'General configuration';
$GLOBALS['TL_LANG']['tl_iso_payment']['gateway_legend']     = 'Payment gateway configuration';
$GLOBALS['TL_LANG']['tl_iso_payment']['price_legend']       = 'Price';
$GLOBALS['TL_LANG']['tl_iso_payment']['template_legend']    = 'Template';
$GLOBALS['TL_LANG']['tl_iso_payment']['expert_legend']      = 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled_legend']     = 'Approval';
