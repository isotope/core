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
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name']					= array('Nazwa', 'Proszę wprowadzić nazwę dla tego typu produktu.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['class']					= array('Klasa produktu', 'Proszę wybrać klasę produktu.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback']				= array('Domyślny', 'Zaznacz, jeśli to jest domyślny typ produktu.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices']				= array('Zaawnsowane ceny', 'Pozwala na określenie wielu cen dla produktu, np. dla różnych konfiguracji sklepów, grup użytkowników czy dat.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template']			= array('Szablon listy', 'Wybierz szablon dla listy produktów.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template']		= array('Szablon czytnika', 'Wybierz szablon dla czytnika produktów.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['description']			= array('Opis', 'Podpowiedź dla menedżerów produktów, do czego nadaje się ten typ.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes']			= array('Atrybuty', 'Wybierz kolekcję atrybutów, która powinna być dołączona dla tego typu produktu.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants']				= array('Włącz warianty', 'Zaznacz, jeśli ten typ produktu ma warianty.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variant_attributes']	= array('Atrybuty wariantów', 'Wybierz kolekcję wariantów atrybutów, która będze dołączona do tego typu produktu. Te, które nie są zaznaczone, będą ukryte i odziedziczone z rodzica produktu.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['force_variant_options']	= array('Zawsze pokazuj atrybuty wariantow', 'Pokaż warianty atrybutów (select, radio), nawet jeśli jest tylko jeden wybór.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads']				= array('Włącz pliki do pobrania', 'Zaznacz, jeśli typ produktu ma pliki do pobrania.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['new']    				= array('Nowy typ produktu', 'Dodaj nowy typ produktu.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit']   				= array('Edytuj typ produktu', 'Edytuj typ produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy']   				= array('Kopiuj definicję typu produktu', 'Kopiuj definicję typu produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'] 				= array('Usuń typ produktu', 'Usuń typ produktu ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['show']   				= array('Szczegóły typu produktu', 'Pokaż szczegóły typu produktu ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name_legend']			= 'Ustawienia typu produktu';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['description_legend']	= 'Opis';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['template_legend']		= 'Szablony';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes_legend']		= 'Atrbyuty produktu';


/**
 * AttributeWizard
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['tl_class_select']	= 'Tutaj możesz wybrać sposród predfiniowanych klas CSS Contao.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['tl_class_text']		= 'Tutaj możesz wpisać własne klasy CSS, które będą zaaplikowane dla pola';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['mandatory_default']	= 'Obowiązkowe: Weź domyślną wartość';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['mandatory_no']		= 'Obowiązkowe: Nie, nigdy';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['mandatory_yes']		= 'Obowiązkowe: Tak, zawsze';
