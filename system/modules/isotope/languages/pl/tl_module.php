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
$GLOBALS['TL_LANG']['tl_module']['iso_list_layout']				= array('Szablon listy produktów', 'Proszę wybrać szablon listy produktów.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout']			= array('Szablon czytnika produktów', 'Proszę wybrać szablon czytnika produktów.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo']			= array('Strona czytnika produktów', 'To pole określa stronę, na którą zostanie przekierowany użytkownik kilkając link produktu (<strong>Uwaga:</strong> To pole nadpisuje ustawienie ze struktury stron!).');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout']				= array('Szablon koszyka produktów', 'Proszę wybrać szablon koszyka produktów.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl']				= array('Szablon filtru', 'Proszę wybrać szablon filtru produktów.');
$GLOBALS['TL_LANG']['tl_module']['iso_cols']					= array('Kolumny', 'Wprowadź liczbę kolumn do wyświetlenia w szablonie listy.');
$GLOBALS['TL_LANG']['tl_module']['iso_config_id']				= array('Konfiguracja sklepu', 'Wybierz konfigurację sklepu, która będzie używana przez ten moduł.');
$GLOBALS['TL_LANG']['tl_module']['iso_config_ids']				= array('Konfiguracje sklepu', 'Wybierz konfiguracje sklepu, które będą używane przez ten moduł.');
$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo']			= array('Strona logowania do kasy', 'Wybierz stronę, na której użytkownik powinien się zalogować by przejść do kasy.');
$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules']			= array('Metody płatności', 'Wybierz jedną lub więcej metod płatności dla tego modułu kasy.');
$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules']		= array('Metody dostawy', 'Wybierz jedną lub więcej metod dostawy dla tego modułu kasy.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method']			= array('Metody kasy', 'Proszę wybrać metodę kasy.');
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']		= array('Formularz warunków zakupu', 'Wybierz formularz, który zostanie użyty do wyświetlenia twojego regulaminu i warunków zakupu (opcjonalne).');
$GLOBALS['TL_LANG']['tl_module']['iso_addToAddressbook']		= array('Dodaj do książki adresowej', 'Dodaj nowe adresy do książki adresowej zalogowanego użytkownika.');
$GLOBALS['TL_LANG']['tl_module']['iso_noProducts']				= array('Wiadomość dla pustych modułów', 'Wprowadź wiadomość jeśli nie ma nic do wyświetlenia (pusta lista produktów, pusty koszyk, itp.).');
$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage']			= array('Określ wiadomość dla pustych modułów', 'Ustaw wiadomość jeśli nie ma nic do wyświetlenia (pusta lista produktów, pusty koszyk, itp.).');
$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo']			= array('Strona po zakończonym zamówieniu', 'Wybierz stronę, na którą zostanie przkierowany klient po zakończonym zamówieniu.');
$GLOBALS['TL_LANG']['tl_module']['iso_jump_first']				= array('Przekieruj do pierwszego produktu', 'Zaznacz tutaj jeśli użytkownicy powinni być przekierowani do pierwszego produktu z listy.');
$GLOBALS['TL_LANG']['tl_module']['iso_hide_list']				= array('Ukryj w czytniku', 'Ukryj listę produktów jeśli alias produktu jest w adresie URL.');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer']			= array('Powiadomienie klienta e-mailem', 'Wybierz Isotope Email, który będzie użyty do wysłania klientom, gdy złożą zamówienie.');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin']				= array('Powiadomienie kierownika sprzedaży e-mailem', 'Wybierz Isotope Email, który będzie użyty do wysłania kierownikowi sprzedaży, gdy kilenci złożą zamówienie.');
$GLOBALS['TL_LANG']['tl_module']['iso_sales_email']				= array('Adres e-mail kierownika sprzedazī', 'Wprowadź inny niż systemowy adres e-mail, na który będą wysyłane powiadomienia.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope']			= array('Zakres kategorii', 'Określ zakres listy prodkutów.');
$GLOBALS['TL_LANG']['tl_module']['iso_list_where']				= array('Warunek', 'Tutaj możesz wprowadzić warunek SQL do filtrowania produktów. Dla wszystkich pól musisz użyć prefiksu "p1." (np. <em>p1.featured=1</em> or <em>p1.color!=\'red\'</em>)!');
$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'] 			= array('Dowolna ilość', 'Pozwala użytkownikowi na określenie ilości produktu, który chce kupić.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterModules']			= array('Moduły filtrów', 'Wybierz moduły filtrów, które chcesz uwzględnić dla tej listy produktów.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterFields']			= array('Włączone filtry', 'Wybierz filtry do włączenia.');
$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields']			= array('Włączone pola sortowania', 'Proszę wybrać pola sortowania do włączenia.');
$GLOBALS['TL_LANG']['tl_module']['iso_searchFields']			= array('Włączone pola wyszukiwania', 'Proszę wybrać pola wyszukiwania do włączenia.');
$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit']				= array('Włącz limit rekordów na stronę', 'Pozwala użytkownikowi wybrać, ile rekordów zostanie pokazanych na jednej stronie.');
$GLOBALS['TL_LANG']['tl_module']['iso_perPage']					= array('Opcje rekordów na stronę', 'Wprowadź listę wartości oddzieloną przecinkami, które będą użyte w menu dropdown. Pierwsza opcja zostanie użyta jako domyślna wartość. Wartości zostaną automatycznie posortowane wg. liczby.');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo']				= array('Strona koszyka produktów', 'Wybierz stronę, na którą zostanie przekierowany użytkownik, gdy zażąda pełnego widoku koszyka.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo']			= array('Strona kasy', 'Wybierz stronę, na którą zostanie przekierowany użytkownik, gdy zakończy transakcję.');
$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo']		= array('Strona dodania produktu', 'Wybierz stronę, na którą zostanie przekierowany użytkownik, gdy doda nowy produkt do koszyka.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'] 		= array('Domyślne pole sortujące', 'Wybierz pole sortujące, które będzie użyte po pierwszym załadowaniu strony.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'] 	= array('Domyślny kierunek sortujący', 'Wybierz domyślne kierunek sortujący.');
$GLOBALS['TL_LANG']['tl_module']['iso_buttons']					= array('Przyciski', 'Wybierz przyciski, które chcesz wyświetlić.');
$GLOBALS['TL_LANG']['tl_module']['iso_forward_review']			= array('Przejdź do strony przeglądu', 'Przekieruj użytkownika do strony przeglądu, jeśli żadne informacje nie są wymagane w żadnym z kroków.');
$GLOBALS['TL_LANG']['tl_module']['iso_related_categories']		= array('Powiązane kategorie', 'Wybierz kategorie z których będą pokazane produkty.');
$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages']			= array('Dołącz wiadomości', 'To ustawienie pozwala modułowi na dołączenie wszelakich błędów, powiadomień czy potwierdzeń, z którymi odwiedzający powinien się zapoznać.');
$GLOBALS['TL_LANG']['tl_module']['iso_continueShopping']		= array('Włącz przycisk "Kontynuuj zakupy"', 'Dodaj link do właśnie dodanego produktu do koszyka.');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['member']	= 'Wymagane logowanie/rejestracja';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['guest']	= 'Kasa tylko dla gości';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['both']		= 'Oba dozwolone';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['global']					= 'Wszystkie kategorie';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_and_first_child']	= 'Aktywna kategoria i pierwsza podkategoria';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_and_all_children']	= 'Aktywna kategoria i wszystkie podkategorie';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_category']			= 'Aktywna kategoria';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['parent']					= 'Nadrzędna kategoria';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['product']					= 'Kategorie aktywnego produktu';
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['ASC'] 	= "ASC";
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['DESC'] 	= "DESC";

