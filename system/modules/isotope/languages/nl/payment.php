<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Paul Kegel <paul@artified.nl>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['MSC']['authorizedotnet']['1'] = 'Goedgekeurd';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet']['2'] = 'Afgekeurd';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet']['3'] = 'Fout';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet']['4'] = 'Wacht op goedkeuring';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['1']['1'] = 'Deze transactie is goedgekeurd.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['2'] = 'Deze transactie is afgekeurd.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['3'] = 'Deze transactie is afgekeurd.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['4'] = 'Deze transactie is afgekeurd. - De code van de bank geeft aan dat deze kaart ingenomen moet worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['5'] = 'Er moet een geldig bedrag worden ingevuld. - De ingevulde waarde is niet goedgekeurd als bedrag.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['6'] = 'Het creditcard nummer is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['7'] = 'De vervaldatum van de creditcard is ongeldig - het opgegeven formaat is fout.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['8'] = 'De creditcard is verlopen.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['9'] = 'De ABA code is ongeldig - de waarde in het x_bank_aba_code veld kan niet gevalideerd worden of is niet voor een geldige bank.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['10'] = 'Het rekeningnummer is ongeldig - De waarde in het x_bank_acct_num veld is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['11'] = 'De transactie is twee keer verzonden - Een transactie met hetzelfde bedrag en creditcard informatie is 2 minuten geleden ook al verstuurd.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['12'] = 'Een autorisatiecode is verplicht maar niet aanwezig - Een transactie waarbij x_auth_code verplicht is werd verstuurd zonder waarde voor het veld.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['13'] = 'Het API login ID van de winkelier is ongeldig of niet actief.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['15'] = 'Het transactie ID is ongeldig - Het transactie ID is niet-numeriek of niet aanwezig voor een transactie waarbij deze verplicht is (b.v. VOID, PRIOP_AUTH_CAPTURE en CREDIT).';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['16'] = 'De transactie is niet gevonden - Het transactie ID is goed maar de gateway heeft geen informatie over deze transactie.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['17'] = 'De winkelier accepteert dit type creditcard niet.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['18'] = 'ACH transacties worden door de winkelier niet geaccepteerd - De winkelier accepteert geen elektronische cheques.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['19'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['20'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['21'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['22'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['23'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['24'] = 'Het Nova bank nummer of terminal ID is fout. Neem contact op met uw service provider.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['25'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['26'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['27'] = 'De transactie resulteert in een AVS fout. Het opgegeven adres komt niet overeen met het factuuradres van de kaarthouder.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['28'] = 'De winkelier accepteert deze creditcard niet - De betalingsverwerker accepteert dit creditcard type niet voor deze winkelier.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['30'] = 'De configuratie bij de betalingsverwerker is ongeldig. Neem contact op met uw service provider.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['31'] = 'Het FDC ID of het terminal ID is ongeldig. Neem contact op met uw service provider. De account is niet goed geconfigureerd bij de betalingsverwerker.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['32'] = 'De "reason code" is gereserveerd of niet toegelaten voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['33'] = 'Een door de winkelier als verplicht opgegeven veld is niet ingevuld. Raadpleeg het "Form Settings"  deel in de "Merchant Integration Guide" voor meer informatie.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['34'] = 'De VITAL identificatienummers zijn incorrect. Neem contact op met de service provider. Het account van de winkelier is verkeerd geconfigureerd bij de betalingsverwerker.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['35'] = 'Er is een fout opgetreden tijdens de verwerking. Neem contact op met uw service provider. Het account van de winkelier is niet goed geconfigureerd bij de betalingsverwerker.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['36'] = 'De autorisatie is goedgekeurd maar de afhandeling faalt.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['37'] = 'Het creditcard nummer is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['38'] = 'De Global Payment System identificatienummers zijn onjuist. Neem contact op met uw service provider - Het account van de winkelier is niet juist geconfigureerd bij de betalingsverwerker.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['40'] = 'Deze transactie moet geëncrypt zijn.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['41'] = 'Deze transactie is afgekeurd. Alleen winkeliers die de Fraudscreen.Net service gebruiken krijgen deze melding. Deze afkeuring treed op als de fraud score hoger is dan de limiet die de winkelier heeft ingesteld.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['43'] = 'Het account van de winkelier is incorrect geconfigureerd bij de betalingsverwerker.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['44'] = 'Deze transactie is afgekeurd - De kaart code die bij deze transactie is gebruikt klopt niet met de creditcard.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['45'] = 'Deze transactie is afgekeurd - Deze fout kan ontstaan als de door de winkelier opgezette creteria voor AVS en kaart code filters kloppen.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['46'] = 'Uw sessie is verlopen of bestaat niet. U moet opnieuw inloggen om verder te gaan.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['47'] = 'Het gevraagde bedrag mag niet hoger zijn dan het oorspronkelijke bedrag - Deze fout treedt op als de winkelier probeert een bedrag af te schrijven dat groter is dan het oorspronkelijk gereserveerde bedrag.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['48'] = 'De betalingsverwerker accepteert geen gedeeltelijke terugboekingen - De winkelier probeert een betaling te doen die lager is dan het oorspronkelijk gereserveerde bedrag.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['49'] = 'Een transactiebedrag hoger dan $[amount] wordt niet geaccepteerd - Het gevraagde transactiebedrag was groter dan het toegestane maximum.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['50'] = 'Deze transactie wacht op afhandeling en kan niet teruggestort worden - Credits of terugboekingen zijn alleen mogelijk voor afgesloten transacties. De gevraagde transactie is nog niet afgesloten en een terugboeking is nog niet mogelijk.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['51'] = 'De som van alle credit bedragen van deze transactie is groter dan het oorspronkelijke bedrag.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['52'] = 'Deze transactie is goedgekeurd maar de klant kan niet geïnformeerd worden; de transactie zal niet plaat vinden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['53'] = 'Het transactie-type is ongeldig voor ACH transacties - Als x_method = ECHECK, x_type niet op CAPTURE_ONLY gezet kan worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['54'] = 'De gevraagde transactie voldoet niet aan de creteria voor het doen van een terugboeking.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['55'] = 'Het totaal van credit bedragen voor de gevraagde transactie overschreidt het oorspronkelijke debet bedrag - De transactie wordt afgekeurd als de som van alle credit bedragen groter is dan het oorspronkelijke debet bedrag.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['56'] = 'De winkelier accepteert alleen ACH transacties - De winkelier accepteert alleen eCheck.net transacties en accepteert geen creditcards.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['57'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['58'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['59'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['60'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['61'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['62'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['63'] = 'Er is een fout opgetreden tijdens de verwerking. Probeer het over 5 minuten nog eens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['65'] = 'Deze transactie is afgekeurd - De transactie is afgekeurd omdat de winkelier ervoor kiest transacties met bepaalde fouten in de kaart code niet te accepteren.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['66'] = 'De transactie wordt niet geaccepteerd voor verwerking - De transactie voldoet niet aan de veiligheidseisen van de gateway.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['68'] = 'De versie parameter is ongeldig - De waarde in x_version is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['69'] = 'Het transactietype is ongeldig - De waarde in x_type is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['70'] = 'De transactie methode is ongeldig - De waarde in x_method is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['71'] = 'De bankcode van de rekening is ongeldig - De waarde in x_bank_acct_type is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['72'] = 'De authorisatiecode is ongeldig - De waarde in x_auth_code is langer dan 6 tekens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['73'] = 'De geboortedatum op het rijbewijs is ongeldig. Het formaat van de versonden x_drivers_license_dob is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['74'] = 'Het bedrag aan rechten is ongeldig - De waarde in x_duty heeft een verkeerd formaat.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['75'] = 'Het bedrag voor verzendkosten is ongeldig - De waarde in x_freight heeft een verkeerd formaat.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['76'] = 'Het bedrag voor belasting is ongeldig - De waarde in x_tax heeft een verkeerd formaat.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['77'] = 'Het SSN of belasting nummer is ongeldig. De waarde in x_customer_tax_id kan niet gevalideerd worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['78'] = 'De kaartcode (CCV2/CVC2/CID) is ongeldig - De waarde in x_card_code kan niet gevalideerd worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['79'] = 'Het rijbewijsnummer is ongeldig - De waarde in x_drivers_license_num kan niet gevalideerd worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['80'] = 'De provincie/staat op het rijbewijs is ongeldig - De waarde in x_drivers_license_state kan niet gevalideerd worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['81'] = 'Het gevraagde formulier-type is ongeldig - De winkelier vraagt een integratiecode die niet compatibel is met de AIM API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['82'] = 'Scripts zijn niet langen toegestaan in versie 2.5 - Het systeem ondersteunt versie 2.5 niet meer; verzoeken met scripts kunnen niet verzonden worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['83'] = 'Het gevraagde script is of ongeldig of wordt niet meer gebruikt - Het systeem ondersteunt versie 2.5 niet meer; verzoeken met scripts kunnen niet verzonden worden.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['84'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['85'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['86'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['87'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['88'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['89'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['90'] = 'Deze reden is gereserveerd of niet toepasbaar voor deze API.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['91'] = 'Versie 2.5 wordt niet langer ondersteund.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['92'] = 'De gateway accepteerd de gevraagde integratie methode niet meer.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['97'] = 'Deze transactie wordt niet geaccepteerd - Alleen voor de SIM API; Vingerafdrukken zijn slechts beperkte tijd bruikbaar. Al een vingerafdruk meer dan 15 minuten oud is wordt deze afgekeurd. Deze code geeft aan dat de vingerafdruk verlopen is.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['98'] = 'Deze transactie wordt niet geaccepteerd - Alleen voor SIM API. Deze vingerafdruk is al gebruikt voor een transactie.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['99'] = 'Deze transactie wordt niet geaccepteerd - Alleen voor SIM API. De op de server gegenereerde vingerafdruk klopt niet met de in het opgegeven x_fp_hash veld.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['100'] = 'Het eCheck.Net type is ongeldig - Alleen voor eCheck.Net. De waarde in x_echeck_type is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['101'] = 'De gegeven naar op het rekeningnummer en/of type kloppen niet met de werkelijke rekening - Alleen voor eCheck.Net. De opgegeven naam klopt niet met het NOC record voor deze rekening.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['102'] = 'Het verzoek wordt niet geaccepteerd - Een password of tansactie key zijn verzonden met een weblink verzoek. Dit is een hoog veiligheidsrisico.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['103'] = 'Deze transactie wordt niet geaccepteerd - Een geldige vingerfdruk, transactie key of password is verplicht voor deze transactie.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['104'] = 'Deze transactie wordt momenteel gecontroleerd - Alleen voor eCheck.Net. De gegeven landcode klopt niet';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['105'] = 'Deze transactie wordt momenteel gecontroleerd - Alleen voor eCheck.Net. De gegeven plaats en land waarden kloppen niet.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['106'] = 'Deze transactie wordt momenteel gecontroleerd. Alleen voor eCheck.Net. De gegeven bedrijfsnaam klopt niet.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['107'] = 'Deze transactie wordt momenteel gecontroleerd. Alleen voor eCheck.Net. De naam van de bank klopt niet.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['108'] = 'Deze transactie wordt momenteel gecontroleerd. Alleen voor eCheck.Net. De voor- en achternaam kloppen niet.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['109'] = 'Deze transactie wordt momenteel gecontroleerd. Alleen voor eCheck.Net. De voor- en achternaam kloppen niet.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['110'] = 'Deze transactie wordt momenteel gecontroleerd. Alleen voor eCheck.Net. Het veld voor het rekeningnummer bevat ongeldige tekens.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['116'] = 'De authenticatie indicator is ongeldig. Deze fout is alleen voor Visa en MasterCard SecureCode transacties. De ECI waarde voor Visa of de UCAF indicator voor MasterCard  transacties in het x_authentication_indicator veld is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['117'] = 'De kaarthouder authenticatie is ongeldig - Deze fout is alleen voor SecureCode transacties van Visa en masterCard. De CAVV voor Visa of AVV/UCAF voor MasterCard is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['118'] = 'De combinatie van de authenticatie indicator en kaarthouder authenticatie is ongeldig - Deze fout is alleen voor SecureCode transacties van Visa of MasterCard.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['119'] = 'Transacties met kaarthouder authenticatie kunnen geen periodieke transacties zijn - Deze fout is alleen voor SecureCode transacties van Visa en MasterCard. Transacties met x_authentication_indicator en x_recurring_billing = YES worden afgekeurd.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['123'] = 'Dit account heeft niet de benodigde toestemming voor dit verzoek - De transactie moet voorzien zijn van de API login ID voor het payment gateway account.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['128'] = 'Deze transactie kan niet verwerkt worden - De bank van uitgifte staat momenteel geen transacties toe voor deze rekening.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['3']['180'] = 'Er is een fout opgetreden tijdens verwerking. Probeer het opnieuw - De betalingsverwerker geeft een verkeerd antwoord.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['4']['193'] = 'Deze transactie wordt momenteel gecontroleerd - De transactie is aangeduid als "hoog risico"  en wordt gecontroleerd.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['200'] = 'Deze transactie is afgekeurd - Deze fout is alleen voor FDC Omaha klanten. Het creditcard nummer is ongeldig.';
$GLOBALS['TL_LANG']['MSG']['authorizedotnet']['2']['201'] = 'Deze transactie is afgekeurd - Deze fout is alleen voor FDC Omaha klanten. De vervaldatum is ongeldig.';

