<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *
 * PHP version 5
 * @copyright  Rispler&Rispler Designer Partnerschaftsgesellschaft 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    commercial
 * @version    $Id$
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupId']			= array('ExperCash Popup-ID', 'Geben Sie die Popup-ID aus Ihrem ExperCash Portal ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_profile']			= array('ExperCash Profile', 'Geben Sie die dreistellige Profilnummer ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupKey']			= array('ExperCash Popup-Key', 'Geben Sie den Popup-Key aus Ihrem ExperCash Portal ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']	= array('Transaktionsart', 'Sie können eine Transaktionsart vordefinieren oder den Kunden wählen lassen.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_css']				= array('CSS-Vorlage', 'Wählen Sie eine CSS-Datei für die Übergabe an ExperCash.');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['automatic_payment_method'] = 'Auswahl der Zahlart durch den Endkunden';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['elv_buy'] = 'Zahlung per Lastschrift (ELV)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['elv_authorize'] = 'Prüfung und Speicherung von Kontodaten zum späteren Einzug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['cc_buy'] = 'Kreditkartenzahlung';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['cc_authorize'] = 'verbindliche Reservierung auf eine Kreditkarte zum späteren Einzug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['giropay'] = 'Transaktion über giropay';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod']['sofortueberweisung'] = 'Transaktion über Sofortüberweisung';

