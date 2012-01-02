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
 * @author     Andreas Burg <ab@andreasburg.de>
 * @author     Nikolas Runde <info@nrmedia.de>
 * @author     Patrick Grob <grob@a-sign.ch>
 * @author     Frank Berger <berger@mediastuff.de>
 * @author     Angelica Schempp <aschempp@gmx.net>
 * @author     Oliver Hoff <oliver@hoff.com>
 * @author     Stefan Preiss <stefan@preiss-at-work.de>
 * @author     Nina Gerling <gerling@ena-webstudio.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_attributes']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_attributes']['name'][1] = 'Geben Sie einen Namen für dieses Artikelmerkmal ein.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'][0] = 'Interner Name';
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'][1] = 'Der interne Feldname entspricht dem Datenbank-Feld und muss eindeutig sein.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['type'][0] = 'Typ';
$GLOBALS['TL_LANG']['tl_iso_attributes']['type'][1] = 'Wählen Sie einen Attribut-Typ.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'][0] = 'Feldgruppe';
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'][1] = 'Wählen Sie eine Feldgruppe die mit diesem Artikelmerkmal in Beziehung steht (wird benutzt um verbundene Felder in ausklappbaren Fieldset-Gruppen einzurichten, wenn Produkte bearbeitet werden.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'][0] = 'Für Varianten verwenden';
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'][1] = 'Klicken Sie hier wenn dieses Attribut für die Konfiguration von Produktvarianten verwendet wird.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'][0] = 'Durch den Kunden auswählbar.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'][1] = 'Bitte wählen Sie dieses Feld aus, wenn der Wert durch den Kunden (Frontend) ausgewählt/definiert werden kann.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description'][0] = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description'][1] = 'Die Beschreibung wird als Hinweis für den Backend-Nutzer angezeigt.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options'][0] = 'Optionen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options'][1] = 'Bitte geben Sie eine oder mehrere Optionen ein. Nutzen Sie die Buttons um eine Option hinzuzufügen, ihre Position zu verändern oder sie zu löschen. Wenn Sie ohne JavaScript-Unterstützung arbeiten, sollten Sie Ihre Änderungen speichern bevor Sie die Reihenfolge ändern!';
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'][0] = 'Pflichtfeld';
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'][1] = 'Das Produkt wird nicht in den Warenkorb gelegt, wenn das Feld leer ist.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'][0] = 'Mehrfach-Auswahl';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'][1] = 'Ermöglicht es dem Nutzer mehr als eine Option zu wählen.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['size'][0] = 'Listengröße';
$GLOBALS['TL_LANG']['tl_iso_attributes']['size'][1] = 'Hier können Sie die Größe der Auswahlbox (Select-Box) eingeben.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'][0] = 'Erlaubte Dateitypen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'][1] = 'Eine kommagetrennte Liste erlaubter Dateitypen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'][0] = 'Nutze HTML-Editor';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'][1] = 'Wähle eine tinyMCE-Konfigurationsdatei für den Rich Text Editor';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'][0] = 'Mehrsprachig';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'][1] = 'Wählen Sie ob dieses Attribut in andere Sprachen übersetzt werden muss (z.B. Textfelder).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp'][0] = 'Eingabeprüfung';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp'][1] = 'Prüft den eingegebenen Inhalt auf Basis einer Regular Expression.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'][0] = 'Maximallänge';
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'][1] = 'Schränke die Feldlänge auf eine bestimmte Zeichenanzahl (Text) oder Bytes (Datei-Upload) ein.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'][1] = 'Anstatt Optionen hinzuzufügen, können Sie hier eine table.field Kombination eingeben um Daten aus der Datenbank auszuwählen.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField'][0] = 'Übergeordnetes Feld';
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField'][1] = 'Bitte wählen Sie das übergeordnete Feld, welches vom Typ "Select-Menü" sein muss. Damit die Relation funktioniert, definieren Sie die Optionen des übergeordneten Feldes als Gruppe des abhängigen Select-Menüs.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'][0] = 'Bildergalerie';
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'][1] = 'Es können verschiedene Bildergalerien entwickelt werden um die Mediendateien angepasst anzuzeigen.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting'][0] = 'Zur "Sortieren nach"-Optionsliste hinzufügen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting'][1] = 'Dieses Feld kann im Listenmodul sortiert werden wenn das Attribut für Kunden sichtbar ist.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter'][0] = 'Filterbar im Backend';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter'][1] = 'Kann dieses Attribut im Backend als Filter genutzt werden?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'][0] = 'Durchsuchbar im Backend';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'][1] = 'Soll die Backend-Suchfunktion dieses Feld nach Suchbegriffen durchsuchen?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'][0] = 'Filterbar im Frontend';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'][1] = 'Kann dieses Attribut im Frontend als Filter genutzt werden?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'][0] = 'Durchsuchbar im Frontend';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'][1] = 'Soll die Suchmaschine dieses Feld nach Suchbegriffen durchsuchen?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opValue'] = 'Wert';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opLabel'] = 'Beschriftung';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opDefault'] = 'Standard';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opGroup'] = 'Gruppe';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit'][0] = 'Numerische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit'][1] = 'Erlaubt numerische Zeichen, Minus (-), Punkt (.) und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha'][0] = 'Alphabetische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha'][1] = 'Erlaubt alphabetische Zeichen, Minus (-), Punkt (.) und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum'][0] = 'Alphanumerische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum'][1] = 'Erlaubt alphanumerische Zeichen, Minus (-), Punkt (.) und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extnd'][0] = 'Erweiterte alphanumerische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extnd'][1] = 'Erlaubt alles außer Spezialzeichen die normalerweise aus Sicherheitsgründen kodiert werden (#/()<=>).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date'][0] = 'Datum';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date'][1] = 'Prüft ob die Eingabe dem globalen Datumsformat entspricht.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['time'][0] = 'Zeit';
$GLOBALS['TL_LANG']['tl_iso_attributes']['time'][1] = 'Prüft ob die Eingabe dem globalen Zeitformat entspricht.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim'][0] = 'Datum und Zeit';
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim'][1] = 'Prüft ob die Eingabe dem globalen Datums- und Zeitformat entspricht.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone'][0] = 'Telefonnummer';
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone'][1] = 'Erlaubt numerische Zeichen, Plus (+), Minus (-), Querstrich (/), Klammerzeichen () und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['email'][0] = 'E-Mail-Adresse';
$GLOBALS['TL_LANG']['tl_iso_attributes']['email'][1] = 'Prüft ob die Eingabe eine gültige E-Mail-Adresse ist.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['url'][0] = 'URL-Format';
$GLOBALS['TL_LANG']['tl_iso_attributes']['url'][1] = 'Prüft ob die Eingabe eine gültige URL ist.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['price'][0] = 'Preis';
$GLOBALS['TL_LANG']['tl_iso_attributes']['price'][1] = 'Prüft ob die Eingabe ein gültiger Preis ist.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount'][0] = 'Diskont';
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount'][1] = 'Prüft ob die Eingabe eine gültige Ermäßigung ist.<br />Beispiel: -10%, -10, +10, +10%';
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge'][0] = 'Preisaufschlag';
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge'][1] = 'Prüft ob die Eingabe ein gültiger Preisaufschlag ist.<br />Beispiel: 10.00, 10%';
$GLOBALS['TL_LANG']['tl_iso_attributes']['new'][0] = 'Neues Attribut';
$GLOBALS['TL_LANG']['tl_iso_attributes']['new'][1] = 'Erstellt ein neues Attribut.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'][0] = 'Attribut bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'][1] = 'Attribut mit der ID %s bearbeiten.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'][0] = 'Attribut kopieren';
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'][1] = 'Attribut mit der ID %s kopieren.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'][0] = 'Attribut löschen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'][1] = 'Löscht das Attribut mit der ID %s. Die Datenbank-Spalte wird nicht gelöscht. Sie müssen die Datenbank manuell aktualisieren indem Sie das Installationstool oder die Erweiterungsverwaltung benutzen.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['show'][0] = 'Zeige Attributdetails';
$GLOBALS['TL_LANG']['tl_iso_attributes']['show'][1] = 'Details des Attributs mit der ID %s anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['deleteConfirm'] = 'Möchten Sie wirklich das Attribut mit der ID %s löschen? Die Datenbank-Spalte wird nicht gelöscht. Sie müssen die Datenbank manuell aktualisieren indem Sie das Installationstool oder die Erweiterungsverwaltung benutzen.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['attribute_legend'] = 'Attribut-Name & -Typ';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description_legend'] = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options_legend'] = 'Optionen';
$GLOBALS['TL_LANG']['tl_iso_attributes']['config_legend'] = 'Attribut-Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_attributes']['validation_legend'] = 'Eingabeprüfung';
$GLOBALS['TL_LANG']['tl_iso_attributes']['search_filters_legend'] = 'Suche- & Filter-Einstellungen';

