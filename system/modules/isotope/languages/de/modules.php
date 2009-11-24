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
 

/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['store']						= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['orders']					= array('Bestellungen', '');
$GLOBALS['TL_LANG']['MOD']['shipping']					= array('Versandarten','');
$GLOBALS['TL_LANG']['MOD']['payment']					= array('Zahlungsarten','');
$GLOBALS['TL_LANG']['MOD']['tax_class']					= array('Steuerklassen', '');
$GLOBALS['TL_LANG']['MOD']['tax_rate']					= array('Steuersätze', '');
$GLOBALS['TL_LANG']['MOD']['attribute_manager']			= array('Attribute','');
$GLOBALS['TL_LANG']['MOD']['product_type_manager']		= array('Produkttypen','');
$GLOBALS['TL_LANG']['MOD']['product_manager']			= array('Produkte','');
$GLOBALS['TL_LANG']['MOD']['store_configuration']		= array('Konfigurationen','');
$GLOBALS['TL_LANG']['MOD']['iso_mail']					= array('E-Mail Vorlagen','');


/**
 * Front end modules
 */ 
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['isoFilterModule']			= array('Isotope Filter-Modul', 'Erstellt individuelle Filter für Isotope wie einen Kategorienbaum oder Produktattribut-Filter.');
$GLOBALS['TL_LANG']['FMD']['isoProductLister']			= array('Isotope Produktliste', 'Allgemeines Listen-Modul. Zeigt Produkte oder Werte von Attributen an. Kann mit anderen Modulen (z.B. Filter-Modul) kombiniert werden um die Funktionen zu erweitern.');
$GLOBALS['TL_LANG']['FMD']['isoProductReader']			= array('Isotope Produktleser', 'Mit diesem Modul zeigen Sie Produktdetails an.');
$GLOBALS['TL_LANG']['FMD']['isoShoppingCart']			= array('Isotope Warenkorb', 'Ein vollwertiges Warenkorb-Modul. Klein- oder Grossanzeige kann durch Wahl des Templates eingestellt werden.');
$GLOBALS['TL_LANG']['FMD']['isoCheckout']				= array('Isotope Kasse', 'Erlaubt Kunden eine Bestellung auszuführen.');
$GLOBALS['TL_LANG']['FMD']['isoAddressBook']			= array('Isotope Addressbuch','Erlaubt Kunden deren Adressbuch zu bearbeiten.');
$GLOBALS['TL_LANG']['FMD']['isoOrderHistory']			= array('Vergangene Bestellungen', 'Zeigt eine Liste der bisherigen Bestellungen an.');
$GLOBALS['TL_LANG']['FMD']['isoOrderDetails']			= array('Bestellungsdetails', 'Kunden können mit diesem Modul ihre vergangenen Bestellungen sehen und z.B. Download-Artikel herunterladen.');

