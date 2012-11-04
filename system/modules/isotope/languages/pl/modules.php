<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Radosław Maślanek <radek@dziupla.pl>
 * @author     Kamil Kuźmiński <kamil.kuzminski@gmail.com>
 */


/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['iso_products']				= array('Zarządzanie produktami','');
$GLOBALS['TL_LANG']['MOD']['iso_orders']				= array('Zamówienia', '');
$GLOBALS['TL_LANG']['MOD']['iso_setup']					= array('Konfiguracje sklepów','');

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter']			= array('Filtry produktów', 'Definiuj indywidualne filtry dla Isotope, takie jak drzewa kategorii i atrybuty produków.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist']			= array('Lista produktów', 'Główny moduł listy produktów. Może być użyty do wyświetlenia listy produktów lub wartości atrybutów. Może być używany z innymi modułami (np. moduł filtrowania).');
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist']	= array('Lista wariantów produktu', 'Wyświetla listę każdego wariantu produktu. Używaj templatki iso_list_variants z tym modułem.');
$GLOBALS['TL_LANG']['FMD']['iso_productreader']			= array('Przeglądarka produktów', 'Ten moduł wyświetla pełny opis produktu');
$GLOBALS['TL_LANG']['FMD']['iso_cart']					= array('Koszyk', 'W pełni fukncjonalny koszyk produktów. Tryb wyświetlania zależy od templatki.');
$GLOBALS['TL_LANG']['FMD']['iso_checkout']				= array('Kasa', 'Miejsce, gdzie klienci finalizują swoje transakcje.');
$GLOBALS['TL_LANG']['FMD']['iso_addressbook']			= array('Książka adresowa','Miejsce, gdzie klienci zarządzają swoimi książkami adresowymi.');
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory']			= array('Historia zamówień', 'Lista sfinalizowanych zamówień klienta.');
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails']			= array('Szczegóły zamówienia', 'Czytnik zamówienia, który pozwala klientowi na dostęp do historii zamówienia.');
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher']		= array('Zmiana konfiguracji sklepu', 'Przełącznik pomiędzy konfiguracjami sklepu, np. do zmiany waluty i innych ustawień.');
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts']		= array('Powiązane produkty', 'Lista produktów powiązanych z aktywnie wyświetlonym.');


/**
 * Isotope Modules
 */
$GLOBALS['TL_LANG']['ISO']['config_module']				= 'Isotope eCommerce konfiguracja';
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

