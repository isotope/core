<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2013 Isotope eCommerce Workgroup
 * 
 * Core translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/de/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][0][0] = '##document_number##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][0][1] = 'Eindeutige Nummer dieser Bestellung';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][1][0] = '##items##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][1][1] = 'Anzahl Artikel im Warenkorb';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][2][0] = '##products##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][2][1] = 'Anzahl Produkte im Warenkorb';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][3][0] = '##subTotal##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][3][1] = 'Subtotal der Bestellung';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][6][0] = '##grandTotal##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][6][1] = 'Gesamttotal der Bestellung';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][7][0] = '##cart_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][7][1] = 'Liste der bestellten Artikel im Text-Format';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][8][0] = '##cart_html##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][8][1] = 'Liste der bestellten Artikel im HTML-Format';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][9][0] = '##billing_address##<br />##billing_address_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][9][1] = 'Rechnungsadresse im HTML- oder Text-Format';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][10][0] = '##shipping_address##<br />##shipping_address_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][10][1] = 'Versandadresse im HTML- oder Text-Format';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][11][0] = '##shipping_method##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][11][1] = 'Bezeichnung der Versandmethode (wie im Backend eingegeben)';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][12][0] = '##shipping_note##<br />##shipping_note_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][12][1] = 'Hinweismeldung der gewählten Versandmethode (auch als Nur-Text abrufbar).';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][13][0] = '##payment_method##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][13][1] = 'Bezeichnung der Zahlungsmethode (wie im Backend eingegeben)';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][14][0] = '##payment_note##<br />##payment_note_text##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][14][1] = 'Hinweismeldung der gewählten Zahlungsart (auch als Nur-Text abrufbar).';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][15][0] = '##billing_firstname##<br />##billing_lastname##<br />...';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][15][1] = 'Einzelne Felder der Rechnungsadresse.';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][16][0] = '##shipping_firstname##<br />##shipping_lastname##<br />...';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][16][1] = 'Einzelne Felder der Versandadresse.';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][17][0] = '##form_...##';
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][17][1] = 'Verwenden Sie den Präfix "form_" und den Feldnamen, um Daten aus dem Formular der Bestellbestätigung zu verwenden.';
$GLOBALS['TL_LANG']['XPL']['isoReaderJumpTo'] = "\n<p class=\"tl_help_table\">\n    Anders als bei anderen Contao-Modulen wird der Benutzer nicht direkt auf die Detailseite weitergeleitet, wenn sich der Benutzer die Produktdetails ansieht. Wir haben eine neue Lösung, um das Problem, den Seitennamen leserlich auszugeben, und die zugehörige Detailseite zuzuordnen, gelöst.<br>\n    <br>\n    Die Detailseite (Alias) ist immer die selbe Seite, welche für eine Produktkategorie ausgewählt wurde. Es gibt 2 Möglichkeiten die Produktdetails anzuzeigen:<br>\n    <br>\n    <strong>Möglichkeit 1:</strong><br>\n    Sie wählen keine Detailseite in der Seitenstruktur aus. Sie setzen die Module für die Produktliste und die Produktdetails auf die selbe Seite. Stellen Sie beim Modul für die Produktliste ein, dass die Liste ausgeblendet werden soll, befindet sich ein Produktname (Alias) in der Adresse. Wird kein Produktname gefunden werden die Produktdetails automatisch ausgeblendet.<br>\n    <u>Vorteile:</u> Einfach einzustellen.<br>\n    <u>Nachteile:</u> Das Layout der Produktliste und der Details ist das selbe und Sie können nicht verschiedene Artikelinhalte für beide Fälle anlegen.<br>\n    <br>\n    <strong>Möglichkeit 2:</strong><br>\n    Setzen Sie eine Detailseite für jede Produktliste (Produktkategorie). <i>Beachten Sie, dass die Einstellungen für die Detailseite nicht vereerbt bzw. geerbt werden!</i> Fügen Sie das Modul für die Produktdetails wie gewohnt an.<br>\n    Isotope wird nun diese Seite verwenden wenn ein ein Produktname (Alias) in der Adresse gefunden wird.<br>\n    <u>Vorteile:</u> Sie können verschiedene Layouts sowie Seiten- und Artikelinhalte für die Produktliste und die Detailseite anlegen. <br>\n    <u>Nachteile:</u> Sie MÜSSEN eine Detailseite für jede Produktliste definieren. Die Einstellung wird NICHT VEREERBT.\n</p>";
$GLOBALS['TL_LANG']['XPL']['mediaManager'] = '<p class="tl_help_table">Wenn Sie ein neues Bild hochladen möchten, wählen Sie die Datei aus und speichern Sie das Produkt. Nach dem Sie das Bild erfolgreich hochgeladen haben, wird ein Vorschaubild angezeigt neben dem Sie den Alternativtext und eine Beschreibung eingeben können. Wenn Sie mehrere Bilder nutzen, können Sie ihre Reihenfolge durch Klicken auf die Pfeile rechts verändern. Das oberste Bild wird als Hauptbild des Produktes genutzt.';
