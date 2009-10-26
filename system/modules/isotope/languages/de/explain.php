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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'] = array
(
	array('##order_id##', 'Eindeutige Nummer dieser Bestellung'),
	array('##items##', 'Anzahl Artikel im Warenkorb'),
	array('##products##', 'Anzahl Produkte im Warenkorb'),
	array('##subTotal##', 'Subtotal der Bestellung'),
	array('##taxTotal##', 'Total der Mehrwertsteuer (ohne Versandkosten)'),
	array('##taxTotalWithShipping##', 'Total der Mehrwertsteuer (inkl. Versandkosten)'),
	array('##shippingPrice##', 'Total der Versandkosten'),
	array('##grandTotal##', 'Gesamttotal der Bestellung'),
	array('##cart_text##', 'Liste der bestellten Artikel im Text-Format'),
	array('##cart_html##', 'Liste der bestellten Artikel im HTML-Format'),
	array('##billing_address##<br />##billing_address_html##', 'Rechnungsadresse als Text<br />(auch als HTML mit &lt;br /&gt; verfügbar)'),
	array('##shipping_address##<br />##shipping_address_html##', 'Versandadresse als Text<br />(auch als HTML mit &lt;br /&gt; verfügbar)'),
	array('##shipping_method##', 'Bezeichnung der Versandmethode (wie im Backend eingegeben)'),
	array('##shipping_note##<br />##shipping_note_text##', 'Hinweismeldung der gewählten Versandmethode (auch als Nur-Text abrufbar).'),
	array('##payment_method##', 'Bezeichnung der Zahlungsmethode (wie im Backend eingegeben)'),
	array('##payment_note##<br />##payment_note_text##', 'Hinweismeldung der gewählten Zahlungsart (auch als Nur-Text abrufbar).'),
	array('##billing_firstname##<br />##billing_lastname##<br />...', 'Einzelne Felder der Rechnungsadresse.'),
	array('##shipping_firstname##<br />##shipping_lastname##<br />...', 'Einzelne Felder der Versandadresse.'),
);

$GLOBALS['TL_LANG']['XPL']['variantsWizard'] = array
(
	array('Indicating Price and Weight','You may specify either numeric changes from the product base price or else whole values.<br /><br /><h2>Whole Values</h2>To overwrite the base price, simply type in the new price (e.g. 15.50).<br /><br /><h2>Changes In Value</h2>To indicate a value change, use the plus (+) or minus(-) signs <strong>before</strong> the change value (e.g. 5/+5 or -7.5.<br /><br /><h2>Change in unit</h2>To make a change value a percentage, simply indicate the percentile <strong>after</strong> the number (e.g. 5% or +5% will calculate a 5% price increase for the given subproduct over the base price.)')

);

