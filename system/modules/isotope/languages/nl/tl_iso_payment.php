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

$GLOBALS['TL_LANG']['tl_iso_payment']['name'][0] = 'Naam betaalmethode';
$GLOBALS['TL_LANG']['tl_iso_payment']['name'][1] = 'Vul een naam in voor deze betaalmethode. Deze wordt alleen in het backend weergegeven.';
$GLOBALS['TL_LANG']['tl_iso_payment']['label'][0] = 'Betaalmethode label';
$GLOBALS['TL_LANG']['tl_iso_payment']['label'][1] = 'Het label wordt weergegeven voor klanten tijdens het afrekenen.';
$GLOBALS['TL_LANG']['tl_iso_payment']['type'][0] = 'Soort betalingsaanbieder';
$GLOBALS['TL_LANG']['tl_iso_payment']['type'][1] = 'Kies een soort betalingsaanbieder';
$GLOBALS['TL_LANG']['tl_iso_payment']['note'][0] = 'Opmerking bij betaalmethode';
$GLOBALS['TL_LANG']['tl_iso_payment']['note'][1] = 'Deze opmerking kan meegestuurd worden in bevestigingse-mails (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'][0] = 'Status voor nieuwe bestellingen';
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'][1] = 'Kies een status voor nieuwe bestellingen.';
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'][0] = 'Minimum totaal';
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'][1] = 'Vul een getal groter dan nul in om deze betaalmethode te verbergen bij bestellingen met een lager totaalbedrag.';
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'][0] = 'Maximun totaal';
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'][1] = 'Vul een getal groter dan nul in om deze betaalmethode te verbergen bij bestellingen met een hoger totaalbedrag.';
$GLOBALS['TL_LANG']['tl_iso_payment']['countries'][0] = 'Beperk landen';
$GLOBALS['TL_LANG']['tl_iso_payment']['countries'][1] = 'Kies de landen waarvoor deze betaalmethode gebruikt wordt (gebaseerd op het factuuradres van de klant).';
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'][0] = 'Verzendmethoden';
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'][1] = 'U kunt deze betaalmethode beperken tot enkele verzendmethodes (bijv. alleen contant betalen bij afhalen).';
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'][0] = 'Productsoorten';
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'][1] = 'U kan deze betaalmethode beperken tot bepaalde product soorten. Als de winkelwagen een product soort bevat die niet geselecteerd is, is deze betaalmethode niet beschikbaar.';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_ids'][0] = 'Winkel configuraties';
$GLOBALS['TL_LANG']['tl_iso_payment']['price'][0] = 'Prijs';
$GLOBALS['TL_LANG']['tl_iso_payment']['price'][1] = 'Vul een prijs of percentage in (bijv. "10" of "10%").';
$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class'][0] = 'Belastingklasse';
$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class'][1] = 'Kies een belastingklasse voor deze prijs.';
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'][0] = 'Transactiesoort';
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'][1] = 'Kies of u meteen wilt doorverwijzen om de betaling te verichten, of de bestelling toestaan (en vasthouden) om later te betalen (bijv. bij verzending).';
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_account'][0] = 'PayPal account';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_pspid'][0] = 'PSPID';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_pspid'][1] = 'Het PSPID is uw unieke identificatie van de betaalmethode.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_http_method'][0] = 'HTTP methode';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_http_method'][1] = 'Type HTTP gebruikt voor data van en naar servers.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'][0] = 'Hashmethode';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'][1] = 'Hashing methode voor data van en naar servers.';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha1'] = 'SHA-1';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha256'] = 'SHA-256';
$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method']['sha512'] = 'SHA-512';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'][0] = 'Betaalmethode';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['creditcard'] = 'Creditcard';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['maestro'] = 'Debitcard';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword'][0] = 'Wachtwoord';
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_description'][0] = 'Omschrijving';
$GLOBALS['TL_LANG']['tl_iso_payment']['groups'][0] = 'Ledengroepen';
$GLOBALS['TL_LANG']['tl_iso_payment']['groups'][1] = 'Beperk deze betaalmethode tot bepaalde ledengroepen.';
$GLOBALS['TL_LANG']['tl_iso_payment']['guests'][0] = 'Alleen zichtbaar voor gasten';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled'][0] = 'Ingeschakeld';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['cc'] = 'Creditcard';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['dc'] = 'Debitcard';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['vor'] = 'Voorafbetaling';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['rec'] = 'Factuur';
$GLOBALS['TL_LANG']['tl_iso_payment']['type_legend'] = 'Naam &amp; type';
$GLOBALS['TL_LANG']['tl_iso_payment']['note_legend'] = 'Aanvullende berichten';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_legend'] = 'Algemene configuratie';
$GLOBALS['TL_LANG']['tl_iso_payment']['gateway_legend'] = 'Configuratie betalingsaanbieder';
$GLOBALS['TL_LANG']['tl_iso_payment']['price_legend'] = 'Prijs';
$GLOBALS['TL_LANG']['tl_iso_payment']['template_legend'] = 'Template';
$GLOBALS['TL_LANG']['tl_iso_payment']['expert_legend'] = 'Expert instellingen';
