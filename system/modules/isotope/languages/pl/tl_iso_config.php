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
$GLOBALS['TL_LANG']['tl_iso_config']['name']							= array('Nazwa konfiguracji', '');
$GLOBALS['TL_LANG']['tl_iso_config']['label']							= array('Etykieta', 'Etykieta jest używana we frontendzie, np. przy przełączniku konfiguracji.');
$GLOBALS['TL_LANG']['tl_iso_config']['fallback']						= array('Ustaw jako domyślny sklep', 'Ustaw ten sklep jako domyślny dla formatowania waluty w backendzie i innych lokalnych ustawień.');
$GLOBALS['TL_LANG']['tl_iso_config']['store_id']						= array('ID sklepu', 'Używaj różnych ID sklepów do grupowania zestawów konfiguracji. Koszyk użytkownika i adres będzie współdzielony w obrębie tego samego ID sklepu.');
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder']		= array('Obrazek zastępczy', 'Ten obrazek będzie użyty jeśli produkt nie posiada swojego obrazka.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor']			= array('Mnożnik ceny', 'Domyślnie powinien być 1. Możesz użyć tego do konwersji pomiędzy kilkoma walutami.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode']				= array('Tryb kalkulacji', 'Podziel lub pomnóż używając mnożnika.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision']				= array('Precyzja zaokrąglenia', 'Jaka powinna być precyzja podczas zaokrąglania. Powinieneś ustawić ją pomiędzy 0 i 2 by wszystkie metody płatności działały prawidłowo. Zobacz manual PHP i funkcję round().');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement']				= array('Zwiększenie zaokrąglenia', 'Niektóre waluty (np. frank szwajcarski) nie wspierają precyzji 0.01.');
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal']					= array('Minimalna wartość koszyka', 'Minimalna wartość koszyka do złożenia zamówienia. Wprowadź 0 by wyłączyć.');
$GLOBALS['TL_LANG']['tl_iso_config']['currency']						= array('Waluta', 'Proszę wybrać walutę dla tego sklepu.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol']					= array('Używaj symbolu waluty', 'Użyj symbolu waluty ($, €, zł) jeśli dostępny.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace']					= array('Dodaj przerwę', 'Dodaj przerwę pomiędzy ceną a walutą.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition']				= array('Pozycja kodu/symbolu waluty', 'Wybierz, czy chcesz wyświetlać walutę po lewej lub po prawej stronie ceny.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat']					= array('Formatowanie waluty', 'Wybierz sposób formatowania cen.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator']				= array('Automatyczna konwersja waluty', 'Włącz automatyczne aktualizacje mnożników ceny używając źródła online.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin']					= array('Waluta źródłowa', 'Proszę wybrać walutę źródłową do kalkulacji.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider']				= array('Źródła danych walutowych', 'Wybierz źródło danych walutowych, które będą użyte do kalkulacji.');
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo']						= array('Logo na fakturze', 'Wybierz logo, które zostanie umieszone na fakturach tego sklepu.');
$GLOBALS['TL_LANG']['tl_iso_config']['firstname']   					= array('Imię', 'Proszę wprowadzić imię.');
$GLOBALS['TL_LANG']['tl_iso_config']['lastname']    					= array('Nazwisko', 'Proszę wprowadzić naziwsko.');
$GLOBALS['TL_LANG']['tl_iso_config']['company']     					= array('Firma', 'Tutaj możesz wprowadzić nazwę firmy.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_1']      					= array('Ulica', 'Proszę wprowadzić nazwę ulicy i number lokalu.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_2']						= array('Ulica 2', 'Tutaj możesz wprowadzić dodatkową informację o ulicy');
$GLOBALS['TL_LANG']['tl_iso_config']['street_3']						= array('Ulica 3', 'Tutaj możesz wprowadzić dodatkową informację o ulicy');
$GLOBALS['TL_LANG']['tl_iso_config']['postal']      					= array('Kod pocztowy', 'Proszę wprowadzić kod pocztowy.');
$GLOBALS['TL_LANG']['tl_iso_config']['city']       						= array('Miasto', 'Proszę wprowadzić nazwę miasta.');
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision']       				= array('Stan', 'Proszę wprowadzić nazwę stanu.');
$GLOBALS['TL_LANG']['tl_iso_config']['country']     					= array('Kraj', 'Proszę wybrać kraj. Będzie on użyty jako domyślny dla adresów dostawy/na fakturach.');
$GLOBALS['TL_LANG']['tl_iso_config']['phone']       					= array('Numer telefonu', 'Proszę wprowadzić numer telefonu.');
$GLOBALS['TL_LANG']['tl_iso_config']['email'] 							= array('Adres e-mail dostawy', 'Proszę wprowadzić adres e-mail.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries']				= array('Kraje dostawy', 'Wybierz kraje, które mogą być wybrane do dostawy przy składaniu zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields']					= array('Pola adresowe dostawy', 'Wybierz pola dla nowego adresu dostawy przy składaniu zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries']				= array('Kraje do faktur', 'Wybierz kraje, które mogą być wybrane do adresu na fakturze przy składaniu zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields']					= array('Pola adresowe do faktur', 'Wybierz pola dla nowego adresu na fakturze przy składaniu zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix']						= array('Prefiks numeru zamówienia', 'Możesz dodać prefiks (np. rok) do samozwiększającego się numeru zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits']						= array('Długość numeru zamówienia', 'Wybierz minimalną długość numeru zamówienia (wyłączając prefiks).');
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup']					= array('Folder szablonów', 'Tutaj możesz wybrać folder szablonów do przeszukania, zanim zostaną przeszukane pozostałe foldery.');
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries']			= array('Limit krajów użytkowników', 'Ogranicz kraje użytkowników (Rejestracja, Dane osobiste) do połączonej listy krajów dostawy/na fakturach.');
$GLOBALS['TL_LANG']['tl_iso_config']['gallery']							= array('Galeria obrazków produktu', 'Różne galerie obrazków mogą prezentować pliki multimedialne w dowolnym stylu.');
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes']						= array('Rozmiary obrazka', 'Możesz wprowadzić własne rozmiary obrazków do użycia w szablonach. Domyślne rozmiary to "gallery" (galeria), "thumbnail" (miniaturka), "medium" (średni) i "large" (wielki).');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_config']['left']							= 'Po lewej stronie ceny';
$GLOBALS['TL_LANG']['tl_iso_config']['right']							= 'Po prawej stronie ceny';
$GLOBALS['TL_LANG']['tl_iso_config']['div']								= 'Dzielenie';
$GLOBALS['TL_LANG']['tl_iso_config']['mul']								= 'Mnożenie';

$GLOBALS['TL_LANG']['tl_iso_config']['tl']								= 'Góra Lewo';
$GLOBALS['TL_LANG']['tl_iso_config']['tc']								= 'Góra';
$GLOBALS['TL_LANG']['tl_iso_config']['tr']								= 'Góra Prawo';
$GLOBALS['TL_LANG']['tl_iso_config']['bl']								= 'Dół Lewo';
$GLOBALS['TL_LANG']['tl_iso_config']['bc']								= 'Dół';
$GLOBALS['TL_LANG']['tl_iso_config']['br']								= 'Dół Prawo';
$GLOBALS['TL_LANG']['tl_iso_config']['cc']								= 'Środek';

$GLOBALS['TL_LANG']['tl_iso_config']['iwName']							= 'Nazwa';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWidth']							= 'Długość';
$GLOBALS['TL_LANG']['tl_iso_config']['iwHeight']						= 'Wysokość';
$GLOBALS['TL_LANG']['tl_iso_config']['iwMode']							= 'Tryb';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark']						= 'Znak wodny';
$GLOBALS['TL_LANG']['tl_iso_config']['iwPosition']						= 'Pozycja';

$GLOBALS['TL_LANG']['tl_iso_config']['fwEnabled']						= 'Włącz pole';
$GLOBALS['TL_LANG']['tl_iso_config']['fwLabel']							= 'Etykieta';
$GLOBALS['TL_LANG']['tl_iso_config']['fwMandatory']						= 'Wymagane';

$GLOBALS['TL_LANG']['tl_iso_config']['ecb.int']							= 'European Central Bank';
$GLOBALS['TL_LANG']['tl_iso_config']['admin.ch']						= 'Swiss Federal Department of Finance';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_config']['new']    							= array('Nowa konfiguracja', 'Utwórz nową konfigurację sklepu.');
$GLOBALS['TL_LANG']['tl_iso_config']['edit']   							= array('Edytuj konfigurację', 'Edytuj konfigurację sklepu ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['copy']   							= array('Kopiuj konfigurację', 'Kopiuj konfigurację sklepu ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['delete'] 							= array('Usuń konfigurację', 'Usuń konfigurację sklepu ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['show']   							= array('Pokaż szczegóły konfiguracji', 'Pokaż szczegóły konfiguracji sklepu ID %s.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name_legend']						= 'Nazwa';
$GLOBALS['TL_LANG']['tl_iso_config']['address_legend']	    			= 'Adres i konfiguracja';
$GLOBALS['TL_LANG']['tl_iso_config']['config_legend']					= 'Konfiguracja';
$GLOBALS['TL_LANG']['tl_iso_config']['price_legend']	    			= 'Ceny';
$GLOBALS['TL_LANG']['tl_iso_config']['currency_legend']	    			= 'Formatowanie waluty';
$GLOBALS['TL_LANG']['tl_iso_config']['converter_legend']	    		= 'Konwertowanie waluty';
$GLOBALS['TL_LANG']['tl_iso_config']['invoice_legend']	    			= 'Faktury';
$GLOBALS['TL_LANG']['tl_iso_config']['images_legend']	    			= 'Obrazki';

