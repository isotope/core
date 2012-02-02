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
$GLOBALS['TL_LANG']['tl_iso_mail']['name']				= array('Nazwa', 'Proszę wprowadzić nazwę dla tego e-maila. Zostanie użyta ona tylko jako odwołanie w systemie.');
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName']		= array('Nazwa nadawcy', 'Proszę wprowadzić nazwę nadaawcy.');
$GLOBALS['TL_LANG']['tl_iso_mail']['sender']			= array('E-mail nadawcy', 'Proszę wprowadzić adres e-mail nadawcy. Odbiorca będzie mógł odpowiedzieć na ten adres.');
$GLOBALS['TL_LANG']['tl_iso_mail']['cc']				= array('Wyślij kopię CC do', 'Odbiorcy, którzy powinni otrzymać kopię CC tego e-maila. Oddziel kilka adresów przecinkami.');
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc']				= array('Wyślij kopię BCC do', 'Odbiorcy, którzy powinni otrzymać kopię BCC tego e-maila. Oddziel kilka adresów przecinkami.');
$GLOBALS['TL_LANG']['tl_iso_mail']['template']			= array('Szablon e-mail', 'Tutaj możesz wybrać szablon HTML dla e-maila.');
$GLOBALS['TL_LANG']['tl_iso_mail']['priority']			= array('Priorytet', 'Proszę wybrać priorytet.');
$GLOBALS['TL_LANG']['tl_iso_mail']['attachDocument']	= array('Dołącz dokument zamówienia', 'Pozwala na wygenerowanie dodatkowego dokumentu w formacie PDF i dołączenie go do tego e-maila.');
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTemplate']	= array('Szablon dokumentu', 'Proszę wybrać szablon dokumentu. Nadpisze to domyślny szablon kolekcji.');
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTitle']		= array('Tytuł dokumentu', 'Proszę określić tytuł dołączonego dokumentu.');
$GLOBALS['TL_LANG']['tl_iso_mail']['source']			= array('Pliki źródłowe', 'Proszę wybrać jeden lub więcej plików .imt.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['new']			= array('Nowy szablon', 'Utwórz nowy szablon e-mail');
$GLOBALS['TL_LANG']['tl_iso_mail']['edit']			= array('Edytuj szablon', 'Edytuj szablon e-mail ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['copy']			= array('Kopiuj szablon', 'Kopiuj szablon e-mail ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['delete']		= array('Usuń szablon', 'Usuń szablon e-mail ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['show']			= array('Szczegóły szablonu', 'Szczegóły szablonu e-mail ID %s');
$GLOBALS['TL_LANG']['tl_iso_mail']['importMail']	= array('Import', 'Import szablonu e-mail');
$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail']	= array('Eksport', 'Eksport szablonu e-mail ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['name_legend']		= 'Nazwa';
$GLOBALS['TL_LANG']['tl_iso_mail']['address_legend']	= 'Adres';
$GLOBALS['TL_LANG']['tl_iso_mail']['document_legend']	= 'Załącznik';
$GLOBALS['TL_LANG']['tl_iso_mail']['expert_legend']		= 'Zaawansowane ustawienia';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['xml_error']			= 'Szablon "%s" jest uszkodzony i nie może być zaimportowany.';
$GLOBALS['TL_LANG']['tl_iso_mail']['mail_imported']		= 'Szablon "%s" został zaimportowany.';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['1'] = 'bardzo wysoki';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['2'] = 'wysoki';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['3'] = 'normalny';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['4'] = 'niski';
$GLOBALS['TL_LANG']['tl_iso_mail']['priority_ref']['5'] = 'bardzo niski';

