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
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name']					= array('Nazwa metody wysyłki');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type']					= array('Typ metody wysyłki');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price']					= array('Cena');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note']					= array('Notatki', 'Te notatki zostaną wyświetlone na stronie przy tej opcji wysyłki.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class']				= array('Klasa podatku');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label']					= array('Etykieta', 'Etykieta zostanie wyświetlona na stronie przy tej opcji wysyłki.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation']		= array('Płaska kalkulacja');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit']			= array('Jednostka wagi', 'Wprowadź jednostkę wagi.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries']				= array('Kraje', 'Wybierz kraje, do których ta metoda wysyłki jest dostępna. Jeśli nic nie wybierzesz, wysyłka będzie dostępna do wszystkich krajów.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions']			= array('Stany/regiony', 'Wybierz stany/regiony, do których ta metoda wysyłki jest dostępna. Jeśli nic nie wybierzesz, wysyłka będzie dostępna do wszystkich stanów/regionów.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['postalCodes']			= array('Kody pocztowe', 'Ogranicz ten moduł wysyłki do kodów pocztowych. Możesz wprowadzić wiele kodów oraz zakresy, oddzielając je przecinkami (np. 1234,1235,1236-1239,1100-1200).');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total']			= array('Minimalna wartość');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total']			= array('Maksymalna wartość');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types']			= array('Typy produktów', 'Tutaj możesz zastrzec dla jakich typów produktów ta metoda wysyłki jest dostępna. Jeśli koszyk zawiera produkt, który nie jest zaznaczony, moduł wysyłki będzie niedostępny.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field']		= array('Dopłata za wysyłkę', 'Proszę określić dopłatę, która będzie zastosowana dla tej metody wysyłki.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups']				= array('Grupy użytkowników', 'Zastrzeż tę metodę wysyłki do konkretnych grup użytkowników.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected']      		= array('Chroń moduł', 'Pokaż metodę wysyłki tylko konkretnym grupom użytkowników.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests']         		= array('Pokaż tylko dla gości', 'Ukryj metodę wysyłki, jeśli użytkownik jest zalogowany.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled']				= array('Aktywny', 'Zaznacz tutaj jeśli moduł wysyłki ma być aktywny.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey']     	= array('UPS XML/HTML access key','This is a special alphanumeric key issued by UPS once you sign up for a UPS account and for access to the UPS Online Tools API');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName']     		= array('UPS username','This is the UPS account username that you chose while signing up on the UPS website.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password']     		= array('UPS password','This is the UPS password that you chose while signing up on the UPS website.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService']	= array('UPS Service Type','Select a UPS shipping service to offer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService']	= array('USPS Service Type','Select a USPS shipping service to offer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName']			= array('USPS username','This is the USPS account username that you chose while signing up on the USPS website.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['title_legend']		= 'Tytuł i typ';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note_legend']		= 'Notatka';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['config_legend']		= 'Konfiguracja';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_legend']		= 'Ustawienia UPS API';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_legend']		= 'Ustawienia USPS API';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price_legend']		= 'Próg cenowy i klasa podatku';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['expert_legend']		= 'Zaawansowane ustawienia';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled_legend']	= 'Ustawienia dostępności';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new']				= array('Nowa metoda wysyłki', 'Dodaj nową metodę wysyłki');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit']				= array('Edytuj metodę wysyłki', 'Edytuj metodę wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy']				= array('Kopiuj metodę wysyłki', 'Kopiuj metodę wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete']			= array('Usuń metodę wysyłki', 'Usuń metodę wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show']				= array('Pokaż szczegóły metody wysyłk', 'Pokaż szczegóły metody wysyłki ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates']	= array('Edytuj zasady', 'Edytuj stawki wysyłki');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat']					 						= 'Płaski';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct']			 						= 'Za produkt';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem']				 						= 'Za rzecz';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['01'] 	 						= 'Next Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['02'] 	 						= '2nd Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['03'] 	 						= 'UPS Ground';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['07']		 						= 'Worldwide Express';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['08']		 						= 'Worldwide Expedited';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['11'] 	 						= 'International Standard';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['12'] 	 						= '3 Day Select';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['13'] 	 						= 'Next Day Air Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['14'] 	 						= 'Next Day Air Early AM';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['54'] 	 						= 'Worldwide Express Plus';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['65'] 	 						= 'International Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PARCEL'] 						= 'USPS Parcel Post';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY'] 						= 'USPS Priority Mail (2-3 days average)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS'] 						= 'USPS Express Mail (Overnight Guaranteed)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['FIRST CLASS']					= 'USPS First Class';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY COMMERCIAL'] 			= 'USPS Priority Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS COMMERCIAL']			= 'USPS Express Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH']					= 'USPS Express Sundays & Holidays';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH COMMERCIAL']			= 'USPS Express Sundays & Holidays Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP']					= 'USPS Express Hold For Pickup';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP COMMERCIAL']		= 'USPS Express Hold For Pickup Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['BPM'] 							= 'USPS Bound Printed Matter';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['MEDIA'] 						= 'USPS Media Mail';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['LIBRARY']						= 'USPS Library Mail';

