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
 * @author     Kamil Kuźmiński <kamil.kuzminski@gmail.com>
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type']						= array('Typ płatności', 'Wybierz typ płatności (np. Authorize.net)');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name']						= array('Nazwa metody płatności', 'Wprowadź nazwę dla tej metody płatności. Zostanie ona użyta tylko w backendzie.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label']						= array('Etykieta metody płatności', 'Etykieta będzie wyświetlona dla klientów przy składaniu zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note']						= array('Notatka', 'Ta notatka może zostać wysłana w potwierdzeniu (##payment_note##).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price']						= array('Cena', '');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class']					= array('Klasa podatku', '');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status']			= array('Status nowych zamówień', 'Wybierz pasujący status dla nowych zamówień.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total']				= array('Minimalna wartość', 'Wprowadź liczbę większą niż zero by nie obsługiwać zamówień o mniejszej wartości.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total']				= array('Maksymalna wartość', 'Wprowadź liczbę większą niż zero by nie obsługiwać zamówień o większej wartości.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries']					= array('Dostępne kraje', 'Wybierz kraje, w których ta metoda płatności może być użyta.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules']			= array('Metody płatności', 'Możesz zastrzec tę metodę płatności do konkretnych metod dostawy.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types']				= array('Typy produktów', 'Możesz zastrzeć tę metodę płatności do konkretnych typów produktów. Jeśli koszyk zawiera pordukt, który nie jest tu wybrany, ta metoda płatności nie będzie dostępna.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type']					= array('Typ transakcji', 'Wybeirz czy chcesz od razu otrzymać pieniądze czy zatrzymać płatność na później (np. przy wysyłce).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account']				= array('PayPal Account', 'Enter your paypal account (email address).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid']			= array('Postfinance PSPID', 'The PSPID is your unique identification for the Postfinance system.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret']			= array('Postfinance SHA-1-IN signature', 'This will be used to validate the server communication.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method']			= array('Postfinance method', 'Type of data transfer from postfinance.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'] 				= array('Require Card Code Verification (CCV) Number', 'Choose this option if you would like to increase transaction security by requiring the card code verification number.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types']			= array('Allowed Credit Card Types', 'Select which credit cards the payment module accepts.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login']			= array('Authorize.net Login', 'Provided when you have completed signup for your gateway');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key']		= array('Authorize.net Transaction Key', 'Provided when you have completed signup for your gateway');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter']		= array('Authorize.net Delimiter', 'What character should be inserted as the data delimiter for the response?');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type']		= array('Authorize.net Transaction Type', 'Authorize and Capture, for example - the first stage is authorizing by validating the data entered by the customer and the next step is submitting for settlement, which is called "capture".');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups']						= array('Grupy użytkowników', 'Zastrzeż tę metodę płatności do konkretnych grup użytkowników.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected']      			= array('Chroń moduł', 'Pokaż metodę płatności tylko konkretnym grupom użytkowników.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests']         			= array('Pokaż tylko dla gości', 'Ukryj metodę płatności, jeśli użytkownik jest zalogowany.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug']						= array('Tryb debugowania', 'Włącz tryb testowy, bez rzeczywistego przyjmowania płatności.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled']					= array('Aktywny', 'Zaznacz tutaj jeśli moduł płatności ma być aktywny.');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['capture']	= 'Authorize and Capture';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['auth']		= 'Authorize Only';

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping']			= 'Orders without shipping';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE']			= array('Authorize and Capture', 'Transactions of this type will be sent for authorization. The transaction will be automatically picked up for settlement if approved. This is the default transaction type in the gateway. If no type is indicated when submitting transactions to the gateway, the gateway will assume that the transaction is of the type');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY']				= array('Authorize Only', 'Transactions of this type are submitted if the merchant wishes to validate the credit card for the amount of the goods sold. If the merchant does not have goods in stock or wishes to review orders before shipping the goods, this transaction type should be submitted. The gateway will send this type of transaction to the financial institution for approval. However this transaction will not be sent for settlement. If the merchant does not act on the transaction within 30 days, the transaction will no longer be available for capture.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY']			= array('Capture Only', 'This is a request to settle a transaction that was not submitted for authorization through the payment gateway. The gateway will accept this transaction if an authorization code is submitted. x_auth_code is a required field for CAPTURE_ONLY type transactions.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'] 				= array('Credit', 'This transaction is also referred to as a "Refund" and indicates to the gateway that money should flow from the merchant to the customer. The gateway will accept a credit or a refund request if the transaction submitted meets the following conditions: <ul><li>The transaction is submitted with the ID of the original transaction against which the credit is being issued</li><li>The gateway has a record of the original transaction.</li><li>The original transaction has been settled.</li><li>The sum of the amount submitted in the Credit transaction and all credits submitted against the original transaction is less than the original transaction amount.</li><li>The full or last four digits of the credit card number submitted with the credit transaction match the full or last four digits of the credit card number used in the original transaction.</li><li>The transaction is submitted within 120 days of the settlement date and time of the original transaction.</li></ul> A transaction key is required to submit a credit to the system.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID']					= array('Void', 'This transaction is an action on a previous transaction and is used to cancel the previous transaction and ensure it does not get sent for settlement. It can be done on any type of transaction (i.e., CREDIT, AUTH_CAPTURE, CAPTURE_ONLY, and AUTH_ONLY). The transaction will be accepted by the gateway if the following conditions are met: <ul><li>The transaction is submitted with the ID of the transaction that has to be voided.</li><li>The gateway has a record of the transaction referenced by the ID.</li><li>The transaction has not been sent for settlement.</li></ul>');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE']		= array('Prior Authorization and Capture', 'This transaction is used to request settlement for a transaction that was previously submitted as an AUTH_ONLY. The gateway will accept this transaction and initiate settlement if the following conditions are met: <ul> <li>The transaction is submitted with the ID of the original authorization-only transaction, which needs to be settled.</li> <li>The transaction ID is valid and the system has a record of the original authorization-only transaction being submitted.</li> <li>The original transaction referred to is not already settled or expired or errored.</li><li>The amount being requested for settlement in this transaction is less than or equal to the original authorized amount.</li></ul>If no amount is submitted in this transaction, the gateway will initiate settlement for the amount of the originally authorized transaction. <em>Note: If extended line item, tax, freight, and/or duty information was submitted with the original transaction, adjusted information may be submitted in the event that the transaction amount changed. If no adjusted line item, tax, freight, and/or duty information is submitted, the information submitted with the original transaction will apply.</em>');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend']		= 'Nazwa i type';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend']		= 'Dodatkowe notatki';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend']		= 'Ogólna konfiguracja';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend']		= 'Konfiguracja metody płatności';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend']		= 'Cena';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend']	= 'Szablon';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend']		= 'Zaawansowane ustawienia';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend']		= 'Ustawienia dostępności';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new']				= array('Nowa metoda płatności', 'Dodaj nową metodę płatności');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit']   			= array('Edytuj metodę płatności', 'Edytuj metodę płatności ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy']   			= array('Kopiuj metodę płatności', 'Kopiuj metodę płatności ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'] 			= array('Usuń metodę płatności', 'Usuń metodę płatności ID %s');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show']   			= array('Szczegóły metody płatności', 'Pokaż szczegóły metody płatności ID %s');
