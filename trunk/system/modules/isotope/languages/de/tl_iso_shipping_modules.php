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
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name']				= array('Name der Versandart', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price']				= array('Preis', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation']	= array('Berechnung Pauschalpreis', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries']			= array('Länder', 'Wählen Sie die Länder für welche diese Versandoption zur Verfügung steht.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total']		= array('Minimaler Bestellwert', 'Wenn dieser Wert unterschritten wird, steht diese Versandoption nicht zur Verfügung.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total']		= array('Maximaler Bestellwert', 'Wenn dieser Wert überschritten wird, steht diese Versandoption nicht zur Verfügung.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled']			= array('Aktiv', 'Wählen Sie ob dieses Modul für den Shop verfügbar ist.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new']    			= array('Neue Versandart', 'Eine neue Versandart erstellen');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit']   			= array('Versandart bearbeiten', 'Versandart ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy']   			= array('Versandart duplizieren', 'Versandart ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'] 			= array('Versandart löschen', 'Versandart ID %s löschen');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show']   			= array('Versandart-Details', 'Details der Versandart ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates']	= array('Regeln bearbeiten', 'Versandraten bearbeiten');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat']				= 'Pauschal';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct']		= 'Pro Produkt';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem']			= 'Pro Stück';

