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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name']           = array('Name', 'Bitte geben Sie den Namen für den Status ein.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid']           = array('Die Bestellung wurde bezahlt', 'Bei diesem Status wurde die Bestellung bezahlt, daß ermöglicht das herunterladen von Dateien.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['welcomescreen']  = array('Auf der Willkommensseite anzeigen', 'Zeigt die Anzahl von Bestellungen mit diesem Status auf der Willkommensseite im Backend an');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_customer']  = array('E-Mail an die Kunden', 'Wählen Sie eine E-Mail-Vorlage aus um den Kunden zu informieren, wenn die Bestellung den Status erreicht hat.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_admin']     = array('E-Mail an den Administrator', 'Wählen Sie eine E-Mail-Vorlage aus um den Administrator zu informieren, wenn die Bestellung den Status erreicht hat.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['sales_email']    = array('Die E-Mail-Adresse des Verkaufsadministrators', 'Geben Sie eine E-Mail-Adresse an, an welche Mitteilungen gesendet werden sollen. Wenn Sie kein angeben, wird die Nachricht an die Adresse des Systemadministrators gesendet.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['new']            = array('Neuer Bestellstatus', 'Einen neuen Bestellstatus hinzufügen');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['edit']           = array('Bestellstatus bearbeiten', 'Bestellstatus ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['copy']           = array('Bestellstatus duplizieren', 'Bestellstatus ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['cut']            = array('Bestellstatus verschieben', 'Bestellstatus ID %s verschieben');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['delete']         = array('Bestellstatus löschen', 'Bestellstatus ID %s löschen');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['show']           = array('Bestellstatusdetails', 'Details des Bestellstatus ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteafter']     = array('Danach einfügen', 'Nach dem Bestellstatus ID %s einfügen');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteinto']      = array('Am Anfang einfügen', 'Am Anfang einfügen');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name_legend']    = 'Name';
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['email_legend']   = 'E-Mail-Mitteilung';
