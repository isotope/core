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
$GLOBALS['TL_LANG']['tl_iso_addresses']['store_id']				= array('ID sklepu', 'Używaj różnych ID sklepów do grupowania zestawów konfiguracji. Koszyk użytkownika i adres będzie współdzielony w obrębie tego samego ID sklepu.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['label']				= array('Etykieta', 'Proszę wprowadzić nazwę etykiety dla tego adresu.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['salutation']			= array('Tytuł', 'Proszę wprowadzić tytuł (Pan, Pani, Dr, Prof.).');
$GLOBALS['TL_LANG']['tl_iso_addresses']['firstname']			= array('Imię', 'Proszę wprowadzić imię.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['lastname']				= array('Nazwisko', 'Proszę wprowadzić nazwisko.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['company']				= array('Nazwa firmy', 'Tutaj możesz wprowadzić nazwę firmy.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_1']				= array('Ulica', 'Proszę wprowadzić nazwę ulicy i numer lokalu.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_2']				= array('Ulica 2', 'Tutaj możesz wprowadzić dodatkową informację o ulicy');
$GLOBALS['TL_LANG']['tl_iso_addresses']['street_3']				= array('Ulica 3', 'Tutaj możesz wprowadzić dodatkową informację o ulicy.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['postal']				= array('Kod pocztowy', 'Proszę wprowadzić kod pocztowy.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['city']					= array('Miasto', 'Proszę wprowadzić nazwę miasta.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['subdivision']			= array('Stan', 'Proszę wprowadzić nazwę stanu.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['country']				= array('Kraj', 'Proszę wybrać kraj.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['phone']				= array('Numer telefonu', 'Proszę wprowadzić numer telefonu.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['email']				= array('Adres e-mail', 'Proszę wprowadzić adres e-mail.');
$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultBilling']		= array('Domyślny adres rozliczeniowy', 'Czy to twój domyślny adres rozliczeniowy?');
$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultShipping']	= array('Domyślny adres do wysyłki', 'Czy to twój domyślny adres do wysyłki?');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_addresses']['store_legend'] 		= 'Sklep';
$GLOBALS['TL_LANG']['tl_iso_addresses']['personal_legend']		= 'Dane osobiste';
$GLOBALS['TL_LANG']['tl_iso_addresses']['address_legend']		= 'Dane adresowe';
$GLOBALS['TL_LANG']['tl_iso_addresses']['contact_legend']		= 'Dane kontaktowe';
$GLOBALS['TL_LANG']['tl_iso_addresses']['default_legend']		= 'Domyślny adres';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_addresses']['personalData']			= 'Dane osobiste';
$GLOBALS['TL_LANG']['tl_iso_addresses']['addressDetails']		= 'Dane adresowe';
$GLOBALS['TL_LANG']['tl_iso_addresses']['contactDetails']		= 'Dane kontaktowe';
$GLOBALS['TL_LANG']['tl_iso_addresses']['loginDetails']			= 'Domyślny adres';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_addresses']['new']		= array('Nowy adres', 'Dodaj nowy adres');
$GLOBALS['TL_LANG']['tl_iso_addresses']['show']		= array('Szczegóły adresu', 'Pokaż szczegóły adresu ID %s');
$GLOBALS['TL_LANG']['tl_iso_addresses']['edit']		= array('Edytuj adres', 'Edytuj adres ID %s');
$GLOBALS['TL_LANG']['tl_iso_addresses']['copy']		= array('Duplikuj adres', 'Duplikuj adres ID %s');
$GLOBALS['TL_LANG']['tl_iso_addresses']['delete']	= array('Usuń adres', 'Usuń adres ID %s');

