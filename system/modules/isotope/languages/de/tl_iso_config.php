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

$GLOBALS['TL_LANG']['tl_iso_config']['name'][0] = 'Name der Shop-Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_config']['name'][1] = 'Geben Sie einen eindeutigen Namen ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['label'][0] = 'Bezeichnung';
$GLOBALS['TL_LANG']['tl_iso_config']['label'][1] = 'Die Bezeichnung wird im Frontend z.B. für den Konfigurationswechsler verwendet.';
$GLOBALS['TL_LANG']['tl_iso_config']['fallback'][0] = 'Standard-Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_config']['fallback'][1] = 'Verwendet dies als Standardkonfiguration für die Anzeige im Backend.';
$GLOBALS['TL_LANG']['tl_iso_config']['store_id'][0] = 'Shop-ID';
$GLOBALS['TL_LANG']['tl_iso_config']['store_id'][1] = 'Nutzen Sie verschiedene Shop-IDs um ein Set von Shop-Konfigurationen zu erstellen. Der Warenkorb und die Adressen eines Nutzers werden zwischen den identischen Shop-IDs gemeinsam genutzt.';
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder'][0] = 'Platzhalter-Bild für Produkte';
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder'][1] = 'Dieses Bild wird verwendet wenn für ein Produkt kein Bild vorhanden ist.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'][0] = 'Berechnungsfaktor';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'][1] = 'Standardmässig auf 1. Wählen Sie einen anderen Faktor für eine Umrechnung.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'][0] = 'Berechnungsmodus';
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'][1] = 'Preis dividieren oder multiplieren mit dem Faktor.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'][0] = 'Rundungspräzision';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'][1] = 'Wählen Sie die Dezimal-Präzision. Sie sollten einen Wert zwischen 0 und 2 verwenden, um Kompatibilität mit allen Zahlungsmodulen zu gewährleisten.';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'][0] = 'Rundungszuschlag';
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'][1] = 'Einige Währungen (z.B. Schweizer Franken) erlauben keine 0.01 Beträge.';
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'][0] = 'Mindestbestellwert';
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'][1] = 'Diese Gesamtsumme muss im Warenkorb mindest erreicht werden um die Bestellung durchzuführen. Geben Sie eine 0 ein um diese Funktion zu deaktivieren.';
$GLOBALS['TL_LANG']['tl_iso_config']['currency'][0] = 'Währung';
$GLOBALS['TL_LANG']['tl_iso_config']['currency'][1] = 'Wählen Sie eine Währung für diesen Shop.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'][0] = 'Währungssymbol verwenden';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'][1] = 'Das Währungssymbol verwenden ($, €) falls möglich.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'][0] = 'Leerzeichen einfügen';
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'][1] = 'Zwischen den Preis und das Währungssymbol ein Leerzeichen einfügen.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'][0] = 'Position der Währungsbezeichung/-symbol';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'][1] = 'Wählen Sie ob die Währung links oder rechts des Preises angezeigt werden soll.';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'][0] = 'Währungsformat';
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'][1] = 'Wählen Sie eine Formatierung für Preise.';
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'][0] = 'Rechnungslogo';
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'][1] = 'Wählen Sie ein Logo das auf den Rechnungen dieses Shops angezeigt werden soll.';
$GLOBALS['TL_LANG']['tl_iso_config']['firstname'][0] = 'Vorname';
$GLOBALS['TL_LANG']['tl_iso_config']['firstname'][1] = 'Bitte geben Sie den Vornamen ein (falls anwendbar).';
$GLOBALS['TL_LANG']['tl_iso_config']['lastname'][0] = 'Nachname';
$GLOBALS['TL_LANG']['tl_iso_config']['lastname'][1] = 'Bitte geben Sie den Nachnamenen (falls anwendbar).';
$GLOBALS['TL_LANG']['tl_iso_config']['company'][0] = 'Unternehmen';
$GLOBALS['TL_LANG']['tl_iso_config']['company'][1] = 'Sie können hier einen Unternehmensnamen angeben (falls anwendbar).';
$GLOBALS['TL_LANG']['tl_iso_config']['street_1'][0] = 'Straße';
$GLOBALS['TL_LANG']['tl_iso_config']['street_1'][1] = 'Bitte geben Sie hier den Straßennamen sowie die Hausnummer an.';
$GLOBALS['TL_LANG']['tl_iso_config']['street_2'][0] = 'Straße 2';
$GLOBALS['TL_LANG']['tl_iso_config']['street_2'][1] = 'Geben Sie hier - falls benötigt - eine zweite Straßeninformation ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['street_3'][0] = 'Straße 3';
$GLOBALS['TL_LANG']['tl_iso_config']['street_3'][1] = 'Geben Sie hier - falls benötigt - eine dritte Straßeninformation ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['postal'][0] = 'Postleitzahl';
$GLOBALS['TL_LANG']['tl_iso_config']['postal'][1] = 'Bitte geben Sie die Postleitzahl ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['city'][0] = 'Ort';
$GLOBALS['TL_LANG']['tl_iso_config']['city'][1] = 'Bitte geben Sie den Namen des Ortes ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'][0] = 'Staat';
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'][1] = 'Bitte geben Sie den Namen des Staates ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['country'][0] = 'Land';
$GLOBALS['TL_LANG']['tl_iso_config']['country'][1] = 'Bitte wählen Sie das Land. Dies ist auch die Standardeinstellung für individuelle Versand-/Rechnungs-Adressen.';
$GLOBALS['TL_LANG']['tl_iso_config']['phone'][0] = 'Telefonnummer';
$GLOBALS['TL_LANG']['tl_iso_config']['phone'][1] = 'Bitte geben Sie die Telefonnummer ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['email'][0] = 'Versand E-Mail-Adresse';
$GLOBALS['TL_LANG']['tl_iso_config']['email'][1] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'][0] = 'Versand-Länder';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'][1] = 'Wählen Sie die Ländern die beim Bestellvorgang als Versand-Adressen erlaubt sind.';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'][0] = 'Versand-Adressfelder';
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'][1] = 'Wählen sie die Felder die für eine neue Versand-Adresse beim Bestellvorgang zur Verfügung stehen.';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'][0] = 'Rechnungs-Länder';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'][1] = 'Wählen Sie die Ländern die beim Bestellvorgang als Rechnungs-Adressen erlaubt sind.';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'][0] = 'Rechnungs-Adressfelder';
$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'][1] = 'Wählen sie die Felder die für eine neue Rechnungs-Adresse beim Bestellvorgang zur Verfügung stehen.';
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'][0] = 'Bestellnummer-Präfix';
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'][1] = 'Sie können für die automatisch hochzählende Bestellnummer einen Präxif (z. B. Fiskaljahr) hinzufügen.';
$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits'][0] = 'Bestellnummern-Länge';
$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits'][1] = 'Wählen sie die minimale Länge für die Bestellnummer (den Bestellnummer-Präfix nicht mitgezählt).';
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'][0] = 'Templates-Ordner';
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'][1] = 'Hier können Sie einen Templates-Ordner festlegen der vor allen anderen Template-Ordnern bevorzugt werden soll.';
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'][0] = 'Mitgliederländer einschränken';
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'][1] = 'Schränken Sie die Mitgliederländer (Registration, Persönliche Daten) auf die kombinierte Liste der Rechnungs- und Versand-Länder ein.';
$GLOBALS['TL_LANG']['tl_iso_config']['enableGoogleAnalytics'][0] = 'Google Analytics E-Commerce Tracking aktivieren';
$GLOBALS['TL_LANG']['tl_iso_config']['enableGoogleAnalytics'][1] = 'Google Analytics E-Commerce Tracking hinzufügen. Bitte beachten Sie, dass Sie das E-Commerce-Tracking auch in Ihrem Google Analytics-Konto aktivieren müssen.';
$GLOBALS['TL_LANG']['tl_iso_config']['gallery'][0] = 'Produkt-Bildergalerie';
$GLOBALS['TL_LANG']['tl_iso_config']['gallery'][1] = 'Es können verschiedene Bildergalerien einstellt werden um die Mediendateien angepasst anzuzeigen.';
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'][0] = 'Bildgrößen';
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'][1] = 'Sie können individuelle Bildgrößen für die Benutzung in Ihren Templates anlegen. Die Standardgrößen sind "gallery", "thumbnauk", "medium" und "large".';
$GLOBALS['TL_LANG']['tl_iso_config']['left'] = 'Links des Preises';
$GLOBALS['TL_LANG']['tl_iso_config']['right'] = 'Rechts des Preises';
$GLOBALS['TL_LANG']['tl_iso_config']['div'] = 'Dividieren';
$GLOBALS['TL_LANG']['tl_iso_config']['mul'] = 'Multiplizieren';
$GLOBALS['TL_LANG']['tl_iso_config']['tl'] = 'Oben Link';
$GLOBALS['TL_LANG']['tl_iso_config']['tc'] = 'Oben';
$GLOBALS['TL_LANG']['tl_iso_config']['tr'] = 'Oben Rechts';
$GLOBALS['TL_LANG']['tl_iso_config']['bl'] = 'Unten Links';
$GLOBALS['TL_LANG']['tl_iso_config']['bc'] = 'Unten';
$GLOBALS['TL_LANG']['tl_iso_config']['br'] = 'Unten Rechts';
$GLOBALS['TL_LANG']['tl_iso_config']['cc'] = 'Mittig';
$GLOBALS['TL_LANG']['tl_iso_config']['iwName'] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWidth'] = 'Breite';
$GLOBALS['TL_LANG']['tl_iso_config']['iwHeight'] = 'Höhe';
$GLOBALS['TL_LANG']['tl_iso_config']['iwMode'] = 'Modus';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark'] = 'Wasserzeichen-Bild';
$GLOBALS['TL_LANG']['tl_iso_config']['iwPosition'] = 'Position';
$GLOBALS['TL_LANG']['tl_iso_config']['fwEnabled'] = 'Feld aktivieren';
$GLOBALS['TL_LANG']['tl_iso_config']['fwLabel'] = 'Individuelle Beschriftung';
$GLOBALS['TL_LANG']['tl_iso_config']['fwMandatory'] = 'Pflichtfeld';
$GLOBALS['TL_LANG']['tl_iso_config']['new'][0] = 'Neue Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_config']['new'][1] = 'Eine neue Shop-Konfiguration erstellen.';
$GLOBALS['TL_LANG']['tl_iso_config']['edit'][0] = 'Konfiguration bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_config']['edit'][1] = 'Shop-Konfiguration ID %s bearbeiten.';
$GLOBALS['TL_LANG']['tl_iso_config']['copy'][0] = 'Konfiguration duplizieren';
$GLOBALS['TL_LANG']['tl_iso_config']['copy'][1] = 'Shop-Konfiguration ID %s duplizieren.';
$GLOBALS['TL_LANG']['tl_iso_config']['delete'][0] = 'Konfiguration löschen';
$GLOBALS['TL_LANG']['tl_iso_config']['delete'][1] = 'Shop-Konfiguration ID %s löschen.  Dies löscht nicht die zugeordneten Dateien sondern lediglich die Grundkonfiguration.';
$GLOBALS['TL_LANG']['tl_iso_config']['show'][0] = 'Konfigurationsdetails anzeigen';
$GLOBALS['TL_LANG']['tl_iso_config']['show'][1] = 'Details für Shop-Konfiguration ID %s anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_config']['name_legend'] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_config']['address_legend'] = 'Adresse';
$GLOBALS['TL_LANG']['tl_iso_config']['config_legend'] = 'Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_config']['price_legend'] = 'Preise';
$GLOBALS['TL_LANG']['tl_iso_config']['currency_legend'] = 'Währungs-Formattierung';
$GLOBALS['TL_LANG']['tl_iso_config']['invoice_legend'] = 'Rechnungen';
$GLOBALS['TL_LANG']['tl_iso_config']['images_legend'] = 'Bilder';

