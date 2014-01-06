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
 * @link https://www.transifex.com/projects/i/isotope/language/nl/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['XPL']['isoReaderJumpTo'] = "\n<p class=\"tl_help_table\">\nAnders dan in andere Contao modules wordt een bezoeker niet naar een aparte detailpagina doorgestuurd als deze productgegevens bekijkt. Om problemen met nette aliassen op te lossen en om te weten welke de pagina met productgegevens is hebben we een nieuwe oplossing bedacht.<br>\n<br>\nDe pagina met productgegevens is altijd de pagina van de categorie waarin het product zich bevindt. Er zijn twee manieren om het product weer te geven:<br>\n<br>\n<strong>Optie 1:</strong><br>\nStel geen productgegevenspagina in in de paginastructuur. Plaats de productlijst- en productgegevensmodule op dezelfde pagina. Geef in de productlijstmodule aan dat deze verborgen moet zijn als er een productalias (in de URL) wordt gevonden, hiervoor is er een keuzevakje in de instellingen van de betreffende module. De productgegevens module is al automatisch onzichtbaar als er geen product getoond hoeft te worden.<br>\n<u>Voordeel:</u> Makkelijk in te stellen<br>\n<u>Nadeel:</u> De lay-out van de productgegevens en -lijst zijn gelijk en er kan geen afwijkende inhoud aan de pagina toegevoegd worden voor een van beide.<br>\n<br>\n<strong>Optie 2:</strong><br>\nStel een standaard productgegevens pagina in voor iedere productlijst pagina (productcategorie) in de paginastructuur. <i>Let op dat deze instelling niet over wordt genomen door dieper liggende pagina's!</i> Voeg de productgegevensmodule toe zoals gebruikelijk.<br>\nIsotope gebruikt nu deze pagina om productgegevens weer te geven als een productalias in de URL wordt gevonden. De alias staat wel nog op de pagina van de productlijst.<br>\n<u>Voordeel:</u> Het is mogelijk een afwijkende lay-out en overige inhoud te hebben voor productlezer- en productlijstpagina's (bijv. afwijkende kolommen).<br>\n<u>Nadeel:</u> Er MOET voor iedere productlezerpagina ingesteld worden op iedere categoriepagina. De instelling wordt niet geërfd.\n</p>";
$GLOBALS['TL_LANG']['XPL']['mediaManager'] = '<p class="tl_help_table">Om een nieuwe afbeelding te uploaden het bestand kiezen en product opslaan. Als het product succesvol opgeslagen is wordt de afbeelding getoond en dan kunt u een \'alt\' tekst en beschrijving opgeven. Indien er meerdere afbeeldingen zijn kan u de volgorde wijzigen door op de pijlen rechts van de afbeelding te klikken. De bovenste afbeelding is de hoofd-afbeelding van elk product.</p>';
