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
 * @author     Kamil Kuźmiński <kamil.kuzminski@gmail.com>
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name']			= array('Nazwa stawki podatku','Wprowadź nazwę stawki podatku.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label']			= array('Etykieta stawki podatku','Ta etykieta zostanie używa we frontendzie przy składaniu zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address']		= array('Adres do użycia w kalkulacji', 'Wybierz, który adres powinien zostać użyty do kalkulacji.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postalCodes']	= array('Kody pocztowe', 'Ogranicz tę stawkę podatku do kodów pocztowych. Możesz wprowadzić wiele kodów oraz zakresy, oddzielając je przecinkami (np. 1234,1235,1236-1239,1100-1200).');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount']		= array('Ograniczenie wartości podsumy', 'Opcjonalne: Zastrzeż tę stawkę podatku do określonej wartości podsumy.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate']			= array('Stawka podatku', 'Stawka podatku w procentach.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'] 		= array('Konfiguracja sklepu', 'Proszę wybrać konfigurację sklepu dla tej stawki podatku.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'] 			= array('Zatrzymać kalkulacje przy przełączeniu?', 'Zatrzymaj inne kalkulacje jeśli ta stawka jest użyta.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new']			= array('Nowa stawka podatku', 'Dodaj nową stawkę podatku');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit']			= array('Edytuj stawkę podatku', 'Edytuj stawkę podatku ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy']			= array('Kopiuj stawkę podatku', 'Kopiuj stawkę podatku ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete']		= array('Usuń stawkę podatku', 'Usuń stawkę podatku ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show']			= array('Szczegóły zamówienia', 'Pokaż szczegóły stawki podatku ID %s');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['billing']		= 'Adres rozliczeniowy';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['shipping']		= 'Adres wysyłki';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name_legend']		= 'Nazwa';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate_legend']		= 'Stawka';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['location_legend']	= 'Lokalizacja';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['condition_legend']	= 'Warunki';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config_legend']		= 'Konfiguracja';
