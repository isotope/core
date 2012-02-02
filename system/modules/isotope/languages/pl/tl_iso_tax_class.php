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
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name']						= array('Nazwa', 'Wprowadź nazwę podatku, która wyjaśnia do czego jest użyty.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback']					= array('Domyślna', 'Zaznacz, jeśli to domyślna stawka podatku.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement']	= array('Zastosuj przyrost zaokrąglenia', 'Zaznacz, jeśli chcesz zastosować przyrost zaokrąglenia z konfiguracji sklepu.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes']					= array('Stawka podatkowa zawarta w cenie produktu', 'Zaznacz, jeśli ceny produktu z tą klasą podatkową już zawierają w sobie stawkę podatkową. Ta stawka podatkowa będzie odjęta od ceny produktu, jesli nie pasuje.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['label']					= array('Dołącz etykietę', 'Etykieta dla zamówień, która prezentuje odjęte podatki (jeśli podatek nie pasuje). Jeśli pole jest puste, użyta zostnaie etykieta domyślnej stawki podatku.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates']					= array('Zastosuj stawki podatkowe', 'Dodaj te stawik podatkowe do produktów z tą klasą podatkową.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['new']    = array('Nowa klasa podatku', 'Dodaj nową klasę podatku');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit']   = array('Edytuj klasę podatku', 'Edytuj klasę podatku ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy']   = array('Kopiuj klasę podatku', 'Kopiuj klasę podatku ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'] = array('Usuń klasę podatku', 'Usuń klasę podatku ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['show']   = array('Szczegóły zamówienia', 'Pokaż szczegóły klasy podatku ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name_legend']	= 'Nazwa';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rate_legend']	= 'Stawki podatkowe';

