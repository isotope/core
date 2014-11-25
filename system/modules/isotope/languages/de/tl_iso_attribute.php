<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 * 
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/de/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_attribute']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_attribute']['name'][1] = 'Geben Sie einen Namen für dieses Artikelmerkmal ein.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['field_name'][0] = 'Interner Name';
$GLOBALS['TL_LANG']['tl_iso_attribute']['field_name'][1] = 'Der interne Feldname entspricht dem Datenbank-Feld und muss eindeutig sein.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['type'][0] = 'Typ';
$GLOBALS['TL_LANG']['tl_iso_attribute']['type'][1] = 'Wählen Sie einen Attribut-Typ.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['legend'][0] = 'Feldgruppe';
$GLOBALS['TL_LANG']['tl_iso_attribute']['legend'][1] = 'Wählen Sie eine Feldgruppe die mit diesem Artikelmerkmal in Beziehung steht (wird benutzt um verbundene Felder in ausklappbaren Fieldset-Gruppen einzurichten, wenn Produkte bearbeitet werden.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['variant_option'][0] = 'Für Varianten verwenden';
$GLOBALS['TL_LANG']['tl_iso_attribute']['variant_option'][1] = 'Klicken Sie hier wenn dieses Attribut für die Konfiguration von Produktvarianten verwendet wird.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['customer_defined'][0] = 'Durch den Kunden auswählbar';
$GLOBALS['TL_LANG']['tl_iso_attribute']['customer_defined'][1] = 'Bitte wählen Sie dieses Feld aus, wenn der Wert durch den Kunden (Frontend) ausgewählt/definiert werden kann.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['description'][0] = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_iso_attribute']['description'][1] = 'Die Beschreibung wird als Hinweis für den Backend-Nutzer angezeigt.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource']['product'] = 'Produkt';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options'][0] = 'Optionen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options'][1] = 'Bitte geben Sie eine oder mehrere Optionen ein. Nutzen Sie die Buttons um eine Option hinzuzufügen, ihre Position zu verändern oder sie zu löschen. Wenn Sie ohne JavaScript-Unterstützung arbeiten, sollten Sie Ihre Änderungen speichern bevor Sie die Reihenfolge ändern!';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['value'][0] = 'Wert';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['label'][0] = 'Bezeichnung';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['default'][0] = 'Standard';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['group'][0] = 'Gruppe';
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsTable'][0] = 'Optionen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['mandatory'][0] = 'Pflichtfeld';
$GLOBALS['TL_LANG']['tl_iso_attribute']['mandatory'][1] = 'Das Produkt wird nicht in den Warenkorb gelegt, wenn das Feld leer ist.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['multiple'][0] = 'Mehrfach-Auswahl';
$GLOBALS['TL_LANG']['tl_iso_attribute']['multiple'][1] = 'Ermöglicht es dem Nutzer mehr als eine Option zu wählen.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['size'][0] = 'Listengröße';
$GLOBALS['TL_LANG']['tl_iso_attribute']['size'][1] = 'Hier können Sie die Größe der Auswahlbox (Select-Box) eingeben.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['extensions'][0] = 'Erlaubte Dateitypen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['extensions'][1] = 'Eine kommagetrennte Liste erlaubter Dateitypen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['rte'][0] = 'Nutze HTML-Editor';
$GLOBALS['TL_LANG']['tl_iso_attribute']['rte'][1] = 'Wähle eine tinyMCE-Konfigurationsdatei für den Rich Text Editor';
$GLOBALS['TL_LANG']['tl_iso_attribute']['multilingual'][0] = 'Mehrsprachig';
$GLOBALS['TL_LANG']['tl_iso_attribute']['multilingual'][1] = 'Wählen Sie ob dieses Attribut in andere Sprachen übersetzt werden muss (z.B. Textfelder).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['rgxp'][0] = 'Eingabeprüfung';
$GLOBALS['TL_LANG']['tl_iso_attribute']['rgxp'][1] = 'Prüft den eingegebenen Inhalt auf Basis einer Regular Expression.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['maxlength'][0] = 'Maximallänge';
$GLOBALS['TL_LANG']['tl_iso_attribute']['maxlength'][1] = 'Schränke die Feldlänge auf eine bestimmte Zeichenanzahl (Text) oder Bytes (Datei-Upload) ein.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['foreignKey'][0] = 'Fremdtabelle und Feld';
$GLOBALS['TL_LANG']['tl_iso_attribute']['foreignKey'][1] = 'Anstatt Optionen hinzuzufügen, können Sie hier eine table.field Kombination eingeben um Daten aus der Datenbank auszuwählen. Mehrsprachigkeit lässt sich durch Angabe der Sprache pro Zeile realisieren (Beispiel: en=table.field)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['conditionField'][0] = 'Übergeordnetes Feld';
$GLOBALS['TL_LANG']['tl_iso_attribute']['conditionField'][1] = 'Bitte wählen Sie das übergeordnete Feld, welches vom Typ "Select-Menü" sein muss. Damit die Relation funktioniert, definieren Sie die Optionen des übergeordneten Feldes als Gruppe des abhängigen Select-Menüs.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['files'][0] = 'Dateien anzeigen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['files'][1] = 'Klicken Sie hier um Dateien (und nicht nur Ordner) anzuzeigen.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['filesOnly'][0] = 'Nur Dateien anzeigen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['filesOnly'][1] = 'Auswahl von Ordnern nicht erlauben.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fieldType'][0] = 'Feld-Typ';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fieldType'][1] = 'Wählen Sie ob eine oder mehrere Datei gewählt werden können (Radio- oder Checkbox-Auswahl).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['sortBy'][0] = 'Sortieren nach';
$GLOBALS['TL_LANG']['tl_iso_attribute']['sortBy'][1] = 'Bitte wählen Sie eine Sortierreihenfolge aus.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['path'][0] = 'Basisverzeichnis';
$GLOBALS['TL_LANG']['tl_iso_attribute']['path'][1] = 'Wählen Sie ab welchem Verzeichnis die Ordnerstruktur angezeigt werden soll.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['storeFile'][0] = 'Hochgeladene Dateien speichern';
$GLOBALS['TL_LANG']['tl_iso_attribute']['storeFile'][1] = 'Die hochgeladenen Dateien in einen Ordner auf dem Server verschieben.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['uploadFolder'][0] = 'Zielverzeichnis';
$GLOBALS['TL_LANG']['tl_iso_attribute']['uploadFolder'][1] = 'Bitte wählen Sie das Zielverzeichnis aus der Dateiübersicht.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['useHomeDir'][0] = 'Benutzerverzeichnis verwenden';
$GLOBALS['TL_LANG']['tl_iso_attribute']['useHomeDir'][1] = 'Die Datei im Benutzerverzeichnis speichern, wenn sich ein Benutzer angemeldet hat.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['doNotOverwrite'][0] = 'Bestehende Dateien erhalten';
$GLOBALS['TL_LANG']['tl_iso_attribute']['doNotOverwrite'][1] = 'Der neuen Datei ein numerisches Suffix hinzufügen, wenn der Dateiname bereits existiert.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_sorting'][0] = 'Zur "Sortieren nach"-Optionsliste hinzufügen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_sorting'][1] = 'Dieses Feld kann im Listenmodul sortiert werden wenn das Attribut für Kunden sichtbar ist.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['be_filter'][0] = 'Filterbar im Backend';
$GLOBALS['TL_LANG']['tl_iso_attribute']['be_filter'][1] = 'Kann dieses Attribut im Backend als Filter genutzt werden?';
$GLOBALS['TL_LANG']['tl_iso_attribute']['be_search'][0] = 'Durchsuchbar im Backend';
$GLOBALS['TL_LANG']['tl_iso_attribute']['be_search'][1] = 'Soll die Backend-Suchfunktion dieses Feld nach Suchbegriffen durchsuchen?';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_filter'][0] = 'Filterbar im Frontend';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_filter'][1] = 'Kann dieses Attribut im Frontend als Filter genutzt werden?';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_search'][0] = 'Durchsuchbar im Frontend';
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_search'][1] = 'Soll die Suchmaschine dieses Feld nach Suchbegriffen durchsuchen?';
$GLOBALS['TL_LANG']['tl_iso_attribute']['datepicker'][0] = 'Datumsauswahl';
$GLOBALS['TL_LANG']['tl_iso_attribute']['datepicker'][1] = 'Datumsauswahl für dieses Feld anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['checkbox'] = 'Checkbox';
$GLOBALS['TL_LANG']['tl_iso_attribute']['radio'] = 'Radio-Button';
$GLOBALS['TL_LANG']['tl_iso_attribute']['digit'][0] = 'Numerische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['digit'][1] = 'Erlaubt numerische Zeichen, Minus (-), Punkt (.) und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['alpha'][0] = 'Alphabetische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['alpha'][1] = 'Erlaubt alphabetische Zeichen, Minus (-), Punkt (.) und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['alnum'][0] = 'Alphanumerische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['alnum'][1] = 'Erlaubt alphanumerische Zeichen, Minus (-), Punkt (.) und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['extnd'][0] = 'Erweiterte alphanumerische Zeichen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['extnd'][1] = 'Erlaubt alles außer Spezialzeichen die normalerweise aus Sicherheitsgründen kodiert werden (#/()<=>).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['date'][0] = 'Datum';
$GLOBALS['TL_LANG']['tl_iso_attribute']['date'][1] = 'Prüft ob die Eingabe dem globalen Datumsformat entspricht.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['time'][0] = 'Zeit';
$GLOBALS['TL_LANG']['tl_iso_attribute']['time'][1] = 'Prüft ob die Eingabe dem globalen Zeitformat entspricht.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['datim'][0] = 'Datum und Zeit';
$GLOBALS['TL_LANG']['tl_iso_attribute']['datim'][1] = 'Prüft ob die Eingabe dem globalen Datums- und Zeitformat entspricht.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['phone'][0] = 'Telefonnummer';
$GLOBALS['TL_LANG']['tl_iso_attribute']['phone'][1] = 'Erlaubt numerische Zeichen, Plus (+), Minus (-), Querstrich (/), Klammerzeichen () und Leerzeichen ( ).';
$GLOBALS['TL_LANG']['tl_iso_attribute']['email'][0] = 'E-Mail-Adresse';
$GLOBALS['TL_LANG']['tl_iso_attribute']['email'][1] = 'Prüft ob die Eingabe eine gültige E-Mail-Adresse ist.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['url'][0] = 'URL-Format';
$GLOBALS['TL_LANG']['tl_iso_attribute']['url'][1] = 'Prüft ob die Eingabe eine gültige URL ist.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['price'][0] = 'Preis';
$GLOBALS['TL_LANG']['tl_iso_attribute']['price'][1] = 'Prüft ob die Eingabe ein gültiger Preis ist.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['discount'][0] = 'Ermäßigung';
$GLOBALS['TL_LANG']['tl_iso_attribute']['discount'][1] = 'Prüft ob die Eingabe eine gültige Ermäßigung ist.<br />Beispiel: -10%, -10, +10, +10%';
$GLOBALS['TL_LANG']['tl_iso_attribute']['surcharge'][0] = 'Preisaufschlag';
$GLOBALS['TL_LANG']['tl_iso_attribute']['surcharge'][1] = 'Prüft ob die Eingabe ein gültiger Preisaufschlag ist.<br />Beispiel: 10.00, 10%';
$GLOBALS['TL_LANG']['tl_iso_attribute']['custom'] = 'Eigene Reihenfolge';
$GLOBALS['TL_LANG']['tl_iso_attribute']['name_asc'] = 'Dateiname (aufsteigend)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['name_desc'] = 'Dateiname (absteigend)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['date_asc'] = 'Datum (aufsteigend)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['date_desc'] = 'Datum (absteigend)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['random'] = 'Zufällige Reihenfolge';
$GLOBALS['TL_LANG']['tl_iso_attribute']['new'][0] = 'Neues Attribut';
$GLOBALS['TL_LANG']['tl_iso_attribute']['new'][1] = 'Erstellt ein neues Attribut.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['edit'][0] = 'Attribut bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_attribute']['edit'][1] = 'Attribut mit der ID %s bearbeiten.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['copy'][0] = 'Attribut kopieren';
$GLOBALS['TL_LANG']['tl_iso_attribute']['copy'][1] = 'Attribut mit der ID %s kopieren.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['delete'][0] = 'Attribut löschen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['delete'][1] = 'Löscht das Attribut mit der ID %s. Die Datenbank-Spalte wird nicht gelöscht. Sie müssen die Datenbank manuell aktualisieren indem Sie das Installationstool oder die Erweiterungsverwaltung benutzen.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['show'][0] = 'Zeige Attributdetails';
$GLOBALS['TL_LANG']['tl_iso_attribute']['show'][1] = 'Details des Attributs mit der ID %s anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['deleteConfirm'] = 'Möchten Sie wirklich das Attribut mit der ID %s löschen? Die Datenbank-Spalte wird nicht gelöscht. Sie müssen die Datenbank manuell aktualisieren indem Sie das Installationstool oder die Erweiterungsverwaltung benutzen.';
$GLOBALS['TL_LANG']['tl_iso_attribute']['attribute_legend'] = 'Attribut-Name & -Typ';
$GLOBALS['TL_LANG']['tl_iso_attribute']['description_legend'] = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options_legend'] = 'Optionen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['config_legend'] = 'Attribut-Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_attribute']['search_filters_legend'] = 'Suche- & Filter-Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_attribute']['store_legend'] = 'Datei speichern';
