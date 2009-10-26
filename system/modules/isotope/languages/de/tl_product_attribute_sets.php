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
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['tstamp'] = array('Letzte Änderung', '');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['name'] = array('Name des Artikeltyps', '');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['store_id'] = array('Shop-Konfiguration', 'Wählen Sie die Shop-Konfiguration welche für diesen Artikeltyp gültig ist.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['storeTable'] = array('Datenbank-Tabelle', '');

$GLOBALS['TL_LANG']['tl_product_attribute_sets']['addImage']     = array('Ein Bild hinzufügen', 'Wenn Sie diese Option aktivieren wird der Produktliste ein Bild hinzugefügt.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['singleSRC']    = array('Bilddatei', 'Wählen Sie ein Bild welche in der Produkttyp-Liste angezeigt wird.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['size']        = array('Bildbreite und -höhe', 'Geben Sie entweder die Bildbreite, die Bildhöhe oder beide Werte ein, um die Bildgröße anzupassen. Wenn Sie keine Angaben machen, wird das Bild in seiner Originalgröße angezeigt.');

$GLOBALS['TL_LANG']['tl_product_attribute_sets']['format'] = array('Titel formatieren', 'Geben Sie eine Formatierung für Artikel ein (optional).<br />Beispiele: <em>{{image_field::w=100&h=80}} {{title_field}}</em>: <em>{{checkbox_field::src=all.gif}} {{checkbox_field}}</em>');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['new']				= array('Neuer Artikeltyp', 'Einen neuen Artikeltyp erstellen');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['edit']			= array('Artikel verwalten', 'Artikel des Typs ID %s verwalten');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['copy']			= array('Artikeltyp duplizieren', 'Artikeltyp ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['delete']			= array('Artikeltyp löschen', 'Artikeltyp ID %s löschen');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['show']			= array('Artikeltypdetails', 'Details des Artikeltyps ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['editheader']		= array('Artikeltyp bearbeiten', 'Diesen Artikeltyp bearbeiten');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['attributes']		= array('Artikelmerkmale definieren', 'Merkmale für Artikeltyp ID %s definieren');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['regenerateDca']	= array('DCA neu generieren', 'DCA für alle Artikeltypen neu erstellen');

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemFormat'] = ' <span style="color:#b3b3b3;"><em>(%s %s)</em></span>';
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemSingle'] = 'Artikel';
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemPlural'] = 'Artikel';

