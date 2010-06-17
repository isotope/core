<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Content Elements
 */
$GLOBALS['TL_LANG']['CTE']['attributeLinkRepeater'] = array('Artikelmerkmal-Filter Auflistung', 'Dieses Element generiert eine Sammlung von Links eines Artikelmerkmal-Filters.');


/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['systemColumn']                = 'Der Name `%s` ist vom System reserviert. Bitte wählen Sie einen anderen Namen.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate']       = 'Für den Button "%s" müssen Sie ein Template angeben.';

$GLOBALS['TL_LANG']['ERR']['order_conditions']            = 'Sie müssen die AGB akzeptieren um fortzufahren';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']     = 'Keine Shop-Konfiguration verfügbar';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'] = 'Bitte erstellen Sie eine standard Shop-Konfiguration.';

$GLOBALS['TL_LANG']['ERR']['productNameMissing']          = '<keinen Produkt-Namen gefunden>';
$GLOBALS['TL_LANG']['ERR']['noSubProducts']               = 'keine Unter-Produkte gefunden';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory']           = 'Sie haben noch keine Bestellungen getätigt.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound']               = 'Die gewünschte Bestellung wurde nicht gefunden.';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat']       = 'Währungs-Formatierung nicht gefunden';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled']            = 'Die Suchfunktion ist nicht aktiviert!';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired']            = 'Für den Bezahlvorgang müssen Sie eingeloggt sein.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption']             = 'Bitte wählen Sie eine Option.';
$GLOBALS['TL_LANG']['ERR']['noAddressData']               = 'Zur Steuerberechnung werden Adressdaten benötigt!';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate']            = 'Eine Variante mit diesen Attributen ist bereits vorhanden. Wählen Sie bitte eine andere Zusammenstellung.';

//Checkout Errors
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] = 'Es wurde keine Rechnungs-Adresse gefunden. Bitte geben Sie eine Rechnungs-Adresse an.';
$GLOBALS['TL_LANG']['ERR']['cc_num']                = 'Geben Sie bitte eine gültige Kreditkarten-Nummer an.';
$GLOBALS['TL_LANG']['ERR']['cc_type']               = 'Bitte wählen Sie einen Kreditkarten-Typ.';
$GLOBALS['TL_LANG']['ERR']['cc_exp']                = 'Bitte geben Sie ein Kreditkarten-Ablaufdatum im Format mm/jj an.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv']                = 'Bitte geben Sie eine Kartenprüfnummer (CVC) an (3- od. 4-stellig auf der Vorder- od. Rückseite der Karte).';
$GLOBALS['TL_LANG']['ERR']['cc_match']              = 'Ihre Kreditkarten-Nummer stimmt nicht mit dem gewählten Kreditkarten-Typ überein.';
$GLOBALS['TL_LANG']['ERR']['cc_exp_paypal']         = 'Bitte geben Sie ein Kreditkarten-Ablaufdatum im Format mm/jjjj an.';

//Address Book Errors
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist']  = 'Diese Adresse ist nicht in Ihrem Adressbuch.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'] = 'Sie haben keine Adressbuch-Einträge.';


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['labelLanguage']         = 'Sprache';
$GLOBALS['TL_LANG']['MSC']['editLanguage']          = 'Bearbeiten';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage']        = 'Löschen';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage']       = 'Fallback';
$GLOBALS['TL_LANG']['MSC']['editingLanguage']       = 'ACHTUNG: Sie bearbeiten die sprachspezifischen Daten dieses Produkts!';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] = 'Soll diese Sprache wirklich gelöscht werden? Hierfür gibt es keine Rückgängig-Funktion!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage']     = 'undefiniert';
$GLOBALS['TL_LANG']['MSC']['noSurcharges']          = 'Es wurden keine Aufpreise gefunden.';

$GLOBALS['TL_LANG']['MSC']['priceRangeLabel']                = 'Ab';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage']             = 'Lade...';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel']         = 'Bitte auswählen...';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline']           = 'Bestellung Nr. %s vom %s';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel']                 = 'Ihre Produkte zum Herunterladen';
$GLOBALS['TL_LANG']['MSC']['paypal_processing']              = 'Ihre PayPal-Zahlung ist in Bearbeitung. Bitte haben Sie etwas Geduld...';
$GLOBALS['TL_LANG']['MSC']['paypal_processing_failed']       = 'Ihre PayPal-Zahlung konnte nicht durchgeführt werden.';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed'] = 'Ihre Zahlung konnte nicht durchgeführt werden.<br /><br />Begründung: %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel']                    = 'Details sehen';
$GLOBALS['TL_LANG']['MSC']['mmNoImagesUploaded']             = 'Keine Bilder hoch geladen.';
$GLOBALS['TL_LANG']['MSC']['mmUploadImage']                  = 'Zusätzliche Bilder hochladen';
$GLOBALS['TL_LANG']['MSC']['quantity']                       = 'Anzahl';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel']             = 'Suchbegriff: ';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel']              = 'Suchfelder: ';
$GLOBALS['TL_LANG']['MSC']['order_conditions']               = 'Ich akzeptiere die AGB';
$GLOBALS['TL_LANG']['MSC']['downloadCount']                  = '%s Download(s)';
$GLOBALS['TL_LANG']['MSC']['perPage']                        = 'Produkte pro Seite';
$GLOBALS['TL_LANG']['MSC']['searchTerms']                    = 'Suchwörter';
$GLOBALS['TL_LANG']['MSC']['search']                         = 'Suche';
$GLOBALS['TL_LANG']['MSC']['clearFilters']                   = 'Filter zurücksetzen';

$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']             = 'Aktualisieren';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart']        = 'In den Warenkorb';
$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = 'Produkt %s in den Warenkorb';

$GLOBALS['TL_LANG']['MSC']['labelPagerSectionTitle']    = 'Seite:';
$GLOBALS['TL_LANG']['MSC']['labelOrderBy']              = 'Sortieren nach:';
$GLOBALS['TL_LANG']['MSC']['noProducts']                = 'Zurzeit sind in dieser Kategorie keine Produkte vorhanden.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation'] = 'Das von Ihnen gewünschte Produkt ist leider nicht vorhanden oder wurde aus dem Shop entfernt. Für weitere Hilfe nehmen Sie bitte Kontakt mit uns auf.';
$GLOBALS['TL_LANG']['MSC']['productDescriptionLabel']   = 'Beschreibung';

$GLOBALS['TL_LANG']['MSC']['productDetailLabel']  = 'Details';
$GLOBALS['TL_LANG']['MSC']['productMediaLabel']   = 'Audio und Video';
$GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] = 'Optionen';

$GLOBALS['TL_LANG']['MSC']['previousStep']  = 'Zurück';
$GLOBALS['TL_LANG']['MSC']['nextStep']      = 'Weiter';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']  = 'Bestellen';

$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'Dieses Produkt ist keiner Kategorie zugeordnet.';
$GLOBALS['TL_LANG']['MSC']['labelPerPage']           = 'Pro Seite';
$GLOBALS['TL_LANG']['MSC']['labelSortBy']            = 'Sortieren nach';
$GLOBALS['TL_LANG']['MSC']['labelSubmit']            = 'Senden';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants']   = 'Bitte wählen Sie';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText']  = 'Entfernen';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart']          = 'Ihr Warenkorb ist leer.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = '%s aus Ihrem Warenkorb entfernen';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel']          = 'Zwischensumme';
$GLOBALS['TL_LANG']['MSC']['shippingLabel']          = 'Versandkosten';
$GLOBALS['TL_LANG']['MSC']['paymentLabel']           = 'Bezahlung';
$GLOBALS['TL_LANG']['MSC']['taxLabel']               = 'Enthaltene MwSt.: ';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel']        = 'Bestellsumme: ';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel']   = 'Gewählte Versand-Optionen: ';
$GLOBALS['TL_LANG']['MSC']['noVariants']             = 'Keine Produktvarianten gefunden.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts']    = 'Erzeuge Unter-Produkte';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt']       = "(wähle)";
$GLOBALS['TL_LANG']['MSC']['actualPrice']            = 'Aktueller Preis';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules']       = 'Zur Zeit sind keine Zahlungsoptionen verfügbar.';
$GLOBALS['TL_LANG']['MSC']['noShippingModules']      = 'Leider können die Produkte zur Zeit nicht in Ihr Land geliefert werden. Bitte wählen Sie eine andere Versand-Adresse oder nur Artikel die nicht versendet werden müssen (z.B. Download-Artikel).';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails']          = 'Keine Bestell-E-Mails gefunden.';

$GLOBALS['TL_LANG']['MSC']['cartBT']              = 'Warenkorb';
$GLOBALS['TL_LANG']['MSC']['checkoutBT']          = 'Zur Kasse';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT']  = 'Weiter einkaufen';
$GLOBALS['TL_LANG']['MSC']['updateCartBT']        = 'Warenkorb aktualisieren';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'] = 'Bestell-Status: %s';


//Addresses
$GLOBALS['TL_LANG']['addressBookLabel']         = 'Adressen';
$GLOBALS['TL_LANG']['editAddressLabel']         = 'Bearbeiten';
$GLOBALS['TL_LANG']['deleteAddressLabel']       = 'Löschen';
$GLOBALS['TL_LANG']['createNewAddressLabel']    = 'Neue Adresse anlegen';
$GLOBALS['TL_LANG']['useBillingAddress']        = 'Rechnungs-Adresse verwenden';
$GLOBALS['TL_LANG']['differentShippingAddress'] = 'Abweichende Versand-Adresse';


//Invoice language Entries
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title']            = 'Rechnung';
$GLOBALS['TL_LANG']['MSC']['iso_order_status']             = 'Status';
$GLOBALS['TL_LANG']['MSC']['iso_order_date']               = 'Bestelldatum';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header']   = 'Rechnungs-Adresse';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header']  = 'Versand-Adresse';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header']               = 'Davon MwSt.';
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header']          = 'Zwischensumme';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header']    = 'Versand und Bearbeitung';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] = 'Gesamtsumme';
$GLOBALS['TL_LANG']['MSC']['iso_order_items']              = 'Artikel';
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header']          = 'Anzahl';
$GLOBALS['TL_LANG']['MSC']['iso_price_header']             = 'Preis';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header']               = 'Artikelnr.';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header']      = 'Produktbezeichnung';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title']          = 'Name auf Kreditkarte';


// Order status options
$GLOBALS['TL_LANG']['ORDER']['pending']    = 'Anstehend';
$GLOBALS['TL_LANG']['ORDER']['processing'] = 'In Bearbeitung';
$GLOBALS['TL_LANG']['ORDER']['complete']   = 'Erledigt';
$GLOBALS['TL_LANG']['ORDER']['on_hold']    = 'Zurück gestellt';
$GLOBALS['TL_LANG']['ORDER']['cancelled']  = 'Storniert';


$GLOBALS['TL_LANG']['MSC']['low_to_high'] = 'niedrig - hoch';
$GLOBALS['TL_LANG']['MSC']['high_to_low'] = 'hoch - niedrig';
$GLOBALS['TL_LANG']['MSC']['a_to_z']      = 'A - Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a']      = 'Z - A';
$GLOBALS['TL_LANG']['MSC']['old_to_new']  = 'alt - neu';
$GLOBALS['TL_LANG']['MSC']['new_to_old']  = 'neu - alt';



/**
 * Isotope module labels
 */
$GLOBALS['TL_LANG']['ISO']['productSingle']   = '1 Produkt';
$GLOBALS['TL_LANG']['ISO']['productMultiple'] = '%s Produkte';

$GLOBALS['TL_LANG']['ISO']['shipping_address_message']      = 'Geben Sie Ihre Versand-Adresse ein, oder wählen Sie eine bestehende Adresse.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message']       = 'Geben Sie Ihre Rechnungs-Adresse ein, oder wählen Sie eine bestehende Adresse.';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message'] = 'Bitte geben Sie Ihre Rechnungs-Adresse ein.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_message']       = 'Wählen Sie eine Versandart.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_missing']       = 'Bitte wählen Sie eine Versandart.';
$GLOBALS['TL_LANG']['ISO']['payment_method_message']        = 'Wählen Sie eine Zahlungsart.';
$GLOBALS['TL_LANG']['ISO']['payment_method_missing']        = 'Bitte wählen Sie eine Zahlungsart.';
$GLOBALS['TL_LANG']['ISO']['order_review_message']          = 'Überprüfen und bestätigen Sie Ihre Bestellung.';

$GLOBALS['TL_LANG']['ISO']['checkout_address']          = 'Adresse';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping']         = 'Versand';
$GLOBALS['TL_LANG']['ISO']['checkout_payment']          = 'Bezahlung';
$GLOBALS['TL_LANG']['ISO']['checkout_review']           = 'Überprüfung';
$GLOBALS['TL_LANG']['ISO']['billing_address']           = 'Rechnungs-Adresse';
$GLOBALS['TL_LANG']['ISO']['shipping_address']          = 'Versand-Adresse';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address']  = 'Rechnungs- und Versand-Adresse';
$GLOBALS['TL_LANG']['ISO']['shipping_method']           = 'Versandart';
$GLOBALS['TL_LANG']['ISO']['payment_method']            = 'Zahlungsart';
$GLOBALS['TL_LANG']['ISO']['order_conditions']          = 'AGB akzeptieren';
$GLOBALS['TL_LANG']['ISO']['order_review']              = 'Bestellübersicht';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo']        = 'Ändern';
$GLOBALS['TL_LANG']['ISO']['cc_num']                    = 'Kreditkarten-Nummer';
$GLOBALS['TL_LANG']['ISO']['cc_type']                   = 'Kreditkarten-Typ';
$GLOBALS['TL_LANG']['ISO']['cc_exp']                    = 'Kreditkarten-Ablaufdatum (mm/yy)';
$GLOBALS['TL_LANG']['ISO']['cc_ccv']                    = 'CVC-Nummer (3- od. 4-stelliger Code)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_paypal']             = 'Kreditkarten-Ablaufdatum (mm/yyyy)';
$GLOBALS['TL_LANG']['ISO']['cc_issue_number']           = 'Kreditkarten-Ausgabenummer, 2-stellig (erforderlich für Maestro- und Solo-Karten).';
$GLOBALS['TL_LANG']['ISO']['cc_start_date']             = 'Kreditkarten-Ausstelldatum (erforderlich für Maestro- und Solo-Karten).';
$GLOBALS['TL_LANG']['ISO']['pay_with_paypal']           = array('Mit PayPal bezahlen', 'Sie werden nun automatisch zur PayPal Webseite weitergeleitet. Falls Sie nicht weitergeleitet werden, klicken Sie bitte auf "Jetzt bezahlen".', 'Jetzt bezahlen');


/**
 * Shipping modules
 */
$GLOBALS['TL_LANG']['SHIP']['collection']  = array('Abholung');
$GLOBALS['TL_LANG']['SHIP']['order_total'] = array('Order total-based shipping');
$GLOBALS['TL_LANG']['SHIP']['flat']        = array('Pauschalversand');
$GLOBALS['TL_LANG']['SHIP']['ups']         = array('UPS Live Rates and Service shipping');
$GLOBALS['TL_LANG']['SHIP']['usps']        = array('USPS Live Rates and Service shipping');


/**
 * Payment modules
 */
$GLOBALS['TL_LANG']['PAY']['cash']              = array('Barzahlung', 'Wählen Sie dies für alle offline Bezahlmethoden.');
$GLOBALS['TL_LANG']['PAY']['paypal']            = array('PayPal Standard', 'Dieses PayPal-Modul unterstützt die "Sofortige Zahlungsbestätigung" (IPN).');
$GLOBALS['TL_LANG']['PAY']['paypalpro']         = array('PayPal Website Payments Pro', 'This PayPal module is a full service credit card gateway using Paypals own Website Payments Pro gateway.  Recommended only for low-traffic situations with no transactions over $10,000.');
$GLOBALS['TL_LANG']['PAY']['paypalpayflowpro']  = array('PayPal Payflow Pro', 'The PayPal Payflow module is a full service credit card gateway, a more robust solution for most e-commerce sites.');
$GLOBALS['TL_LANG']['PAY']['postfinance']       = array('Postfinance (Schweizerische Post)', 'Schnittstelle des Bezahlsystems der Schweizerischen Post. Unterstützt verschiedene Karten. Der Shop wird sofort über erfolgreiche Transaktionen informiert.');
$GLOBALS['TL_LANG']['PAY']['authorizedotnet']   = array('Authorize.net', 'An Authorize.net payment gateway.');


/**
 * Product types
 */
$GLOBALS['TL_LANG']['ISO_PRODUCT']['regular'] = array('Normales Produkt', 'Ein Standard-Produkt. Wähle dieses, wenn sonst keins zutrifft.');


/**
 * Credit card types
 */
$GLOBALS['TL_LANG']['CCT']['mc']       = 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['visa']     = 'Visa';
$GLOBALS['TL_LANG']['CCT']['amex']     = 'American Express';
$GLOBALS['TL_LANG']['CCT']['discover'] = 'Discover';
$GLOBALS['TL_LANG']['CCT']['jcb']      = 'JCB';
$GLOBALS['TL_LANG']['CCT']['diners']   = 'Diner\'s Club';
$GLOBALS['TL_LANG']['CCT']['enroute']  = 'EnRoute';


/**
 * Attributes
 */
$GLOBALS['TL_LANG']['ATTR']['text']              = 'Text (bis zu 255 Zeichen)';
$GLOBALS['TL_LANG']['ATTR']['integer']           = 'Integer/Ganze Zahl';
$GLOBALS['TL_LANG']['ATTR']['decimal']           = 'Dezimalzahl';
$GLOBALS['TL_LANG']['ATTR']['textarea']          = 'Langer Text (mehr als 255 Zeichen)';
$GLOBALS['TL_LANG']['ATTR']['datetime']          = 'Datum/Zeit';
$GLOBALS['TL_LANG']['ATTR']['select']            = 'Auswahl-Liste';
$GLOBALS['TL_LANG']['ATTR']['checkbox']          = 'Checkbox';
$GLOBALS['TL_LANG']['ATTR']['options']           = 'Options-Liste';
$GLOBALS['TL_LANG']['ATTR']['file']              = 'Dateianhang';
$GLOBALS['TL_LANG']['ATTR']['media']             = 'Media (Bilder, Videos, MP3s, usw.)';
$GLOBALS['TL_LANG']['ATTR']['label']             = 'Beschriftung/Nur Ansicht';
$GLOBALS['TL_LANG']['ATTR']['input']             = 'Kundeneingabe erlauben';
$GLOBALS['TL_LANG']['ATTR']['conditionalselect'] = 'Abhängiges Select-Menü';


/**
 * Currencies
 * Quelle: http://publications.europa.eu/code/de/de-5000700.htm
 */
$GLOBALS['TL_LANG']['CUR']['AED'] = 'AED - VAE-Dirham';
$GLOBALS['TL_LANG']['CUR']['AFN'] = 'AFN - Afghani';
$GLOBALS['TL_LANG']['CUR']['ALL'] = 'ALL - Lek';
$GLOBALS['TL_LANG']['CUR']['AMD'] = 'AMD - Dram';
$GLOBALS['TL_LANG']['CUR']['ANG'] = 'ANG - Antillen-Gulden';
$GLOBALS['TL_LANG']['CUR']['AOA'] = 'AOA - Kwanza';
$GLOBALS['TL_LANG']['CUR']['ARS'] = 'ARS - argentinischer Peso';
$GLOBALS['TL_LANG']['CUR']['AUD'] = 'AUD - australischer Dollar';
$GLOBALS['TL_LANG']['CUR']['AWG'] = 'AWG - Aruba-Gulden';
$GLOBALS['TL_LANG']['CUR']['AZN'] = 'AZN - Aserbaidschan-Manat';
$GLOBALS['TL_LANG']['CUR']['BAM'] = 'BAM - konvertierbarer Mark';
$GLOBALS['TL_LANG']['CUR']['BBD'] = 'BBD - Barbados-Dollar';
$GLOBALS['TL_LANG']['CUR']['BDT'] = 'BDT - Taka';
$GLOBALS['TL_LANG']['CUR']['BGN'] = 'BGN - Lew';
$GLOBALS['TL_LANG']['CUR']['BHD'] = 'BHD - Bahrain-Dinar';
$GLOBALS['TL_LANG']['CUR']['BIF'] = 'BIF - Burundi-Franc';
$GLOBALS['TL_LANG']['CUR']['BMD'] = 'BMD - Bermuda- Dollar';
$GLOBALS['TL_LANG']['CUR']['BND'] = 'BND - Brunei-Dollar';
$GLOBALS['TL_LANG']['CUR']['BOB'] = 'BOB - Boliviano';
$GLOBALS['TL_LANG']['CUR']['BRL'] = 'BRL - Real';
$GLOBALS['TL_LANG']['CUR']['BSD'] = 'BSD - Bahama-Dollar';
$GLOBALS['TL_LANG']['CUR']['BTN'] = 'BTN - Ngultrum';
$GLOBALS['TL_LANG']['CUR']['BWP'] = 'BWP - Pula';
$GLOBALS['TL_LANG']['CUR']['BYR'] = 'BYR - Belarus-Rubel';
$GLOBALS['TL_LANG']['CUR']['BZD'] = 'BZD - Belize-Dollar';
$GLOBALS['TL_LANG']['CUR']['CAD'] = 'CAD - kanadischer Dollar';
$GLOBALS['TL_LANG']['CUR']['CDF'] = 'CDF - Kongo-Franc';
$GLOBALS['TL_LANG']['CUR']['CHF'] = 'CHF - Schweizer Franken';
$GLOBALS['TL_LANG']['CUR']['CLP'] = 'CLP - chilenischer Peso';
$GLOBALS['TL_LANG']['CUR']['CNY'] = 'CNY - Renminbi Yuan';
$GLOBALS['TL_LANG']['CUR']['COP'] = 'COP - kolumbianischer Peso';
$GLOBALS['TL_LANG']['CUR']['COU'] = 'COU - Unidad de Valor real';
$GLOBALS['TL_LANG']['CUR']['CRC'] = 'CRC - Costa-Rica-Colón';
$GLOBALS['TL_LANG']['CUR']['CUC'] = 'CUC - konvertibler Peso';
$GLOBALS['TL_LANG']['CUR']['CUP'] = 'CUP - kubanischer Peso';
$GLOBALS['TL_LANG']['CUR']['CVE'] = 'CVE - Kap-Verde-Escudo';
$GLOBALS['TL_LANG']['CUR']['CZK'] = 'CZK - tschechische Krone';
$GLOBALS['TL_LANG']['CUR']['DJF'] = 'DJF - Dschibuti-Franc';
$GLOBALS['TL_LANG']['CUR']['DKK'] = 'DKK - dänische Krone';
$GLOBALS['TL_LANG']['CUR']['DOP'] = 'DOP - dominikanischer Peso';
$GLOBALS['TL_LANG']['CUR']['DZD'] = 'DZD - algerischer Dinar';
$GLOBALS['TL_LANG']['CUR']['EEK'] = 'EEK - estnische Krone';
$GLOBALS['TL_LANG']['CUR']['EGP'] = 'EGP - ägyptisches Pfund';
$GLOBALS['TL_LANG']['CUR']['ERN'] = 'ERN - Nakfa';
$GLOBALS['TL_LANG']['CUR']['ETB'] = 'ETB - Birr';
$GLOBALS['TL_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['TL_LANG']['CUR']['FJD'] = 'FJD - Fidschi-Dollar';
$GLOBALS['TL_LANG']['CUR']['FKP'] = 'FKP - Falkland-Pfund';
$GLOBALS['TL_LANG']['CUR']['GBP'] = 'GBP - Pfund Sterling';
$GLOBALS['TL_LANG']['CUR']['GEL'] = 'GEL - Lari';
$GLOBALS['TL_LANG']['CUR']['GGP'] = 'GGP - Guernsey-Pfund';
$GLOBALS['TL_LANG']['CUR']['GHS'] = 'GHS - Ghana Cedi';
$GLOBALS['TL_LANG']['CUR']['GIP'] = 'GIP - Gibraltar-Pfund';
$GLOBALS['TL_LANG']['CUR']['GMD'] = 'GMD - Dalasi';
$GLOBALS['TL_LANG']['CUR']['GNF'] = 'GNF - Guinea-Franc';
$GLOBALS['TL_LANG']['CUR']['GTQ'] = 'GTQ - Quetzal';
$GLOBALS['TL_LANG']['CUR']['GYD'] = 'GYD - Guyana-Dollar';
$GLOBALS['TL_LANG']['CUR']['HKD'] = 'HKD - Hongkong-Dollar';
$GLOBALS['TL_LANG']['CUR']['HNL'] = 'HNL - Lempira';
$GLOBALS['TL_LANG']['CUR']['HRK'] = 'HRK - Kuna';
$GLOBALS['TL_LANG']['CUR']['HTG'] = 'HTG - Gourde';
$GLOBALS['TL_LANG']['CUR']['HUF'] = 'HUF - Forint';
$GLOBALS['TL_LANG']['CUR']['IDR'] = 'IDR - Rupiah';
$GLOBALS['TL_LANG']['CUR']['ILS'] = 'ILS - Schekel';
$GLOBALS['TL_LANG']['CUR']['IMP'] = 'IMP - Isle of Man-Pfund';
$GLOBALS['TL_LANG']['CUR']['INR'] = 'INR - indische Rupie';
$GLOBALS['TL_LANG']['CUR']['IQD'] = 'IQD - Irak-Dinar';
$GLOBALS['TL_LANG']['CUR']['IRR'] = 'IRR - iranischer Rial';
$GLOBALS['TL_LANG']['CUR']['ISK'] = 'ISK - isländische Krone';
$GLOBALS['TL_LANG']['CUR']['JEP'] = 'JEP - Jersey-Pfund';
$GLOBALS['TL_LANG']['CUR']['JMD'] = 'JMD - Jamaika-Dollar';
$GLOBALS['TL_LANG']['CUR']['JOD'] = 'JOD - Jordan-Dinar';
$GLOBALS['TL_LANG']['CUR']['JPY'] = 'JPY - Yen';
$GLOBALS['TL_LANG']['CUR']['KES'] = 'KES - Kenia-Schilling';
$GLOBALS['TL_LANG']['CUR']['KGS'] = 'KGS - Som';
$GLOBALS['TL_LANG']['CUR']['KHR'] = 'KHR - Riel';
$GLOBALS['TL_LANG']['CUR']['KMF'] = 'KMF - Komoren-Franc';
$GLOBALS['TL_LANG']['CUR']['KPW'] = 'KPW - nordkoreanischer Won';
$GLOBALS['TL_LANG']['CUR']['KRW'] = 'KRW - südkoreanischer Won';
$GLOBALS['TL_LANG']['CUR']['KWD'] = 'KWD - Kuwait-Dinar';
$GLOBALS['TL_LANG']['CUR']['KYD'] = 'KYD - Kaiman-Dollar';
$GLOBALS['TL_LANG']['CUR']['KZT'] = 'KZT - Tenge';
$GLOBALS['TL_LANG']['CUR']['LAK'] = 'LAK - Kip';
$GLOBALS['TL_LANG']['CUR']['LBP'] = 'LBP - libanesisches Pfund';
$GLOBALS['TL_LANG']['CUR']['LKR'] = 'LKR - Sri-Lanka-Rupie';
$GLOBALS['TL_LANG']['CUR']['LRD'] = 'LRD - liberianischer Dollar';
$GLOBALS['TL_LANG']['CUR']['LSL'] = 'LSL - Loti';
$GLOBALS['TL_LANG']['CUR']['LTL'] = 'LTL - Litas';
$GLOBALS['TL_LANG']['CUR']['LVL'] = 'LVL - Lats';
$GLOBALS['TL_LANG']['CUR']['LYD'] = 'LYD - libyscher Dinar';
$GLOBALS['TL_LANG']['CUR']['MAD'] = 'MAD - marokkanischer Dirham';
$GLOBALS['TL_LANG']['CUR']['MDL'] = 'MDL - Moldau-Leu';
$GLOBALS['TL_LANG']['CUR']['MGA'] = 'MGA - Ariary';
$GLOBALS['TL_LANG']['CUR']['MKD'] = 'MKD - Denar';
$GLOBALS['TL_LANG']['CUR']['MMK'] = 'MMK - Kyat';
$GLOBALS['TL_LANG']['CUR']['MNT'] = 'MNT - Tugrik';
$GLOBALS['TL_LANG']['CUR']['MOP'] = 'MOP - Pataca';
$GLOBALS['TL_LANG']['CUR']['MRO'] = 'MRO - Ouguiya';
$GLOBALS['TL_LANG']['CUR']['MUR'] = 'MUR - Mauritius-Rupie';
$GLOBALS['TL_LANG']['CUR']['MVR'] = 'MVR - Rufiyaa';
$GLOBALS['TL_LANG']['CUR']['MWK'] = 'MWK - Malawi-Kwacha';
$GLOBALS['TL_LANG']['CUR']['MXN'] = 'MXN - mexikanischer Peso';
$GLOBALS['TL_LANG']['CUR']['MYR'] = 'MYR - Ringgit';
$GLOBALS['TL_LANG']['CUR']['MZN'] = 'MZN - Metical';
$GLOBALS['TL_LANG']['CUR']['NAD'] = 'NAD - Namibia-Dollar';
$GLOBALS['TL_LANG']['CUR']['NGN'] = 'NGN - Naira';
$GLOBALS['TL_LANG']['CUR']['NIO'] = 'NIO - Córdoba';
$GLOBALS['TL_LANG']['CUR']['NOK'] = 'NOK - norwegische Krone';
$GLOBALS['TL_LANG']['CUR']['NPR'] = 'NPR - nepalesische Rupie';
$GLOBALS['TL_LANG']['CUR']['NZD'] = 'NZD - neuseeländischer Dollar';
$GLOBALS['TL_LANG']['CUR']['OMR'] = 'OMR - Rial Omani';
$GLOBALS['TL_LANG']['CUR']['PAB'] = 'PAB - Balboa';
$GLOBALS['TL_LANG']['CUR']['PEN'] = 'PEN - Neuer Sol';
$GLOBALS['TL_LANG']['CUR']['PGK'] = 'PGK - Kina';
$GLOBALS['TL_LANG']['CUR']['PHP'] = 'PHP - philippinischer Peso';
$GLOBALS['TL_LANG']['CUR']['PKR'] = 'PKR - pakistanische Rupie';
$GLOBALS['TL_LANG']['CUR']['PLN'] = 'PLN - Zloty';
$GLOBALS['TL_LANG']['CUR']['PYG'] = 'PYG - Guarani';
$GLOBALS['TL_LANG']['CUR']['QAR'] = 'QAR - Katar-Riyal';
$GLOBALS['TL_LANG']['CUR']['RON'] = 'RON - rumänischer Leu';
$GLOBALS['TL_LANG']['CUR']['RSD'] = 'RSD - serbischer Dinar';
$GLOBALS['TL_LANG']['CUR']['RUB'] = 'RUB - russischer Rubel';
$GLOBALS['TL_LANG']['CUR']['RWF'] = 'RWF - Ruanda-Franc';
$GLOBALS['TL_LANG']['CUR']['SAR'] = 'SAR - Riyal';
$GLOBALS['TL_LANG']['CUR']['SBD'] = 'SBD - Salomonen-Dollar';
$GLOBALS['TL_LANG']['CUR']['SCR'] = 'SCR - Seychellen-Rupie';
$GLOBALS['TL_LANG']['CUR']['SDG'] = 'SDG - sudanesisches Pfund';
$GLOBALS['TL_LANG']['CUR']['SEK'] = 'SEK - schwedische Krone';
$GLOBALS['TL_LANG']['CUR']['SGD'] = 'SGD - Singapur-Dollar';
$GLOBALS['TL_LANG']['CUR']['SHP'] = 'SHP - St. Helena-Pfund';
$GLOBALS['TL_LANG']['CUR']['SLL'] = 'SLL - Leone';
$GLOBALS['TL_LANG']['CUR']['SOS'] = 'SOS - Somalia-Schilling';
$GLOBALS['TL_LANG']['CUR']['SRD'] = 'SRD - Suriname-Dollar';
$GLOBALS['TL_LANG']['CUR']['STD'] = 'STD - Dobra';
$GLOBALS['TL_LANG']['CUR']['SVC'] = 'SVC - El-Salvador-Colón';
$GLOBALS['TL_LANG']['CUR']['SYP'] = 'SYP - syrisches Pfund';
$GLOBALS['TL_LANG']['CUR']['SZL'] = 'SZL - Lilangeni';
$GLOBALS['TL_LANG']['CUR']['THB'] = 'THB - Baht';
$GLOBALS['TL_LANG']['CUR']['TJS'] = 'TJS - Somoni';
$GLOBALS['TL_LANG']['CUR']['TMT'] = 'TMT - Turkmenistan-Manat';
$GLOBALS['TL_LANG']['CUR']['TND'] = 'TND - tunesischer Dinar';
$GLOBALS['TL_LANG']['CUR']['TOP'] = 'TOP - Pa’anga';
$GLOBALS['TL_LANG']['CUR']['TRY'] = 'TRY - türkische Lira';
$GLOBALS['TL_LANG']['CUR']['TTD'] = 'TTD - Trinidad-und-Tobago-Dollar';
$GLOBALS['TL_LANG']['CUR']['TWD'] = 'TWD - neuer Taiwan-Dollar';
$GLOBALS['TL_LANG']['CUR']['TZS'] = 'TZS - Tansania-Schilling';
$GLOBALS['TL_LANG']['CUR']['UAH'] = 'UAH - Griwna';
$GLOBALS['TL_LANG']['CUR']['UGX'] = 'UGX - Uganda-Schilling';
$GLOBALS['TL_LANG']['CUR']['USD'] = 'USD - US-Dollar';
$GLOBALS['TL_LANG']['CUR']['UYU'] = 'UYU - uruguayischer Peso';
$GLOBALS['TL_LANG']['CUR']['UZS'] = 'UZS - Sum';
$GLOBALS['TL_LANG']['CUR']['VEF'] = 'VEF - Bolivar fuerte';
$GLOBALS['TL_LANG']['CUR']['VND'] = 'VND - Dong';
$GLOBALS['TL_LANG']['CUR']['VUV'] = 'VUV - Vatu';
$GLOBALS['TL_LANG']['CUR']['WST'] = 'WST - Tala';
$GLOBALS['TL_LANG']['CUR']['XAF'] = 'XAF - CFA-Franc (BEAC)';
$GLOBALS['TL_LANG']['CUR']['XCD'] = 'XCD - ostkaribischer Dollar';
$GLOBALS['TL_LANG']['CUR']['XOF'] = 'XOF - CFA-Franc (BCEAO)';
$GLOBALS['TL_LANG']['CUR']['XPF'] = 'XPF - CFP-Franc';
$GLOBALS['TL_LANG']['CUR']['YER'] = 'YER - Jemen-Rial';
$GLOBALS['TL_LANG']['CUR']['ZAR'] = 'ZAR - Rand';
$GLOBALS['TL_LANG']['CUR']['ZMK'] = 'ZMK - sambischer Kwacha';
$GLOBALS['TL_LANG']['CUR']['ZWL'] = 'ZWL - Simbabwe-Dollar';

