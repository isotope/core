<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name']					= array('Name', 'Geben Sie einen Namen für dieses Modul ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type']					= array('Zahlungsmodul', 'Wählen Sie eine der unterstützen Zahlungsmethoden.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label']					= array('Bezeichnung', 'Dieser Text wird dem Kunden bei der Bestellung angezeigt.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note']					= array('Hinweise', 'Die Hinweise können im Bestätigungs-Mail mitgesendet werden (##payment_note##).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries']				= array('Aktive Länder', 'Falls diese Zahlungsart nur in gewissen Ländern unterstütz wird (Rechnungsadresse des Kunden).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules']		= array('Versandarten', 'Falls diese Zahlungsart nur für bestimmte Versandarten unterstützt wird (z.B. Barzahlung nur bei Abholung).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total']			= array('Minimaler Betrag', 'Geben Sie ein Zahl grösser als 0 ein, wenn diese Zahlungsart erst ab einem gewissen Totalbetrag zur Verfügung steht.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total']			= array('Maximaler Betrag', 'Geben Sie ein Zahl grösser als 0 ein, wenn diese Zahlungsart nur bis einem gewissen Totalbetrag zur Verfügung steht.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status']		= array('Status für neue Bestellungen', 'Wählen Sie einen zutreffenden Status für neue Bestellungen im System.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account']			= array('PayPal-Konto', 'Die E-Mail Adresse des PayPal Kontos, auf welches die Zahlung empfangen werden soll.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_business']		= array('Artikelbezeichnung', 'Diese Bezeichnung wird für Ihre Bestellung bei PayPal angezeigt.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postsale_mail']			= array('E-Mail Vorlage für Statusänderungen', 'Wählen Sie eine E-Mail Vorlage wenn bei Statusänderungen der Systemadministrator benachrichtigt werden soll.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid']		= array('Postfinance PSPID', 'Die PSPID ist Ihr eindeutiger Erkennungsname im Postfinance-System.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret']		= array('Postfinance SHA-1 Signatur', 'Diese Daten werden zur Prüfung der Zahlungsübergabe verwendet.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method']		= array('Postfinance Methode', 'Art der Datenübermittlung seitens Postfinance (Punkt 1.1 in den technischen Einstellungen).');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button']					= array('Bestellen-Button', 'Wählen Sie ein Bild falls Sie einen eigenen Bestellen-Button darstellen möchten.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug']					= array('Testsystem verwenden', 'Aktivieren Sie diese Option um ein Testsystem zu verwenden, auf dem keine echten Zahlungen ausgeführt werden.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled']				= array('Modul aktivieren', 'Klicken Sie hier wenn dieses Modul für Besucher sichtbar sein soll.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new']		= array('Neue Zahlungsart', 'Erstellen Sie eine neue Zahlungsart');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit']		= array('Zahlungsart bearbeiten', 'Zahlungsart ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy']		= array('Zahlungsart kopieren', 'Zahlungsart ID %s kopieren');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete']		= array('Zahlungsart löschen', 'Zahlungsart ID %s löschen');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show']		= array('Zahlungsartdetails', 'Details der Zahlungsart ID %s anzeigen');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend']		= 'Name & Typ';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend']		= 'Bestellhinweis';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend']		= 'Allgemeine Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend']		= 'Konfiguration des Zahlungsanbieters';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend']		= 'Modul-Aktivierung';

