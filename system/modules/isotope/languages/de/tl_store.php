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
$GLOBALS['TL_LANG']['tl_store']['name']							= array('Name der Shop-Konfiguration', 'Geben Sie einen eindeutigen Namen ein.');
$GLOBALS['TL_LANG']['tl_store']['label']						= array('Bezeichnung', 'Die Bezeichnung wird im Frontend z.B. für den Store-Switcher verwendet.');
$GLOBALS['TL_LANG']['tl_store']['isDefaultStore']				= array('Standard-Konfiguration', 'Verwendet dies als Standardkonfiguration für die Anzeige im Backend.');
$GLOBALS['TL_LANG']['tl_store']['cookie_duration']				= array('Vorhaltetage der Warenkorb-Informationen', 'Artikel und deren Informationen werden für nicht registrierte Besucher diese Anzahl Tage gespeichert. Ein Wert 0 bedeutet nach dem verlassen der Seite geht der Warenkorb verloren. Der Warenkorb wird für registrierte Mitglieder immer gespeichert.');
$GLOBALS['TL_LANG']['tl_store']['missing_image_placeholder']	= array('Platzhalter-Bild für Produkte', 'Dieses Bild wird verwendet wenn für ein Produkt kein Bild vorhanden ist.');
$GLOBALS['TL_LANG']['tl_store']['priceField']					= array('Feld für Preis', 'Wählen Sie das Preis-Feld für diese Konfiguration.');
$GLOBALS['TL_LANG']['tl_store']['priceOverrideField']			= array('Feld für Ersatzpreis', 'Wählen Sie das Ersatzpreis-Feld für diese Konfiguration.');
$GLOBALS['TL_LANG']['tl_store']['priceCalculateFactor']			= array('Berechnungsfaktor', 'Standardmässig auf 1. Wählen Sie einen anderen Faktor für eine Umrechnung.');
$GLOBALS['TL_LANG']['tl_store']['priceCalculateMode']			= array('Berechnungsmodus', 'Preis dividieren oder multiplieren mit dem Faktor.');
$GLOBALS['TL_LANG']['tl_store']['priceRoundPrecision']			= array('Rundungspräzision', 'Wählen Sie die Dezimal-Präzision. Wird nur verwendet wenn der Berechnungsfaktor ungleich 0 ist. Lesen Sie dazu auch das PHP-Handbuch zur Funktion round().');
$GLOBALS['TL_LANG']['tl_store']['priceRoundIncrement']			= array('Rundungszuschlag', 'Einige Währungen (z.B. Schweizer Franken) erlauben keine 0.01 Beträge.');
$GLOBALS['TL_LANG']['tl_store']['currency']						= array('Währung', 'Wählen Sie eine Währung für diesen Shop.');
$GLOBALS['TL_LANG']['tl_store']['currencySymbol']				= array('Währungssymbol verwenden', 'Das Währungssymbol verwenden ($, €) falls möglich.');
$GLOBALS['TL_LANG']['tl_store']['currencyPosition']				= array('Position der Währungsbezeichung/-symbol', 'Wählen Sie ob die Währung links oder rechts des Preises angezeigt werden soll.');
$GLOBALS['TL_LANG']['tl_store']['currencyFormat']				= array('Währungsformat', 'Wählen Sie eine Formatierung für Preise.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_store']['left']							= 'Links des Preises';
$GLOBALS['TL_LANG']['tl_store']['right']						= 'Rechts des Preises';
$GLOBALS['TL_LANG']['tl_store']['div']							= 'Dividieren';
$GLOBALS['TL_LANG']['tl_store']['mul']							= 'Multiplizieren';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_store']['new']    						= array('Neue Konfiguration', 'Eine neue Shop-Konfiguration erstellen.');
$GLOBALS['TL_LANG']['tl_store']['edit']   						= array('Konfiguration bearbeiten', 'Shop-Konfiguration ID %s bearbeiten.');
$GLOBALS['TL_LANG']['tl_store']['copy']   						= array('Konfiguration duplizieren', 'Shop-Konfiguration ID %s duplizieren.');
$GLOBALS['TL_LANG']['tl_store']['delete'] 						= array('Konfiguration löschen', 'Shop-Konfiguration ID %s löschen.  Dies löscht nicht die zugeordneten Dateien sondern lediglich die Grundkonfiguration.');
$GLOBALS['TL_LANG']['tl_store']['show']   						= array('Konfigurationsdetails anzeigen', 'Details für Shop-Konfiguration ID %s anzeigen.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_store']['name_legend']					= 'Name';
$GLOBALS['TL_LANG']['tl_store']['address_legend']	    		= 'Adresse';
$GLOBALS['TL_LANG']['tl_store']['config_legend']				= 'Konfiguration';
$GLOBALS['TL_LANG']['tl_store']['price_legend']	    			= 'Preise';
$GLOBALS['TL_LANG']['tl_store']['currency_legend']	    		= 'Währungs-Formattierung';
$GLOBALS['TL_LANG']['tl_store']['invoice_legend']	    		= 'Rechnungen';
$GLOBALS['TL_LANG']['tl_store']['images_legend']	    		= 'Bilder';

