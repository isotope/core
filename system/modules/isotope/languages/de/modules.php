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
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['store']						= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['product_manager']			= array('Produkte', 'Verwalten Sie Produkte und Produkt-Varianten.');
$GLOBALS['TL_LANG']['MOD']['orders']					= array('Bestellungen', 'Alle Bestellungen in Ihrem Online-Shop sind hier ersichtlich.');
$GLOBALS['TL_LANG']['MOD']['iso_statistics']			= array('Verkaufsstatistik', '');
$GLOBALS['TL_LANG']['MOD']['isotope']					= array('Shop-Konfiguration', 'Hier können Sie Isotope eCommerce konfigurieren.');


/**
 * Frontend modules
 */ 
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter']			= array('Produkt Filter', 'Erstellt individuelle Filter für Isotope wie einen Kategorienbaum oder Produktattribut-Filter.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist']			= array('Produktliste', 'Allgemeines Listen-Modul. Zeigt Produkte oder Werte von Attributen an. Kann mit anderen Modulen (z.B. Filter-Modul) kombiniert werden um die Funktionen zu erweitern.');
$GLOBALS['TL_LANG']['FMD']['iso_productreader']			= array('Produktleser', 'Mit diesem Modul zeigen Sie Produktdetails an.');
$GLOBALS['TL_LANG']['FMD']['iso_cart']					= array('Warenkorb', 'Ein vollwertiges Warenkorb-Modul. Klein- oder Grossanzeige kann durch Wahl des Templates eingestellt werden.');
$GLOBALS['TL_LANG']['FMD']['iso_checkout']				= array('Kasse', 'Erlaubt Kunden eine Bestellung auszuführen.');
$GLOBALS['TL_LANG']['FMD']['iso_addressbook']			= array('Adressbuch','Erlaubt Kunden deren Adressbuch zu bearbeiten.');
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory']			= array('Vergangene Bestellungen', 'Zeigt eine Liste der bisherigen Bestellungen an.');
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails']			= array('Bestellungsdetails', 'Kunden können mit diesem Modul ihre vergangenen Bestellungen sehen und z.B. Download-Artikel herunterladen.');


/**
 * Isotope modules
 */
$GLOBALS['TL_LANG']['IMD']['checkout']					= 'Bestellablauf';
$GLOBALS['TL_LANG']['IMD']['product']					= 'Produkte';
$GLOBALS['TL_LANG']['IMD']['config']					= 'Allgemeine Einstellungen';
$GLOBALS['TL_LANG']['IMD']['shipping']					= array('Versandarten', 'Legen Sie Versandarten für verschiedene Regionen an, welche Sie beliefern. Wenn im Warenkorb nur Artikel enthalten sind, welche keinen Versand erfordern, wird die Versandart in der Kasse nicht berücksichtigt.');
$GLOBALS['TL_LANG']['IMD']['payment']					= array('Zahlungsarten', 'Hier definieren Sie die für Ihren Shop erlaubten Zahlungsarten. Es werden verschiedene Zahlungsmodule unterstützt (z.B. PayPal, Barzahlung usw). Zahlungsmodule können bezüglich Produkttypen, Versandarten, Länder und anderen Einstellungen eingeschränkt werden. Es ist gut möglich dass Sie für dieselbe Zahlungsart mehrere Module anlegen müssen, um die volle Flexibilität zu erreichen.');
$GLOBALS['TL_LANG']['IMD']['tax_class']					= array('Steuerklassen', 'In Steuerklassen gruppieren Sie mehrere Steuersätze, um diesen dann den entsprechenden Produkten zuzuweisen. In Steuerklassen können Sie auch festlegen ob ein Produktpreis bereits Steuern enthält (welche dann ggf. beim Versand ins Ausland abgezogen werden).');
$GLOBALS['TL_LANG']['IMD']['tax_rate']					= array('Steuersätze', 'Steuersätze definieren die Gebühren welche zum Preis hinzugerechnet werden sollen. Dies muss nicht zwingend eine Umsatzsteuer sein, sondern z.B. auch ein Zuschlag bei verwenden einer bestimmten Zahlungsart (Kreditkarte).');
$GLOBALS['TL_LANG']['IMD']['product_type_manager']		= array('Produkttypen', 'Mittels Produkttypen können Sie Ihre Produkte gruppieren. Verschiedene Produttypen können verschiedene Felder und Funktionen (z.B. Downloads aktiveren) enthalten.');
$GLOBALS['TL_LANG']['IMD']['attribute_manager']			= array('Attribute', 'In der Attributverewaltung können Sie eigene Felder für Ihre Produkte anlegen. Dies ist z.B. hilfreich wenn Ihr Produkt einen Untertitel haben soll. Beachten Sie dass die entsprechenden Felder dann manuell im Template ausgegeben werden müssen (=Template-Anpassung).');
$GLOBALS['TL_LANG']['IMD']['iso_mail']					= array('E-Mail Vorlagen', 'E-Mail Vorlagen werden für den Versand von Bestellbestätigungen und anderen Nachrichten an Kunden und Shopbetreiber verwendet. Jede Vorlage kann in mehrere Sprachen angelegt werden. Vergessen Sie nicht bei der Hauptsprache den Sprachen-Fallback zu aktivieren!');
$GLOBALS['TL_LANG']['IMD']['store_configuration']		= array('Konfigurationen', 'Konfigurationen umfassen Einstellungen bezüglich Währung, Preisberechnung, Adressaufbereitung, Produktbildgrössen und anderem. Sie können mehrere Konfigurationen anlegen und diese im Shop-Betrieb (über das entsprechende Frontend-Modul) wechseln, um z.B. eine Umschaltung zwischen mehreren Währungen zu ermöglichen.');
