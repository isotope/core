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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Content Elements
 *
 */
$GLOBALS['TL_LANG']['CTE']['attributeLinkRepeater']   = array('Artikelmerkmal-Filter Auflistung', 'Dieses Element generiert eine Sammlung von Links eines Artikelmerkmal-Filters.');


/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['order_conditions'] = 'Sie müssen die AGB akzeptieren um fortzufahren';


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['order_conditions'] = 'AGB akzeptieren';
 
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Bestellen';
$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = '%s bestellen';
		
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Sortieren nach';
$GLOBALS['TL_LANG']['MSC']['deleteImage'] = 'Entfernen';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'Ihr Warenkorb ist leer.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = '%s aus dem Warenkorb entfernen';
$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'Dieses Produkt ist keiner Kategorie zugeordnet.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation'] = 'Das gewünschte Produkt ist nicht vorhanden oder wurde aus dem Shop entfernt.';

//Checkout language entries 
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['billing_information'] = 'Bitte geben Sie Ihre Adresse ein.';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['shipping_method'] = 'Wählen Sie eine Versandart.';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['payment_method'] = 'Wählen Sie eine Zahlungsart.';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['order_review'] = 'Überprüfen Sie Ihre Bestellung.';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['order_complete'] = 'Bestellung erfolgreich!';

$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['billing_information'] = 'Rechnungsadresse';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['shipping_information'] = 'Versandadresse';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['shipping_method'] = 'Versandmethode';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['payment_method'] = 'Zahlungsart';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['order_conditions'] = 'AGB akzeptieren';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['order_review'] = 'Bestellübersicht';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['order_complete'] = 'Bestellung erfolgreich!';

$GLOBALS['TL_LANG']['MSC']['previousStep']	= 'Zurück';
$GLOBALS['TL_LANG']['MSC']['nextStep']		= 'Weiter';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']	= 'Bestellen';

$GLOBALS['TL_LANG']['MSC']['subTotalLabel'] = 'Subtotal: ';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Total: ';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Versandkosten: ';
$GLOBALS['TL_LANG']['MSC']['taxLabel'] = 'Enthaltene MwSt: ';

$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'Zur Zeit sind keine Zahlungsoptionen verfügbar.';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'Zur Zeit sind keine Versandoptionen verfügbar.';



$GLOBALS['TL_LANG']['ISO']['productSingle']		= '1 Produkt';
$GLOBALS['TL_LANG']['ISO']['productMultiple']	= '%s Produkte';


/**
 * Shipping modules
 */
$GLOBALS['TL_LANG']['SHIP']['collection']		= array('Abholung');
$GLOBALS['TL_LANG']['SHIP']['flat']				= array('Pauschalversand');


/**
 * Payment modules
 */
$GLOBALS['TL_LANG']['PAY']['cash']				= array('Barzahlung');
$GLOBALS['TL_LANG']['PAY']['paypal']			= array('PayPal Standard');
$GLOBALS['TL_LANG']['PAY']['postfinance']		= array('Postfinance (Schweizerische Post)');

//Invoice language Entries
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] = 'Bestellung';
$GLOBALS['TL_LANG']['MSC']['iso_order_status'] = 'Status';
$GLOBALS['TL_LANG']['MSC']['iso_order_date'] = 'Bestelldatum';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'] = 'Rechnungsadresse';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'] = 'Versandadresse';
$GLOBALS['TL_LANG']['MSC']['iso_payment_info_header'] = 'Zahlungsart';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_info_header'] = 'Versandart';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header'] = 'Davon MwSt.';	
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] = 'Subtotal';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'] = 'Versandkosten';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] = 'Total';
$GLOBALS['TL_LANG']['MSC']['iso_order_items_header'] = 'Artikel';

// Order Status
$GLOBALS['TL_LANG']['MSC']['order_status_labels']['pending']		= 'Offen';
$GLOBALS['TL_LANG']['MSC']['order_status_labels']['processing']		= 'In Verarbeitung';
$GLOBALS['TL_LANG']['MSC']['order_status_labels']['shipped']		= 'Versendet';
$GLOBALS['TL_LANG']['MSC']['order_status_labels']['complete']		= 'Erledigt';
$GLOBALS['TL_LANG']['MSC']['order_status_labels']['on_hold']		= 'Wartend';
$GLOBALS['TL_LANG']['MSC']['order_status_labels']['cancelled']		= 'Abgebrochen';

// Payment Status
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['pending']		= 'Offen';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['processing']	= 'In Verarbeitung';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['paid']			= 'Bezahlt';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['failed']		= 'Fehlgeschlagen';
$GLOBALS['TL_LANG']['MSC']['payment_status_labels']['cancelled']	= 'Abgebrochen';

//Checkout language entries 
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['shipping_information'] = 'Wählen Sie die Versandadresse.';


//Address Book language entries
$GLOBALS['TL_LANG']['addressBookLabel'] = 'Adressen';
$GLOBALS['TL_LANG']['editAddressLabel'] = 'Bearbeiten';
$GLOBALS['TL_LANG']['deleteAddressLabel'] = 'Löschen';
$GLOBALS['TL_LANG']['createNewAddressLabel'] = 'Adresse erfassen';
$GLOBALS['TL_LANG']['useBillingAddress'] = 'Rechnungsadresse verwenden';
$GLOBALS['TL_LANG']['differentShippingAddress'] = 'Abweichende Lieferadresse';


/**
 * Currencies
 */
$GLOBALS['TL_LANG']['CUR']['USD'] = 'USD - US Dollar';
$GLOBALS['TL_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['TL_LANG']['CUR']['CHF'] = 'CHF - Schweizer Franken';


/** 
 * Default attributes
 */
$GLOBALS['TL_LANG']['ISO_ATTR']['product_name']					= array('Name', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_sku']					= array('Artikelnummer', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_weight']				= array('Gewicht', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_quantity']				= array('Anzahl', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_alias']				= array('Alias', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_visibility']			= array('Sichtbarkeit', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_teaser']				= array('Teaser', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_description']			= array('Beschreibung', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_price']				= array('Preis', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['product_price_override']		= array('Ersatzpreis', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['use_product_price_override']	= array('Ersatzpreis verwenden', '');
$GLOBALS['TL_LANG']['ISO_ATTR']['main_image']				= array('Medien', '');

