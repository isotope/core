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

$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name'][0] = 'Versandart-Name';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type'][0] = 'Versandart-Typ';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price'][0] = 'Preis';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'][0] = 'Versandart-Hinweise';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'][1] = 'Diese werden im Frontend in Verbindung mit der Versandoption angezeigt.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class'][0] = 'Steuerklasse';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'][0] = 'Beschriftung';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'][1] = 'Wird im Frontend in Verbindung mit der Versandoption angezeigt.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation'][0] = 'Pauschalpreis-Berechnung';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'][0] = 'Gewichtseinheit';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'][1] = 'Die Einheit in der Sie Gewichtsregeln angeben.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'][0] = 'Länder';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'][1] = 'Wählen Sie die Länder für die  diese Versandoption zur Verfügung steht. Wenn Sie kein Land wählen, gilt die Versandart für alle Länder.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'][0] = 'Staat/Regionen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'][1] = 'Wählen Sie die Staaten/Regionen für die diese Versandoption zur Verfügung steht. Wenn Sie nichts auswählen, gilt die Versandart für alle Staaten/Regionen.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total'][0] = 'Minimaler Bestellwert';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total'][0] = 'Maximaler Bestellwert';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'][0] = 'Produkttypen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'][1] = 'Sie können diese Versandart auf bestimmte Produkttypen einschränken. Wenn der Warenkorb einen Produkttyp enthält, den Sie hier nicht ausgewählt haben, steht das Versandmodul nicht zur Verfügung.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'][0] = 'Versandzuschlag';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'][1] = 'Bitte geben Sie einen Preisaufschlag (z. B. einen Treibstoffzuschlag auf alle Bestellungen) für diese Versandotion an, falls es einen geben soll.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'][0] = 'Mitgliedergruppen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'][1] = 'Schränken Sie die Versandoptionen auf bestimmte Mitgliedergruppen ein.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'][0] = 'Modul schützen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'][1] = 'Modul nur für bestimmte Mitgliedergruppen anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'][0] = 'Nur für Gäste anzeigen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'][1] = 'Modul für eingeloggte Mitglieder nicht anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'][0] = 'Aktiv';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'][1] = 'Wählen Sie ob dieses Modul für den Shop verfügbar ist.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'][0] = 'UPS XML/HTML Zugangsschlüssel';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'][1] = 'Dies ist ein spezieller alphanumerischer Schlüssen (access key) der von UPS bereitgestellt wird, sobald Sie sich für ein UPS-Konto angemeldet und Zugriff auf die UPS Online Tools API haben.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'][0] = 'UPS-Nutzername';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'][1] = 'Dies ist der UPS-Konto Nutzername den Sie bei der Anmeldung auf der UPS-Website auswählen.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'][0] = 'UPS-Passwort';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'][1] = 'Dies ist das UPS-Konto Passwort, das Sie bei der Anmeldung auf der UPS-Website auswählen.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'][0] = 'UPS-Servicetyp';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'][1] = 'Wählen Sie einen UPS-Servicetyp für das Angebot.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'][0] = 'UPS-Servicetyp';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'][1] = 'Wählen Sie einen UPS-Servicetyp für das Angebot.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'][0] = 'UPS-Nutzername';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'][1] = 'Dies ist der UPS-Konto Nutzername den Sie bei der Anmeldung auf der UPS-Website auswählen.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['title_legend'] = 'Titel und Typ';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note_legend'] = 'Versandhinweis';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['config_legend'] = 'Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_legend'] = 'UPS API-Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_legend'] = 'USPS API-Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price_legend'] = 'Preis-Grenzwert und Steuerklassen-Anwendbarkeit';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['expert_legend'] = 'Experteneinstellungen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled_legend'] = 'Einstellungen aktivieren';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'][0] = 'Neue Versandart';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'][1] = 'Eine neue Versandart erstellen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'][0] = 'Versandart bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'][1] = 'Versandart ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'][0] = 'Versandart duplizieren';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'][1] = 'Versandart ID %s duplizieren';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'][0] = 'Versandart löschen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'][1] = 'Versandart ID %s löschen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'][0] = 'Versandart-Details';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'][1] = 'Details der Versandart ID %s anzeigen';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates'][0] = 'Regeln bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates'][1] = 'Versandraten bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat'] = 'Pauschal';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct'] = 'Pro Produkt';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem'] = 'Pro Stück';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['01'] = 'Next Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['02'] = '2nd Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['03'] = 'UPS Ground';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['07'] = 'Worldwide Express';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['08'] = 'Worldwide Expedited';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['11'] = 'International Standard';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['12'] = '3 Day Select';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['13'] = 'Next Day Air Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['14'] = 'Next Day Air Early AM';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['54'] = 'Worldwide Express Plus';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['65'] = 'International Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PARCEL'] = 'USPS Parcel Post';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY'] = 'USPS Priority Mail (2-3 Tage Durchschnitt)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS'] = 'USPS Express Mail (Über Nacht garantiert)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['FIRST CLASS'] = 'USPS First Class';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY COMMERCIAL'] = 'USPS Priority Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS COMMERCIAL'] = 'USPS Express Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH'] = 'USPS Express Sundays & Holidays';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH COMMERCIAL'] = 'USPS Express Sundays & Holidays Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP'] = 'USPS Express Hold For Pickup';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP COMMERCIAL'] = 'USPS Express Hold For Pickup Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['BPM'] = 'USPS Bound Printed Matter';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['MEDIA'] = 'USPS Media Mail';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['LIBRARY'] = 'USPS Library Mail';

