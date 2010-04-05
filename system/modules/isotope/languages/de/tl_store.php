<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_store']['store_configuration_name']			= array('Name der Shop-Konfiguration', '');
$GLOBALS['TL_LANG']['tl_store']['cookie_duration']					= array('Vorhaltetage der Warenkorb-Informationen', 'Artikel und deren Informationen werden für nicht registrierte Besucher diese Anzahl Tage gespeichert. Ein Wert 0 bedeutet nach dem verlassen der Seite geht der Warenkorb verloren. Der Warenkorb wird für registrierte Mitglieder immer gespeichert.');
$GLOBALS['TL_LANG']['tl_store']['missing_image_placeholder']		= array('Platzhalter-Bild für Produkte', 'Dieses Bild wird verwendet wenn für ein Produkt kein Bild vorhanden ist.');
$GLOBALS['TL_LANG']['tl_store']['currency']							= array('Währung', 'Wählen Sie eine Währung für diesen Shop.');
$GLOBALS['TL_LANG']['tl_store']['currencySymbol']					= array('Währungssymbol verwenden', 'Das Währungssymbol verwenden ($, €) falls möglich.');
$GLOBALS['TL_LANG']['tl_store']['currencyPosition']					= array('Position der Währungsbezeichung/-symbol', 'Wählen Sie ob die Währung links oder rechts des Preises angezeigt werden soll.');
$GLOBALS['TL_LANG']['tl_store']['currencyFormat']					= array('Währungsformat', 'Wählen Sie eine Formatierung für Preise.');
$GLOBALS['TL_LANG']['tl_store']['countries']						= array('Länder', 'Wählen Sie die Länder welche bei der Bestellung zur Verfügung stehen.');
$GLOBALS['TL_LANG']['tl_store']['address_fields']					= array('Adressfelder', 'Wählen Sie die Felder welche bei einer neue Adresse ausgefüllt werden können.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_store']['left']								= 'Links des Preises';
$GLOBALS['TL_LANG']['tl_store']['right']							= 'Rechts des Preises';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_store']['new']    = array('Neue Konfiguration', 'Eine neue Shop-Konfiguration erstellen.');
$GLOBALS['TL_LANG']['tl_store']['edit']   = array('Konfiguration bearbeiten', 'Shop-Konfiguration ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_store']['copy']   = array('Konfiguration duplizieren', 'Shop-Konfiguration ID %s duplizieren.');
$GLOBALS['TL_LANG']['tl_store']['delete'] = array('Konfiguration löschen', 'Shop-Konfiguration ID %s löschen.  Dies löscht nicht die zugeordneten Dateien sondern lediglich die Grundkonfiguration.');
$GLOBALS['TL_LANG']['tl_store']['show']   = array('Konfigurationsdetails anzeigen', 'Details für Shop-Konfiguration ID %s anzeigen.');

