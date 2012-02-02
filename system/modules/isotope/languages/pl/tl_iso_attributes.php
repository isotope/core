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
$GLOBALS['TL_LANG']['tl_iso_attributes']['name']					= array('Nazwa', 'Proszę wprowadzić nazwę dla tego atrybutu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name']				= array('Wewnętrzna nazwa', 'Wewnętrzna nazwa jest nazwą pola w bazie danych i musi być unikalna.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['type']					= array('Typ', 'Proszę wybrać typ dla tego atrybutu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend']					= array('Grupa', 'Wybierz grupę, do której ten atrybut się odnosi (używane to porządkowania powiązanych pól podczas edycji produktu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option']			= array('Dodaj atrybut do wizarda wariantów', 'Dodaje atrybut do wizarda wariantów produktu jako opcję wariantu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined']		= array('Określone przez klienta', 'Zaznacz, jeśli wartość atrybutu jest określana przez klienta (frontend).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['description']				= array('Opis', 'Opis jest pokazany jako podpowiedź dla użytkownika panelu administratora.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['options']					= array('Opcje', 'Proszę wprowadzić jedną lub więcej opcji. Użyj przycisków by dodać, przesunąć lub usunąć opcję.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory']				= array('Pole wymagane', 'Pole musi zostać wypełnione podczas edycji produktu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple']				= array('Wielokrotny wybór', 'Pozwól odwiedzającym na wybranie jednej lub więcej opcji.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['size']					= array('Rozmiar listy', 'Tutaj możesz wprowadzić rozmiar zaznaczonego boxu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions']				= array('Dozwolone typy plików', 'Lista oddzielonych przecinkami dozwolonych rozszerzeń plików.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte']						= array('Używaj edytora HTML', 'Wybierz konfigurację tinyMCE by włączyć edytor tekstu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual']			= array('Wielojęzyczny', 'Zaznacz, jeśli pole powinno być przetłumaczone.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp']					= array('Sprawdzenie wartości', 'Sprawdź wprowadzoną wartość przy pomocy wyrażenia regularnego.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength']				= array('Maksymalna długość', 'Ogranicz długość pola do wybranej liczby znaków (tekst) lub bitów (upload plików).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey']				= array('Zewnętrzna tabela i pole', 'Tutaj możesz wprowadzić nazwę tabeli i pola, z której zostaną pobrane opcje (np. tl_tabela.pole). By używać wielojęzycznych kluczy, wprowadź jeden na linię i określ język (np. pl=tl_tabela.pole)');
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField']			= array('Pole rodzica', 'Proszę wybrać pole rodzica, które musi być typu "Select-Menu". Dla relacji rodzic-dziecko, zdefiniuj każdą opcję dla tego pola jako grupa dla conditional select-menu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery']					= array('Galeria obrazków', 'Różne galerie obrazków mogą prezentować pliki multimedialne w dowolnym stylu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['files']					= array('Pokaż pliki', 'Pokazuj pliki i foldery.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['filesOnly']				= array('Tylko pliki', 'Usuń przyciski radio i checkbox obok folderów.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['fieldType']				= array('Typ pola', 'Wyświetl przycisk radio lub checkbox obok folderów.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['sortBy']					= array('Sortowanie', 'Proszę wybrać opcję sortowania.');

$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting']				= array('Dodaj do listy "Sortuj"', 'To pole będzie możliwe do sortowania w module listingu, o ile atrybut jest widoczny dla klientów.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter']				= array('Filtrowanie backend', 'Czy ten atrybut może być filtrowany a backendzie?');
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search']				= array('Wyszukiwanie backend', 'Czy wyszukiwarka powinna uwzględniać to pole przy wyszukiawniu?');
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter']				= array('Filtrowanie frontend', 'Czy ten atrybut może zostać użyty w filtrze frontend?');
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search']				= array('Wyszukiwanie frontend', 'Czy wyszukiwarka powinna uwzględniać to pole przy wyszukiawniu?');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['opValue']					= 'Wartość';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opLabel']					= 'Etykieta';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opDefault']				= 'Domyślny';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opGroup']					= 'Grupa';
$GLOBALS['TL_LANG']['tl_iso_attributes']['checkbox']				= 'Checkbox';
$GLOBALS['TL_LANG']['tl_iso_attributes']['radio']					= 'Radio';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit']					= array('Znaki numeryczne', 'Dozwolone cyfry, minus (-), kropka (.) i spacja ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha']					= array('Znaki alfabetyczne', 'Dozwolone znaki alfabetu, minus (-), kropka (.) i spacja ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum']					= array('Znaki alfanumeryczne', 'Dozwolone znaki alfabetu i cyfry, minus (-), kropka (.), podkreślenie (_) i spacja ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['extnd']					= array('Rozszerzone znaki alfabetyczne', 'Dozwolone wszystko oprócz znaków specjalnych (#/()<=>).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['date']					= array('Data', 'Sprawdza, czy wprowadzona wartość zgadza się z globalnym formatem daty.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['time']					= array('Czas', 'Sprawdza, czy wprowadzona wartość zgadza się z globalnym formatem czasu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim']					= array('Data i czas', 'Sprawdza, czy wprowadzona wartość zgadza się z globalnym formatem daty i czasu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone']					= array('Numer telefonu', 'Dozwolone cyfry, plus (+), minus (-), slash (/), nawiasy () i spacja ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['email']					= array('Adres e-mail', 'Sprawdza, czy wprowadzona wartość jest prawdiłowym adresem e-mail.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['url']						= array('Adres URL', 'Sprawdza, czy wprowadzona wartość jest prawdiłowym adresem URL.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['price']					= array('Cena', 'Sprawdza, czy wprowadzona wartość jest prawidłową ceną');
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount']				= array('Zniżka', 'Sprawdza, czy wprowadzona wartość jest prawidłową zniżką.<br />Przykład: -10%, -10, +10, +10%');
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge']				= array('Dopłata', 'Sprawdza, czy wprowadzona wartość jest prawidłową dopłatą.<br />Przykład: 10.00, 10%');
$GLOBALS['TL_LANG']['tl_iso_attributes']['name_asc']				= 'Nazwa pliku (rosnąco)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['name_desc']				= 'Nazwa pliku (malejąco)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date_asc']				= 'Data (rosnąco)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date_desc']				= 'Data (malejąco)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['meta']					= 'Plik meta (meta.txt)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['random']					= 'Losowa kolejność';



/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['new']						= array('Nowy atrybut', 'Dodaj nowy atrybut.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit']					= array('Edycja atrybutu', 'Edytuj atrybut ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy']					= array('Kopiuj atrybut', 'Kopiuj atrybut ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete']					= array('Usuń atrybut', 'Usuń atrybut ID %s. Pole w bazie danych nie zostanie usunięte, w tym celu użyj narzędzia instalacyjnego Contao lub Menadżera rozszerzeń.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['show']					= array('Pokaż szczegóły atrybutu', 'Pokaż szczegóły atrybutu ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['deleteConfirm']			= 'Czy na pewno chcesz usunąć atrybut ID %s? Pole w bazie danych nie zostanie usunięte, w tym celu użyj narzędzia instalacyjnego Contao lub Menadżera rozszerzeń.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['attribute_legend']		= 'Nazwa i typ atrybutu';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description_legend']		= 'Opis';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options_legend']			= 'Opcje';
$GLOBALS['TL_LANG']['tl_iso_attributes']['config_legend']			= 'Konfiguracja atrybutu';
$GLOBALS['TL_LANG']['tl_iso_attributes']['validation_legend']		= 'Walidacja inputu';
$GLOBALS['TL_LANG']['tl_iso_attributes']['search_filters_legend']	= 'Ustawienia wyszukiwania i filtrowania';

