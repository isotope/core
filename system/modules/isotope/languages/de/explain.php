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

$GLOBALS['TL_LANG']['XPL']['isoReaderJumpTo'] = "\n<p class=\"tl_help_table\">\n    Anders als bei anderen Contao-Modulen wird der Benutzer nicht direkt auf die Detailseite weitergeleitet, wenn er sich die Produktdetails ansieht. Wir haben eine neue Lösung für das Problem, den Seitennamen leserlich auszugeben und die zugehörige Detailseite zuzuordnen.<br>\n    <br>\n    Die Detailseite (Alias) ist immer die selbe Seite, welche für eine Produktkategorie ausgewählt wurde. Es gibt 2 Möglichkeiten die Produktdetails anzuzeigen:<br>\n    <br>\n    <strong>Möglichkeit 1:</strong><br>\n    Sie wählen keine Detailseite in der Seitenstruktur aus. Sie setzen die Module für die Produktliste und die Produktdetails auf die selbe Seite. Beim Modul für die Produktliste stellen Sie ein, dass die Liste ausgeblendet werden soll, wenn sich ein Produktname (Alias) in der Adresse befindet. Wird kein Produktname gefunden, werden die Produktdetails automatisch ausgeblendet.<br>\n    <u>Vorteile:</u> Einfach einzustellen.<br>\n    <u>Nachteile:</u> Das Layout der Produktliste und der Details ist das selbe und Sie können nicht verschiedene Artikelinhalte für beide Fälle anlegen.<br>\n    <br>\n    <strong>Möglichkeit 2:</strong><br>\n    Setzen Sie eine Detailseite für jede Produktliste (Produktkategorie). <i>Beachten Sie, dass die Einstellungen für die Detailseite nicht vereerbt bzw. geerbt werden!</i> Fügen Sie das Modul für die Produktdetails wie gewohnt an.<br>\n    Isotope wird nun diese Seite verwenden, wenn ein ein Produktname (Alias) in der Adresse gefunden wird.<br>\n    <u>Vorteile:</u> Sie können verschiedene Layouts sowie Seiten- und Artikelinhalte für die Produktliste und die Detailseite anlegen. <br>\n    <u>Nachteile:</u> Sie MÜSSEN eine Detailseite für jede Produktliste definieren. Die Einstellung wird NICHT VEREERBT.\n</p>";
$GLOBALS['TL_LANG']['XPL']['mediaManager'] = '<p class="tl_help_table">Wenn Sie ein neues Bild hochladen möchten, wählen Sie die Datei aus und speichern Sie das Produkt. Nach dem Sie das Bild erfolgreich hochgeladen haben, wird ein Vorschaubild angezeigt neben dem Sie den Alternativtext und eine Beschreibung eingeben können. Wenn Sie mehrere Bilder nutzen, können Sie ihre Reihenfolge durch Klicken auf die Pfeile rechts verändern. Das oberste Bild wird als Hauptbild des Produktes genutzt.';
$GLOBALS['TL_LANG']['XPL']['noVariantAttributes'] = 'Um Produktvarianten mit Isotope eCommerce nutzen zu können, müssen Sie erst Attribute konfigurieren.<br>Bitte beachten Sie <a href="http://isotopeecommerce.org/en/manual.html" target="_blank" style="text-decoration:underline">das Handbuch</a> für weitere Infos zur Attribut-Erstellung';
$GLOBALS['TL_LANG']['XPL']['elevatezoom_position'] = 'Die Fensterposition kann über die Basis-Position sowie einem horizontalen und vertikalen Abstand definiert werden. Genauere Infos unter: <a href="http://www.elevateweb.co.uk/image-zoom/examples#window-position" target="_blank">http://www.elevateweb.co.uk/image-zoom/examples#window-position</a><br><br><img src="system/modules/isotope/assets/images/elevatezoom-positions.png" alt="">';
