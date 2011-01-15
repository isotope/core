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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['order_id']			= array('Bestellnummer');
$GLOBALS['TL_LANG']['tl_iso_orders']['date']				= array('Datum');
$GLOBALS['TL_LANG']['tl_iso_orders']['status']				= array('Bestellstatus');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address']	= array('Versandadresse');
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address']		= array('Rechnungsadresse');

$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal']		= array('Subtotal');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax']			= array('Umsatzsteuer-Betrag');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_shipping_cost']	= array('Versandkosten');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method']		= array('Versandart');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_comments']		= array('Kommentare');
$GLOBALS['TL_LANG']['tl_iso_orders']['gift_message']		= array('Geschenknachricht');
$GLOBALS['TL_LANG']['tl_iso_orders']['gift_wrap']			= array('Geschenkverpackung');



/**
 * Additional Operations
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'] = array('Bestellung drucken', 'Eine Rechnung für diese Bestellung drucken');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['new']    = array('Neue Bestellung', 'Eine neue Bestellung erstellen');
$GLOBALS['TL_LANG']['tl_iso_orders']['edit']   = array('Bestellung bearbeiten', 'Bestellung ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_iso_orders']['copy']   = array('Bestellung duplizieren', 'Bestellung ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'] = array('Bestellung löschen', 'Bestellung ID %s löschen');
$GLOBALS['TL_LANG']['tl_iso_orders']['show']   = array('Bestellungsdetails', 'Details der Bestellung ID %s anzeigen');

