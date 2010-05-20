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
$GLOBALS['TL_LANG']['tl_iso_config']['name']						= array('Name der Shop-Konfiguration', 'Geben Sie einen eindeutigen Namen ein.');
$GLOBALS['TL_LANG']['tl_iso_config']['label']						= array('Bezeichnung', 'Die Bezeichnung wird im Frontend z.B. für den Konfigurationswechsler verwendet.');
$GLOBALS['TL_LANG']['tl_iso_config']['fallback']					= array('Standard-Konfiguration', 'Verwendet dies als Standardkonfiguration für die Anzeige im Backend.');
$GLOBALS['TL_LANG']['tl_iso_config']['cookie_duration']				= array('Vorhaltetage der Warenkorb-Informationen', 'Artikel und deren Informationen werden für nicht registrierte Besucher diese Anzahl Tage gespeichert. Ein Wert 0 bedeutet nach dem verlassen der Seite geht der Warenkorb verloren. Der Warenkorb wird für registrierte Mitglieder immer gespeichert.');
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder']	= array('Platzhalter-Bild für Produkte', 'Dieses Bild wird verwendet wenn für ein Produkt kein Bild vorhanden ist.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor']		= array('Berechnungsfaktor', 'Standardmässig auf 1. Wählen Sie einen anderen Faktor für eine Umrechnung.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode']			= array('Berechnungsmodus', 'Preis dividieren oder multiplieren mit dem Faktor.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision']			= array('Rundungspräzision', 'Wählen Sie die Dezimal-Präzision. Wird nur verwendet wenn der Berechnungsfaktor ungleich 1 ist. Lesen Sie dazu auch das PHP-Handbuch zur Funktion round().');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement']			= array('Rundungszuschlag', 'Einige Währungen (z.B. Schweizer Franken) erlauben keine 0.01 Beträge.');
$GLOBALS['TL_LANG']['tl_iso_config']['currency']					= array('Währung', 'Wählen Sie eine Währung für diesen Shop.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol']				= array('Währungssymbol verwenden', 'Das Währungssymbol verwenden ($, €) falls möglich.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition']			= array('Position der Währungsbezeichung/-symbol', 'Wählen Sie ob die Währung links oder rechts des Preises angezeigt werden soll.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat']				= array('Währungsformat', 'Wählen Sie eine Formatierung für Preise.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_config']['left']						= 'Links des Preises';
$GLOBALS['TL_LANG']['tl_iso_config']['right']						= 'Rechts des Preises';
$GLOBALS['TL_LANG']['tl_iso_config']['div']							= 'Dividieren';
$GLOBALS['TL_LANG']['tl_iso_config']['mul']							= 'Multiplizieren';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_config']['new']    						= array('Neue Konfiguration', 'Eine neue Shop-Konfiguration erstellen.');
$GLOBALS['TL_LANG']['tl_iso_config']['edit']   						= array('Konfiguration bearbeiten', 'Shop-Konfiguration ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_iso_config']['copy']   						= array('Konfiguration duplizieren', 'Shop-Konfiguration ID %s duplizieren.');
$GLOBALS['TL_LANG']['tl_iso_config']['delete'] 						= array('Konfiguration löschen', 'Shop-Konfiguration ID %s löschen.  Dies löscht nicht die zugeordneten Dateien sondern lediglich die Grundkonfiguration.');
$GLOBALS['TL_LANG']['tl_iso_config']['show']   						= array('Konfigurationsdetails anzeigen', 'Details für Shop-Konfiguration ID %s anzeigen.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name_legend']					= 'Name';
$GLOBALS['TL_LANG']['tl_iso_config']['address_legend']	    		= 'Adresse';
$GLOBALS['TL_LANG']['tl_iso_config']['config_legend']				= 'Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_config']['price_legend']	    		= 'Preise';
$GLOBALS['TL_LANG']['tl_iso_config']['currency_legend']	    		= 'Währungs-Formattierung';
$GLOBALS['TL_LANG']['tl_iso_config']['invoice_legend']	    		= 'Rechnungen';
$GLOBALS['TL_LANG']['tl_iso_config']['images_legend']	    		= 'Bilder';

