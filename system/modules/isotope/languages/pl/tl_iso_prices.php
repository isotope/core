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
$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers']		= array('Poziomy cen', 'Ustaw przynajmniej jeden poziom ceny dla "Ilość 1". Możesz wprowadzić obniżki cen, jesli użytkownik zamówi więcej niż jeden produkt.');
$GLOBALS['TL_LANG']['tl_iso_prices']['tax_class']		= array('Klasa podatku', 'Proszę wybrać klasę podatku dla tej ceny.');
$GLOBALS['TL_LANG']['tl_iso_prices']['config_id']		= array('Konfiguracja sklepu', 'Proszę wybrać konfigurację sklepu dla tej ceny.');
$GLOBALS['TL_LANG']['tl_iso_prices']['member_group']	= array('Grupa użytkowników', 'Proszę wybrać grupę użytkowników dla tej ceny.');
$GLOBALS['TL_LANG']['tl_iso_prices']['start']			= array('Używaj od', 'Nie używaj tej ceny przed tym dniem.');
$GLOBALS['TL_LANG']['tl_iso_prices']['stop']			= array('Używaj do', 'Nie używaj ten ceny po tym dniu.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_prices']['new']				= array('Dodaj cenę', 'Dodaj nową cenę dla tego produktu');
$GLOBALS['TL_LANG']['tl_iso_prices']['edit']			= array('Edytuj cenę', 'Edytyj cenę ID %s');
$GLOBALS['TL_LANG']['tl_iso_prices']['copy']			= array('Duplikuj cenę', 'Duplikuj cenę ID %s');
$GLOBALS['TL_LANG']['tl_iso_prices']['delete']			= array('Usuń cenę', 'Usuń cenę ID %s');
$GLOBALS['TL_LANG']['tl_iso_prices']['show']			= array('Szczegóły ceny', 'Pokaż szczegóły ceny ID %s');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['min']	= 'Ilość (min)';
$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['price']	= 'Cena';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_prices']['price_legend']	= 'Cena';
$GLOBALS['TL_LANG']['tl_iso_prices']['limit_legend']	= 'Ograniczenia';

