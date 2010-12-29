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

$GLOBALS['TL_LANG']['CCT']['001'] = 'Visa';
$GLOBALS['TL_LANG']['CCT']['002'] = 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['003'] = 'American Express';
$GLOBALS['TL_LANG']['CCT']['004'] = 'Discover';
$GLOBALS['TL_LANG']['CCT']['005'] = 'Diner\'s Club';
$GLOBALS['TL_LANG']['CCT']['006'] = 'Carte Blanche';
$GLOBALS['TL_LANG']['CCT']['007'] = 'JCB';
$GLOBALS['TL_LANG']['CCT']['014'] = 'EnRoute';
$GLOBALS['TL_LANG']['CCT']['021'] = 'Jal';
$GLOBALS['TL_LANG']['CCT']['024'] = 'Maestro UK';
$GLOBALS['TL_LANG']['CCT']['031'] = 'Delta';
$GLOBALS['TL_LANG']['CCT']['032'] = 'Solo';
$GLOBALS['TL_LANG']['CCT']['033'] = 'Visa Electron';
$GLOBALS['TL_LANG']['CCT']['034'] = 'Dankort';
$GLOBALS['TL_LANG']['CCT']['035'] = 'Laser';
$GLOBALS['TL_LANG']['CCT']['036'] = 'Carte Bleue';
$GLOBALS['TL_LANG']['CCT']['037'] = 'Carta Si';
$GLOBALS['TL_LANG']['CCT']['039'] = 'Gecodeerd rekening-nummer';
$GLOBALS['TL_LANG']['CCT']['040'] = 'Universal Air Traveler Program';
$GLOBALS['TL_LANG']['CCT']['042'] = 'Meastro Internationaal';
$GLOBALS['TL_LANG']['CCT']['043'] = 'GE Money UK';
$GLOBALS['TL_LANG']['CYB']['100'] = 'Transactie gelukt';
$GLOBALS['TL_LANG']['CYB']['101'] = 'Er ontbreken één of meer verplichte velden in het verzoek. Mogelijke acties: Raadpleeg de velden missingField_0...N voor de ontbrekende velden. Biedt na completeren de transactie opnieuw aan. Raadpleeg de informatie over ontbrekende of foutieve velden in "Getting Started with CyberSource Essentials."';
$GLOBALS['TL_LANG']['CYB']['102'] = 'Een of meerdere velden in de transactie bevat ongeldige gegevens. Mogelijke acties: Raadpleeg de velden invalidFields_0...N voor de foutieve velden. Biedt na correctie de transactie opnieuw aan. Raadpleeg de informatie over ontbrekende of foutieve velden in "Getting Started with CyberSource Essentials."';
$GLOBALS['TL_LANG']['CYB']['110'] = 'Slechts een deel-betaling is goedgekeurd. Mogelijke acties: Zie deel-goedkeuringen';
$GLOBALS['TL_LANG']['CYB']['150'] = 'Fout: Algemene systeemfout. Raadpleeg de documentatie van uw Cybersource client (SDK) foor meer informatie over het opnieuw aanbieden van transacties in geval van een systeem-fout.';
$GLOBALS['TL_LANG']['CYB']['151'] = 'Fout: Het verzoek is ontvangen maar er is een server timeout geconstateerd. Mogelijke acties: Om duplicatie te voorkomen, deze transactie niet opnieuw aanbieden voordat u de status in het Business Center heeft gecontroleerd. Raadpleeg de documentatie van uw CyberSource client (SDK)  voor meer informatie over het opnieuw aanbieden van transacties in geval van een systeem-fout.';
$GLOBALS['TL_LANG']['CYB']['152'] = 'Fout: De transactie is ontvangen maar is niet tijdig afgehandeld. Mogelijke acties: Om duplicatie van de transactie te voorkomen, deze transactie niet opnieuw aanbieden voordat u de transactie status in het Business Center gecontroleerd heeft. Raadpleeg de documentatie voor uw CyberSource client (SDK) voor informatie over het opnieuw aanbieden van transacties in geval van een systeem-fout';
$GLOBALS['TL_LANG']['CYB']['200'] = 'Het autorisatieverzoek is goedgekeurd door de bank van uitgifte maar afgekeurd door CyberSource omdat de Adres Verificatie Service (AVS) controle faalt. Mogelijke acties: U kunt de autorisatie aannemen en de bestelling op mogelijke fraude controleren.';
$GLOBALS['TL_LANG']['CYB']['201'] = 'De bank van afgifte heeft vragen over de transactie. U ontvangt geen automatische autorisatie maar moet mogelijk telefonisch met een bankmedewerker contact opnemen. Mogelijke actie: Neem telefonisch contact op met uw bank voor een mondelinge autorisatie. Informeer bij uw bank naar het juiste telefoonnummer.';
$GLOBALS['TL_LANG']['CYB']['202'] = 'Verlopen kaart. Deze melding kunt u ook ontvangen als de opgegeven vervaldatum niet overeenkomt met de gegevens bij de bank. Mogelijke actie: Vraag om een andere kaart of een andere bwijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['203'] = 'Afgekeurde kaart. De bank van afgifte heeft geen verdere informatie verstrekt. Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['204'] = 'Ontoereikend saldo. Mogelijke actie: Verzoek een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['205'] = 'Deze kaart is als gestolen of verloren gemeld. Mogelijke actie: Neem contact op met uw bank voor handmatige controle.';
$GLOBALS['TL_LANG']['CYB']['207'] = 'Bank van afgifte onbereikbaar. Mogelijke actie: Wacht een paar minuten en biedt de transactie opnieuw aan.';
$GLOBALS['TL_LANG']['CYB']['208'] = 'Kaart is niet geactiveerd of niet geautoriseerd voor betalingen waarbij de kaart niet fysiek getoond wordt. Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['209'] = 'American Express Card Identification Digits (CID) kloppen niet. Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['210'] = 'De krediet limiet van de kaart is bereikt. Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['211'] = 'Ongeldige kaart verificatie (CCV nummer fout). Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['221'] = 'De klant komt overeen met een negatieve vermelding in het verwerkbestand. Mogelijke actie: Herzie de bestelling en neem contact op met de betalingsverwerker';
$GLOBALS['TL_LANG']['CYB']['230'] = 'Het autorisatieverzoek is goedgekeurd door de bank van uitgifte maar afgekeurd door CyberSource omdat de kaart controle (CV) faalt. Mogelijke acties: U kunt de autorisatie aannemen en de bestelling op mogelijke fraude controleren.';
$GLOBALS['TL_LANG']['CYB']['231'] = 'Ongeldig kaartnummer. Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['232'] = 'Het kaart-type wordt niet geaccepteerd. Mogelijke actie: Neem contact op met uw bank om te controleren of uw account deze kaart accepteert.';
$GLOBALS['TL_LANG']['CYB']['233'] = 'Algemene afwijzing door de bank. Mogelijke actie: Vraag om een andere kaart of een andere wijze van betaling.';
$GLOBALS['TL_LANG']['CYB']['234'] = 'Er is een probleem met uw CyberSource configuratie. Mogelijke actie: Biedt de transactie niet opnieuw aan. Neem contact op met customer support om uw configuratie te controleren.';
$GLOBALS['TL_LANG']['CYB']['235'] = 'Het gevraagde bedrag overschrijdt het origineel gereserveerde  bedrag. Dit kan optreden als u een hoger bedrag vraagt als oorspronkelijk gereserveerd. Mogelijke actie: Vraag een nieuwe reservering aan voor het gevraagde bedrag.';
$GLOBALS['TL_LANG']['CYB']['236'] = 'Bank fout: Mogelijke actie: Wacht een paar minuten en biedt de transactie opnieuw aan.';
$GLOBALS['TL_LANG']['CYB']['237'] = 'De reservering is al ongedaan gemaakt. Er is geen verdere actie nodig.';
$GLOBALS['TL_LANG']['CYB']['238'] = 'De reservering is al uitgevoerd. Er is geen verdere actie nodig.';
$GLOBALS['TL_LANG']['CYB']['239'] = 'Het gevraagde bedrag voor de transactie moet overeenkomen met dat van de vorige transactie. Mogelijke actie: Verander het bedrag en biedt de transactie opnieuw aan.';
$GLOBALS['TL_LANG']['CYB']['240'] = 'Het kaart-type is ongeldig of komt niet met het creditcard nummer overeen. Mogelijke actie: Controleer kaart-type en nummer in de transactie en biedt deze opnieuw aan.';
$GLOBALS['TL_LANG']['CYB']['241'] = 'Het aangevraagde ID is ongeldig. Mogelijke actie: Vraag een nieuwe goedkeuring aan en ga door met de transactie indien deze slaagt.';
$GLOBALS['TL_LANG']['CYB']['242'] = 'U vraagt een bedrag maar er is geen overeenkomstig autorisatie record. Dit treedt op als er geen verzoek tot autorisatie is gedaan of een vorige autorisatie al is gebruikt voor een andere betaling. Mogelijke actie: Vraag nieuwe autorisatie en ga door met de transactie indien deze slaagt.';
$GLOBALS['TL_LANG']['CYB']['243'] = 'De transactie is al afgerond of ongedaan gemaakt. Er is geen verdere actie nodig.';
$GLOBALS['TL_LANG']['CYB']['246'] = 'De transactie kan niet vernietigd worden omdat deze al naar uw bank is gestuurd. Of omdat dit type transactie niet vernietigd kan worden. Er is geen verdere actie nodig.';
$GLOBALS['TL_LANG']['CYB']['247'] = 'U vraagt een betaling voor een transactie die vernietigd is. Er is geen verdere actie nodig.';
$GLOBALS['TL_LANG']['CYB']['250'] = 'Fout: De transactie is ontvangen maar er is een timeout bij de bank opgetreden. Mogelijke actie: Om duplicatie van de transactie te voorkomen, deze transactie niet opnieuw aanbieden voordat u de transactie status in het Business Center gecontroleerd heeft.';

