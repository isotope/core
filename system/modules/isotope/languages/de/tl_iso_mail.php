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
 * @author     Oliver Hoff <oliver@hoff.com>
 * @author     Stefan Preiss <stefan@preiss-at-work.de>
 * @author     Nina Gerling <gerling@ena-webstudio.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_mail']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_mail']['name'][1] = 'Bitte geben Sie einen Namen ein. Dieser wir lediglich als Referenz im System verwendet.';
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'][0] = 'Absendername';
$GLOBALS['TL_LANG']['tl_iso_mail']['senderName'][1] = 'Geben Sie den Namen des Absenders ein.';
$GLOBALS['TL_LANG']['tl_iso_mail']['sender'][0] = 'Absenderadresse';
$GLOBALS['TL_LANG']['tl_iso_mail']['sender'][1] = 'Geben Sie die E-Mail Adresse des Absenders ein. Der Empfänger wird bei Antworten an diese Adresse senden.';
$GLOBALS['TL_LANG']['tl_iso_mail']['cc'][0] = 'Kopie senden an';
$GLOBALS['TL_LANG']['tl_iso_mail']['cc'][1] = 'Empfänger welche eine Kopie der Nachricht erhalten sollen. Trennen Sie mehrere E-Mail Adressen mit einem Komma.';
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'][0] = 'Blinkkopie senden an';
$GLOBALS['TL_LANG']['tl_iso_mail']['bcc'][1] = 'Empfänger welche eine Blindkopie der Nachricht erhalten sollen. Trennen Sie mehrere E-Mail Adressen mit einem Komma.';
$GLOBALS['TL_LANG']['tl_iso_mail']['template'][0] = 'E-Mail-Template';
$GLOBALS['TL_LANG']['tl_iso_mail']['template'][1] = 'Hier können Sie das E-Mail-Template für HTML-Inhalte auswählen.';
$GLOBALS['TL_LANG']['tl_iso_mail']['attachDocument'][0] = 'Ein Bestelldokument anhängen';
$GLOBALS['TL_LANG']['tl_iso_mail']['attachDocument'][1] = 'Ermöglicht es Ihnen ein ergänzendes Dokument als PDF-Anhang für diese E-Mail zu generieren.';
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTemplate'][0] = 'Dokumenten-Template';
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTemplate'][1] = 'Wählen Sie ein Dokumenten-Template um damit das Standardauswahl-Template zu überschreiben.';
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTitle'][0] = 'Dokumententitel';
$GLOBALS['TL_LANG']['tl_iso_mail']['documentTitle'][1] = 'Bitte geben Sie einen Titel für das angehängte Dokument an.';
$GLOBALS['TL_LANG']['tl_iso_mail']['source'][0] = 'Quelldateien';
$GLOBALS['TL_LANG']['tl_iso_mail']['source'][1] = 'Bitte wählen Sie eine oder mehrere .imt-Dateien aus der Dateiverwaltung.';
$GLOBALS['TL_LANG']['tl_iso_mail']['new'][0] = 'Neue Vorlage';
$GLOBALS['TL_LANG']['tl_iso_mail']['new'][1] = 'Erstellen Sie eine neue E-Mail Vorlage';
$GLOBALS['TL_LANG']['tl_iso_mail']['edit'][0] = 'Vorlage bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_mail']['edit'][1] = 'E-Mail Vorlage ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_mail']['editheader'][0] = 'Vorlageeinstellungen bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_mail']['editheader'][1] = 'Einstellungen der E-Mail Vorlage ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_mail']['copy'][0] = 'Vorlage kopieren';
$GLOBALS['TL_LANG']['tl_iso_mail']['copy'][1] = 'E-Mail Vorlage ID %s kopieren';
$GLOBALS['TL_LANG']['tl_iso_mail']['delete'][0] = 'Vorlage löschen';
$GLOBALS['TL_LANG']['tl_iso_mail']['delete'][1] = 'E-Mail Vorlage ID %s löschen';
$GLOBALS['TL_LANG']['tl_iso_mail']['show'][0] = 'Vorlagendetails';
$GLOBALS['TL_LANG']['tl_iso_mail']['show'][1] = 'Details der E-Mail Vorlage ID %s anzeigen';
$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'][0] = 'Importieren';
$GLOBALS['TL_LANG']['tl_iso_mail']['importMail'][1] = 'E-Mail-Template importieren';
$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail'][0] = 'Exportieren';
$GLOBALS['TL_LANG']['tl_iso_mail']['exportMail'][1] = 'E-Mail-Template ID %s exportieren';
$GLOBALS['TL_LANG']['tl_iso_mail']['name_legend'] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_mail']['address_legend'] = 'Adresse';
$GLOBALS['TL_LANG']['tl_iso_mail']['expert_legend'] = 'Experten-Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_mail']['xml_error'] = 'Template " %s" ist defekt und kann nicht importiert werden.';
$GLOBALS['TL_LANG']['tl_iso_mail']['mail_imported'] = 'Template "%s" wurde importiert.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_mail']['document_legend']	= 'Bestelldokument';

