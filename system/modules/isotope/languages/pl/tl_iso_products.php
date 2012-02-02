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
$GLOBALS['TL_LANG']['tl_iso_products']['id']					= array('ID produktu');
$GLOBALS['TL_LANG']['tl_iso_products']['pages']					= array('Kategorie', 'Proszę wybrać kategorie produktu.');
$GLOBALS['TL_LANG']['tl_iso_products']['type']					= array('Typ produktu', 'Typy produktów są zdeifniowane w menedżerze typów produktu.');
$GLOBALS['TL_LANG']['tl_iso_products']['alias']					= array('Alias');
$GLOBALS['TL_LANG']['tl_iso_products']['name']					= array('Nazwa');
$GLOBALS['TL_LANG']['tl_iso_products']['sku']					= array('SKU');
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight']		= array('Waga przy wysyłce', 'Proszę wprowadzić wagę produktu przy wysyłce. Może być to użyte przy kalkulowaniu kosztów wysyłki.');
$GLOBALS['TL_LANG']['tl_iso_products']['teaser']				= array('Zajawka');
$GLOBALS['TL_LANG']['tl_iso_products']['description']			= array('Opis');
$GLOBALS['TL_LANG']['tl_iso_products']['description_meta']		= array('Opis meta');
$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta']			= array('Słowa kluczowe meta');
$GLOBALS['TL_LANG']['tl_iso_products']['price']					= array('Cena');
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt']		= array('Niedostępny do wysyłki', 'Zaznacz jeśli produkt nie jest dostępny do wysyłki (np. w przypadku plików do pobrania).');
$GLOBALS['TL_LANG']['tl_iso_products']['tax_class']				= array('Klasa podatku');
$GLOBALS['TL_LANG']['tl_iso_products']['images']				= array('Obrazki');
$GLOBALS['TL_LANG']['tl_iso_products']['published']				= array('Publikuj produkt', 'Zanzacz by pokazać produkt na stronie.');
$GLOBALS['TL_LANG']['tl_iso_products']['start']					= array('Pokazuj od', 'Nie pokazuj produktu przed tą datą.');
$GLOBALS['TL_LANG']['tl_iso_products']['stop']					= array('Pokazuj do', 'Nie pokazuj produktu po tej dacie.');
$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes']	= array('Warianty', 'Proszę wybrać kombinację wartości dla tego wariantu.');
$GLOBALS['TL_LANG']['tl_iso_products']['inherit']				= array('Odziedziczone atrybuty', 'Zaznacz pola, które chcesz odziedziczyć po produkcie bazowym.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_products']['source']	= array('Źródłowy folder', 'Proszę wybrać folder, w którym zlokalizowane są pliki produktu.');

$GLOBALS['TL_LANG']['tl_iso_products']['internal'] = array('Wewnętrzny plik', 'Wybierz istniejący plik multimedialny na serwerze (flash lub plik mp3).');
$GLOBALS['TL_LANG']['tl_iso_products']['external'] = array('Zewnętrzny plik', 'Określ wideo z zewnętrznego źródła (np. z YouTube).');

$GLOBALS['TL_LANG']['tl_iso_products']['opAttribute'] = 'Atrybut produktu';
$GLOBALS['TL_LANG']['tl_iso_products']['opValueSets'] = 'Wartości opcji';

$GLOBALS['TL_LANG']['tl_iso_products']['opValue']		= 'Wartość';
$GLOBALS['TL_LANG']['tl_iso_products']['opLabel']		= 'Etykieta';
$GLOBALS['TL_LANG']['tl_iso_products']['opPrice']		= 'Cena (dopłata)';
$GLOBALS['TL_LANG']['tl_iso_products']['opDisable']		= 'Wyłącz';
$GLOBALS['TL_LANG']['tl_iso_products']['opInherit']		= 'Dziedzicz etykietę';

$GLOBALS['TL_LANG']['tl_iso_products']['mmSrc']			= 'Podgląd';
$GLOBALS['TL_LANG']['tl_iso_products']['mmAlt']			= 'Tekst zastępczy';
$GLOBALS['TL_LANG']['tl_iso_products']['mmLink']		= 'Cel odnośnika';
$GLOBALS['TL_LANG']['tl_iso_products']['mmDesc']		= 'Opis';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslate']	= 'Tłumaczenie';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateNone']	= array('Brak', 'Nie tłumacz tego obrazka.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateText']	= array('Tekst', 'Tłumacz tekst zastępczy i opis tego obrazka.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateAll']	= array('Wszystko', 'Nie załączaj tego obrazka w przetłumaczonej wersji.');

$GLOBALS['TL_LANG']['tl_iso_products']['existing_option_set'] = 'Wybierz istniejący zestaw opcji produktu';
$GLOBALS['TL_LANG']['tl_iso_products']['new_option_set'] = 'Utwórz nowy zestaw opcji produktu';

$GLOBALS['TL_LANG']['tl_iso_products']['variantValuesLabel'] = 'Wariant';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_products']['new_product']		= array('Nowy produkt', 'Dodaj nowy produkt');
$GLOBALS['TL_LANG']['tl_iso_products']['new_variant']		= array('Dodaj wariant', 'Dodaj nowy wariant do wybranego produktu');
$GLOBALS['TL_LANG']['tl_iso_products']['edit']				= array('Edytuj produkt', 'Edytuj produkt ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['copy']				= array('Kopiuj produkt', 'Kopiuj produkt ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['cut']				= array('Przenieś produkt', 'Przenieś produkt ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['delete']			= array('Usuń produkt', 'Usuń produkt ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['toggle']			= array('Publikuj/ukryj produkt', 'Publikuj/ukryj produkt ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['show']				= array('Szczegóły produktu', 'Pokaż szczegóły produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['generate']			= array('Generuj warianty', 'Generuj warianty dla produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['related']			= array('Powiązane produkty', 'Zarządzaj powiązanymi produktami dla tego produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['filter']			= array('Zaawansowane filtry', 'Zastosuj zaawansowane filtry');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_remove']		= array('Usuń filtry', 'Usuń aktywne filtry');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_noimages']	= array('Produkty bez obrazków', 'Pokaż produkty bez przypisanych obrazków');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_nocategory']	= array('Nieprzypisane produkty', 'Pokaż produkty, które nie są przypisane do żadnej kategorii');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_today']	= array('Dodane dzisiaj', 'Pokaż produkty dodane dzisiaj');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_week']	= array('Dodane w tym tygodniu', 'Pokaż produkty dodane w ciągu ostatnich 7 dni');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_month']	= array('Dodane w tym miesiącu', 'Pokaż produkty dodane w ciągu ostatnich 30 dni');
$GLOBALS['TL_LANG']['tl_iso_products']['tools']				= array('Narzędzia', 'Więcej opcji do zarządzania produktami');
$GLOBALS['TL_LANG']['tl_iso_products']['toggleGroups']		= array('Przełącz wszystkie grupy', 'Przełącz wszystkie grupy');
$GLOBALS['TL_LANG']['tl_iso_products']['toggleVariants']	= array('Przełącz wszystkie warianty', 'Przełącz wszystkie warianty');
$GLOBALS['TL_LANG']['tl_iso_products']['import']			= array('Importuj pliki', 'Importuj obrazki i inne pliki z folderu');
$GLOBALS['TL_LANG']['tl_iso_products']['groups']			= array('Grupy produktów', 'Zarządzaj grupami produktów');
$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit']		= array('Szybka edycja wariantów', 'Szybka edycja wariantów produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['downloads']			= array('Pliki do pobrania', 'Edytuj pliki do pobrania dla produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['prices']			= array('Zarządzaj cenami', 'Zarządzaj cenami produktu ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_products']['general_legend']	= "Ogólne ustawienia";
$GLOBALS['TL_LANG']['tl_iso_products']['meta_legend']		= 'Informacje meta';
$GLOBALS['TL_LANG']['tl_iso_products']['pricing_legend']	= "Ustawienia cen";
$GLOBALS['TL_LANG']['tl_iso_products']['inventory_legend']	= 'Ustawienia inwentarzu';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_legend']	= 'Ustawienia wysyłki i dostawy';
$GLOBALS['TL_LANG']['tl_iso_products']['options_legend']	= 'Ustawienia opcji produktu';
$GLOBALS['TL_LANG']['tl_iso_products']['media_legend']		= 'Zarządzanie multimediami';
$GLOBALS['TL_LANG']['tl_iso_products']['publish_legend']	= 'Publikacja';
$GLOBALS['TL_LANG']['tl_iso_products']['variant_legend']	= 'Warianty produktu';


/**
 * Table format
 */
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min']		= 'Ilość';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min_format']	= 'od %s szt.';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['price']		= 'cena';

