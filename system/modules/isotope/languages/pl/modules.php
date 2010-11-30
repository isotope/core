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
 * @author     Radosław Maślanek <radek@dziupla.pl>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['iso_products']				= array('Zarządzanie produktami','');
$GLOBALS['TL_LANG']['MOD']['iso_orders']				= array('Zamówienia', '');
$GLOBALS['TL_LANG']['MOD']['iso_statistics']			= array('Statyatyki', '');
$GLOBALS['TL_LANG']['MOD']['iso_setup']					= array('Konfiguracje sklepów','');

/**														
 * Frontend modules									
 */
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter']			= array('Filtry produktów', 'Define individual filters for Isotope such as category trees and product attribute filters.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist']			= array('Lista produktów', 'Główny moduł listy produktów.  Can be used to list products or values of attributes. May be combined with other modules (i.e. the Filter Module) to provide further drill-down capabilities.');
$GLOBALS['TL_LANG']['FMD']['iso_productreader']			= array('Przeglądarka produktów', 'Ten moduł wyświetla pełny opis produktu');
$GLOBALS['TL_LANG']['FMD']['iso_cart']					= array('Koszyk', 'A fully-featured shopping cart module.  Box or Full Display can be set by template selection.');
$GLOBALS['TL_LANG']['FMD']['iso_checkout']				= array('Kasa', 'Allow store customers to complete their transactions.');
$GLOBALS['TL_LANG']['FMD']['iso_addressbook']			= array('Książka adresowa','Allow customers to manage their address book.');
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory']			= array('Hostoria zamówień', 'Order lister that allows customers to view their order history');
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails']			= array('Szczegóły zamówienia', 'Order reader that allows customers to view order history details');
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher']		= array('Zmiana konfiguracji sklepu', 'Switch between store configuration to change currency and other settings.');
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts']		= array('Powiązane produkty', 'List products related to the current one.');


/**
 * Isotope Modules
 */
$GLOBALS['TL_LANG']['ISO']['config_module']				= 'Isotope eCommerce configuration';
$GLOBALS['TL_LANG']['IMD']['checkout']					= 'Funkcje finansowe';
$GLOBALS['TL_LANG']['IMD']['product']					= 'Zarządzanie produktami';
$GLOBALS['TL_LANG']['IMD']['config']					= 'Ustawienia ogólne';
$GLOBALS['TL_LANG']['IMD']['shipping']					= array('Metody dostawy','Utwórz opcje dostawy widoczne dla klientów, PocztaPolska, UPS, DHL itd..');
$GLOBALS['TL_LANG']['IMD']['payment']					= array('Metody płatności','Zdefiniuj metody płatności dostepne przy składaniu zamówienia Authorize.net, PayPal Pro, i inne.');
$GLOBALS['TL_LANG']['IMD']['tax_class']					= array('Klasy podatku','Definiuj i zarządzaj klasami podatku na postawie stawek podatkowych.');
$GLOBALS['TL_LANG']['IMD']['tax_rate']					= array('Stawki podatku','Definiuj stawki podatku dla produktów. Moga one zależeć np. od miejsca/kraju w którym prowadzi działalność lub zamieszkuje odbiorca, miejsca dostawy towaru lub całości zamówienia.');
$GLOBALS['TL_LANG']['IMD']['attributes']				= array('Atrybuty', 'Twórz i zarządzaj atrybutami porduktów takimi jak rozmiar, kolor, kształt itd... Ułatwi Ci to późniejsze generowanie wariantów tego samego towaru.');
$GLOBALS['TL_LANG']['IMD']['producttypes']				= array('Typy produktów', 'Dodawaj i zarządzaj typami towaru w sklepie na podstawie zdefiniowanych atrybutów.');
$GLOBALS['TL_LANG']['IMD']['related_categories']		= array('Powiązane kategorie', 'Swórz nowe kategorie i powiaż ze sobą produkty lub grupy produktów.');
$GLOBALS['TL_LANG']['IMD']['iso_mail']					= array('Zarządzanie E-mail','Ustaw i dostosuj szablony powiadomień e mail do administracji sklepu i klientów.');
$GLOBALS['TL_LANG']['IMD']['configs']					= array('Konfiguracje sklepów', 'Skonfiguruj najważniejsze ustawienia dla swoich sklepów.');

