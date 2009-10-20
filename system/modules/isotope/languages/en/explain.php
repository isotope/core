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
 
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'] = array
(
	array('##order_id##', 'Unique number for this order'),
	array('##items##', 'Number of items in cart'),
	array('##products##', 'Products in cart'),
	array('##subTotal##', 'Subtotal of order'),
	array('##taxTotal##', 'Total of tax (excluding shipping)'),
	array('##taxTotalWithShipping##', 'Total tax (including shipping)'),
	array('##shippingPrice##', 'Shipping Price Total'),
	array('##grandTotal##', 'Grand Total'),
	array('##cart_text##', 'List of products in text format'),
	array('##cart_html##', 'List of products in HTML format'),
	array('##billing_address##<br />##billing_address_html##', 'Invoice address as text <br /> (also known as HTML with &lt;br /&gt; available)'),
	array('##shipping_address##<br />##shipping_address_html##', 'Shipping address as text<br />(auch als HTML mit &lt;br /&gt; verfügbar)'),
	array('##shipping_method##', 'Name of shipping method (as entered in the backend)'),
	array('##shipping_note##<br />##shipping_note_text##', 'Note the chosen shipping method message (also known as plain text available).'),
	array('##payment_method##', 'Name of payment method (as entered in the backend)'),
	array('##payment_note##<br />##payment_note_text##', 'Note the chosen payment method message (also known as plain text available).'),
	array('##billing_firstname##<br />##billing_lastname##<br />...', 'Individual fields of the billing address.'),
	array('##shipping_firstname##<br />##shipping_lastname##<br />...', 'Individual fields of the shipping address.'),
);

$GLOBALS['TL_LANG']['XPL']['variantsWizard'] = array
(
	array('Indicating Price and Weight','You may specify either numeric changes from the product base price or else whole values.<br /><br /><h2>Whole Values</h2>To overwrite the base price, simply type in the new price (e.g. 15.50).<br /><br /><h2>Changes In Value</h2>To indicate a value change, use the plus (+) or minus(-) signs <strong>before</strong> the change value (e.g. 5/+5 or -7.5.<br /><br /><h2>Change in unit</h2>To make a change value a percentage, simply indicate the percentile <strong>after</strong> the number (e.g. 5% or +5% will calculate a 5% price increase for the given subproduct over the base price.)')

);
