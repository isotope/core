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

$GLOBALS['TL_LANG']['tl_iso_rules']['type'][0] = 'Typ';
$GLOBALS['TL_LANG']['tl_iso_rules']['type'][1] = 'Bitte wählen Sie den Regeltyp.';
$GLOBALS['TL_LANG']['tl_iso_rules']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_rules']['name'][1] = 'Bitte geben Sie einen Namen für diese Regel an.';
$GLOBALS['TL_LANG']['tl_iso_rules']['label'][0] = 'Bezeichnung';
$GLOBALS['TL_LANG']['tl_iso_rules']['label'][1] = 'Diese Bezeichnung wird im Warenkorb angezeigt. Wenn Sie keine Bezeichnung angeben, wird stattdessen der Name genutzt.';
$GLOBALS['TL_LANG']['tl_iso_rules']['discount'][0] = 'Ermäßigung';
$GLOBALS['TL_LANG']['tl_iso_rules']['discount'][1] = 'Gültige Werte sind Dezimalzahlen oder ganze Zahlen, Minus einem numerischen Wert oder Minus einer Prozentangabe';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo'][0] = 'Ermäßigung hinzufügen zu';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo'][1] = 'Wählen Sie wie die Ermäßigung hinzugefügt werden soll';
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode'][0] = 'Gutscheincode freischalten';
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode'][1] = 'Festlegen, dass ein Code eingegeben werden muss um diese Regeln als Gutschein zu aktivieren.';
$GLOBALS['TL_LANG']['tl_iso_rules']['code'][0] = 'Regel (Gutschein) Code';
$GLOBALS['TL_LANG']['tl_iso_rules']['code'][1] = 'Bitte geben Sie einen Code ein, mit dem ein Kunde diese Regel als Gutschein aktivieren kann.';
$GLOBALS['TL_LANG']['tl_iso_rules']['minSubtotal'][0] = 'Geringstes Subtotal';
$GLOBALS['TL_LANG']['tl_iso_rules']['minSubtotal'][1] = 'Bitte geben Sie das mindeste Warenkorb-Subtotal ein, ab welchem diese Regel angewendet werden soll.';
$GLOBALS['TL_LANG']['tl_iso_rules']['maxSubtotal'][0] = 'Höchstes Subtotal';
$GLOBALS['TL_LANG']['tl_iso_rules']['maxSubtotal'][1] = 'Bitte geben Sie das maximale Warenkorb-Subtotal ein, bis zu welchem diese Regel angewendet werden soll.';
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity'][0] = 'Geringste Produktanzahl';
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity'][1] = 'Bitte legen Sie fest, wie oft das Produkt mindestens gewählt sein muss, damit sich die Regel darauf auswirkt.';
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity'][0] = 'Höchste Produktanzahl';
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity'][1] = 'Bitte legen Sie fest, wie oft das Produkt höchstens gewählt sein darf, damit sich die Regel darauf auswirkt.';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember'][0] = 'Benutzungen pro Kunde';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember'][1] = 'Dies wird genutzt um zu sehen ob die Regel bereits eingelöst wurde. Wenn es auf 0 eingestellt wird, kann sie von jedem Kunden beliebig oft eingesetzt werden,';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig'][0] = 'Benutzungen pro Shop-Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig'][1] = 'Dies wird genutzt um zu sehen ob die Regel bereits eingelöst wurde. Wenn es auf 0 eingestellt wird, kann sie in jeder Shop-Konfiguration beliebig oft eingesetzt werden,';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode'][0] = 'Mengen-Berechnungsmodus';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode'][1] = 'Wählen Sie einen Berechnungsmodus für die minimale/maximale Menge.';
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate'][0] = 'Startdatum';
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate'][1] = 'Falls gewollt, geben Sie hier an ab welchem Datum diese Regel zur Verfügung steht.';
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate'][0] = 'Enddatum';
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate'][1] = 'Falls gewollt, geben Sie hier ab welchem Datum diese Regel nicht mehr zur Verfügung steht.';
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime'][0] = 'Startzeit';
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime'][1] = 'Falls gewollt, geben Sie hier an ab welcher Uhrzeit diese Regel zur Verfügung steht.';
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime'][0] = 'Endzeit';
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime'][1] = 'Falls gewollt, geben Sie hier ein ab welcher Uhrzeit diese Regel nicht mehr zur Verfügung steht.';
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions'][0] = 'Einschränkungen der Shop-Konfiguration';
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions'][1] = 'Schränken Sie diese Regel auf bestimmte Shop-Konfigurationen ein';
$GLOBALS['TL_LANG']['tl_iso_rules']['configs'][0] = 'Shop-Konfigurationen';
$GLOBALS['TL_LANG']['tl_iso_rules']['configs'][1] = 'Wählen Sie auf welche Konfigurationen diese Regel beschränkt ist.';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions'][0] = 'Mitglieder-Einschränkungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions'][1] = 'Schränken Sie diese Regel auf bestimmte Gruppen oder Mitglieder ein.';
$GLOBALS['TL_LANG']['tl_iso_rules']['members'][0] = 'Mitglieder';
$GLOBALS['TL_LANG']['tl_iso_rules']['members'][1] = 'Wählen Sie auf welche Mitglieder diese Regel beschränkt ist.';
$GLOBALS['TL_LANG']['tl_iso_rules']['groups'][0] = 'Gruppen';
$GLOBALS['TL_LANG']['tl_iso_rules']['groups'][1] = 'Wählen Sie auf welche Gruppen diese Regel beschränkt ist.';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions'][0] = 'Produkt-Einschränkungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions'][1] = 'Schränken Sie diese Regel auf bestimmte Produkttypen, Kategorien oder individuelle Produkte ein.';
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes'][0] = 'Produkttypen';
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes'][1] = 'Wählen Sie auf welche Produkttypen diese Regel beschränkt ist. Falls Sie keine wählen, gilt sie für alle.';
$GLOBALS['TL_LANG']['tl_iso_rules']['products'][0] = 'Produkte';
$GLOBALS['TL_LANG']['tl_iso_rules']['products'][1] = 'Wählen Sie auf welche Produkte diese Regel beschränkt ist. Falls Sie keine wählen, gilt sie für alle.';
$GLOBALS['TL_LANG']['tl_iso_rules']['pages'][0] = 'Kategorien';
$GLOBALS['TL_LANG']['tl_iso_rules']['pages'][1] = 'Wählen Sie auf welche Kategorien diese Regel beschränkt ist. Falls Sie keine wählen, gilt sie für alle.';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled'][0] = 'Aktiv';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled'][1] = 'Bitte wählen Sie ob diese Regel derzeit aktiv ist oder nicht.';
$GLOBALS['TL_LANG']['tl_iso_rules']['basic_legend'] = 'Basis-Regeleinstellungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['coupon_legend'] = 'Geschenkgutschein';
$GLOBALS['TL_LANG']['tl_iso_rules']['limit_legend'] = 'Nutzungen einschränken';
$GLOBALS['TL_LANG']['tl_iso_rules']['datim_legend'] = 'Datum & Zeit Einschränkungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['advanced_legend'] = 'Erweiterte Einschränkungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled_legend'] = 'Verfügbarkeit';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['product'] = 'Produkt';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['cart'] = 'Warenkorb';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['none'] = 'Keine Einschränkungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['guests'] = 'Nur für Gäste';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['groups'] = 'Bestimmte Gruppen';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['members'] = 'Bestimmte Mitglieder';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['none'] = 'Keine Einschränkungen';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['producttypes'] = 'Produkttypen';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['pages'] = 'Bestimmte Kategorien';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['products'] = 'Bestimmte Produkte';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['all'] = 'Alle anderen Regeln ausschließen';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['none'] = 'Keine Regelausnahmen';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['rules'] = 'Bestimmte Regeln ausschließen';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['products'] = 'für jedes Produkt';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['items'] = 'für jede Einheit eines Produktes';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['subtotal'] = 'für den Warenkorb-Gesamtwert';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['product_quantity'] = 'Menge der Produkte im Warenkorb';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_products'] = 'Gesamte der Produkte im Warenkorb';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_items'] = 'Gesamtmenge im Warenkorb';
$GLOBALS['TL_LANG']['tl_iso_rules']['new'][0] = 'Regel hinzufügen';
$GLOBALS['TL_LANG']['tl_iso_rules']['new'][1] = 'Neue Regeln erstellen';
$GLOBALS['TL_LANG']['tl_iso_rules']['edit'][0] = 'Regel bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_rules']['edit'][1] = 'Bearbeite die Regel mit der ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['copy'][0] = 'Regel duplizieren';
$GLOBALS['TL_LANG']['tl_iso_rules']['copy'][1] = 'Dupliziere die Regel mit der ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['delete'][0] = 'Regel löschen';
$GLOBALS['TL_LANG']['tl_iso_rules']['delete'][1] = 'Lösche die Regel mit der ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['show'][0] = 'Regeldetails';
$GLOBALS['TL_LANG']['tl_iso_rules']['show'][1] = 'Zeige die Details der Regel mit der ID %s';

