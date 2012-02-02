<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Kamil Kuźmiński <kamil.kuzminski@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'] 				= array('Nazwa', 'Krótki opis stawki, który będzie wyśwetliony na stronie.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'] 				= array('Stawka', 'Stawka wysyłki w formacie waluty.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description']		= array('Opis', 'Opis stawki, który może być użyty do przekazania klientowi jak jest liczona stawka.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total']		= array('Minimalna wartość');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total']		= array('Maksymalna wartość');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_from']		= array('Waga od', 'Jeśli całkowita waga wszystkich produktów w koszyku jest większa niż ta, stawka będzie pasować.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_to']			= array('Waga do', 'Jeśli całkowita waga wszystkich produktów w koszyku jest mniejsza ksza niż ta, stawka będzie pasować.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['enabled']			= array('Atkywna', 'Zaznacz, jeśli stawka ma być dostępna na stronie.');


/**
 * Reference
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['general_legend']	= 'Informacje ogólne';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['config_legend']		= 'Konfiguracja';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['new']		= array('Nowa stawka wysyłki', 'Dodaj nową stawkę wysyłki');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit']		= array('Edytuj stawkę wysyłki', 'Edytuj stawkę wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy']		= array('Kopiuj stawkę wysyłki', 'Kopiuj stawkę wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete']	= array('Usuń stawkę wysyłki', 'Usuń stawkę wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show']		= array('Szczegóły stawki wysyłki', 'Pokaż szczegóły stawki wysyłki ID %s');

