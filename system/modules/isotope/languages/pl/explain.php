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
 * @link https://www.transifex.com/projects/i/isotope/language/pl/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['XPL']['isoReaderJumpTo'] = "\n<p class=\"tl_help_table\">\n	W przeciwieństwie do innych modułów Contao, użytkownik nie może zostać przekierowany do strony czytnika podczas przeglądania szczegółów produktu. Aby rozwiązać problem ładnych aliasów i znania strony szczegółów produktu, wymyśliliśmy nowe rozwiązanie.<br>\n    <br>\n    Strona czytnika (alias) będzie zawsze tą samę stroną, którą wybrałeś jako kategorię dla produktu. Są dwie opcje wyświetlania szczegółów produktu:<br>\n    <br>\n    <strong>Opcja 1:</strong><br>\n    Nie ustawiaj strony czytnika w strukturze stron. Umieść listę i czytnik na tej samej stronie. Wskaż modułowi listy, aby się ukrył, jeśli znaleziony został alias produktu (checkbox w ustawieniach modułu). Czytnik będzie automatycznie niewidoczny, jeśli żaden czytnik nie został znaleziony.<br>\n    <u>Zaleta:</u> Proste w ustawieniu<br>\n    <u>Wada:</u> Układ strony czytnika oraz listy będą takie same i nie możesz mieć różnych artykułów dla obu przypadków.<br>\n    <br>\n    <strong>Opcja 2:</strong><br>\n    Ustaw stronę czytnika dla każdej strony listy (kategoria produktu) w strukturze stron. <i>Miej świaomość, że ustawienia czytnika nie są dziedziczone!<i> Dodaj czytnik modułu do strony, tak jak zwykle.<br>\n    Isotope użyje tej strony do generowanie serwisu, jeśli alias produktu został znaleziony w URL. Alias jednak będzie taki sam jak ze strony listy.<br>\n    <u>Zaleta:</u> Możesz mieć różną treść i układ strony (np. różne kolumny) dla strony czytnika i strony listy.<br>\n    <u>Wada:</u> Musisz ustawić stronę czytnika dla każdej strony listy (kategorii). To ustawienie NIE JEST DZIEDZICZONE.\n</p>";
$GLOBALS['TL_LANG']['XPL']['mediaManager'] = '<p class="tl_help_table">Aby wgrać nowy obrazek, wybierz plik i zapisz produkt. After successfully uploading, a preview of the image is displayed and next to it you can enter its alternative text and a description. For multiple pictures, you can click on the arrows to the right and change their order, the top image is used as the main image of each product.</p>';
