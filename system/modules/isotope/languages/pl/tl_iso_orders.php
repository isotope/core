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
$GLOBALS['TL_LANG']['tl_iso_orders']['order_id']			= array('ID zamówienia');
$GLOBALS['TL_LANG']['tl_iso_orders']['uniqid']				= array('Unikalne ID');
$GLOBALS['TL_LANG']['tl_iso_orders']['status']				= array('Status zamówienia', 'Proszę wybrrać status tego zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date_paid']			= array('Data płatności', 'Proszę wprowadzić datę płatności za to zamówienie.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped']		= array('Data wysyłki', 'Proszę wprowadzić datę wysyłki tego zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date']				= array('Data');
$GLOBALS['TL_LANG']['tl_iso_orders']['payment_id']			= array('Metoda płatności');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_id']			= array('Metoda dostawy');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address']	= array('Adres dostawy');
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address']		= array('Adres rozliczeniowy');

$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'] = array('Podsuma');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'] = array('Metoda dostawy');
$GLOBALS['TL_LANG']['tl_iso_orders']['surcharges'] = array('Dopłaty');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'] = array('Numer karty', 'Numer karty kredytowej');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'] = array('Number CCV', '3 lub 4 cyfrowy number weryfikacyjny kardy kredytowej');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'] = array('Typ karty', 'Typ karty kredytowej.');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'] = array('Wygasa', 'Data wygaśnięcia karty kredytowej');
$GLOBALS['TL_LANG']['tl_iso_orders']['notes'] = array('Notatki','Jeśli chcesz przekazać dodatkowe informacje dla innych użytkowników, zrób to tutaj.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['new']					= array('Nowe zamówienie', 'Utwórz nowe zamówienie');
$GLOBALS['TL_LANG']['tl_iso_orders']['edit']				= array('Edytuj zamówienie', 'Edytuj zamówienie ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['copy']				= array('Kopiuj zamówienie', 'Kopiuj zamówienie ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['delete']				= array('Usuń zamówienie', 'Usuń zamówienie ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['show']				= array('Szczegóły zamówienia', 'Pokaż szczegóły zamówienia ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order']			= array('Drukuj zamówienie', 'Drukuj fakturę dla tego zamówienia');
$GLOBALS['TL_LANG']['tl_iso_orders']['tools']				= array('Narzędzia', 'Więcej narzędzi do zarządzania zamówieniami.');
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails']		= array('Eksportuj e-maile zamówień', 'Eksportuj wszystkie adresy e-mail klientów, którzy złożyli zamówienia.');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices']		= array('Drukuj faktury', 'Drukuj jedną lub więcej faktur w jednym dokumencie o wybranym statusie zamówienia.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['status_legend']	= 'Status zamówienia';
$GLOBALS['TL_LANG']['tl_iso_orders']['details_legend']	= 'Szczegóły zamówienia';
$GLOBALS['TL_LANG']['tl_iso_orders']['email_legend']	= 'Informacje e-mail';
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address_legend']	= 'Adres rozliczeniowy';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address_legend']	= 'Adres dostawy';
