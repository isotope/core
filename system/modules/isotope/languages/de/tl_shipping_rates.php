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
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    Isotope 
 * @license    Commercial 
 * @filesource
 */



/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_shipping_rates']['description']		= array('Beschreibung', 'Eine Kurzbeschreibung der Versandkosten-Regel. Wird im Frontend angezeigt.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['rate']			= array('Kosten', 'Der Preis in der aktiven Währung.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['upper_limit']		= array('Obere Grenze', 'Die obere Grenze für die aktuelle Vergleichsmethode (Preis oder Gewicht).');
$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_zip']		= array('Ziel Postleitzahl', 'Zips used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_country']	= array('Ziel Land', 'Country used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_region']		= array('Ziel Region', 'Region (county) used in the shipping destination for this rate.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_shipping_rates']['new']    = array('Neue Versandkosten', 'Neue Versandkosten-Regel erstellen');
$GLOBALS['TL_LANG']['tl_shipping_rates']['edit']   = array('Versandkosten bearbeiten', 'Versandkosten-Regel ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_shipping_rates']['copy']   = array('Versandkosten duplizieren', 'Versandkosten-Regel ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_shipping_rates']['delete'] = array('Versandkosten löschen', 'Versandkosten-Regel ID %s löschen');
$GLOBALS['TL_LANG']['tl_shipping_rates']['show']   = array('Versandkosten-Details', 'Details der Versandkosten-Regel ID %s anzeigen');

