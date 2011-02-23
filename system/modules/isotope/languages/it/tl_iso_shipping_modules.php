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
 * @copyright  2009-2011 Isotope eCommerce Workgroup
 * @author     Angelica Schempp <aschempp@gmx.net>
 * @author     Paolo B. <paolob@contaocms.it>
 * @author     Dan N <dan@dss.uniud.it>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */

$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name'][0] = 'Nome Metodo Spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type'][0] = 'Tipo Metodo Spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price'][0] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'][0] = 'Note Metodo Spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'][1] = 'Queste saranno visualizzate nel frontend associate all\'opzione di spedizione.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class'][0] = 'Classe aliquota';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'][0] = 'Etichetta';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'][1] = 'Questo sarà visualizzata nel frontend associata all\'opzione di spedizione.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation'][0] = 'Tassa fissa';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'][0] = 'Unità di misura';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'][1] = 'Unità di misura peso.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'][0] = 'Paesi';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'][1] = 'Selezioni i paesi ai quali questo metodo di spedizione sarà applicato. Se non selezioni niente, questo metodo di spedizione sarà applicato a tutti i paesi.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'][0] = 'Stato/Regione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'][1] = 'Selezionare lo stato/regione ai quali questo metodo di spedizione sarà applicato. Se non seleziona niente, questo metodo di spedizione sarà applicato a tutti gli stati/regioni.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total'][0] = 'Totale minimo';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total'][0] = 'Totale massimo';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'][0] = 'Tipi prodotto';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'][1] = 'Può limitare questo metodo di spedizione a certi prodotti. Se il carrello contiene un prodotto non selezionat, il modulo di spedizione non sarà disponibile.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'][0] = 'Sovrattassa Spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'][1] = 'Per cortesia selezioni una sovrattassa (ad esempio una sovrattassa carburante per tutti gli ordini) da applicare al metodo di spedizione, se presente.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'][0] = 'Gruppi membri';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'][1] = 'Limiti quest\'opzione di spedizione a certi gruppi di membri';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'][0] = 'Modulo protetto';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'][1] = 'Mostra il modulo soltanto ad alcuni membri del gruppo.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'][0] = 'Mostra solo agli ospiti';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'][1] = 'Nasconda il modulo se un membro è loggato.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'][0] = 'Attivato';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'][1] = 'Il modulo è disponibile all\'uso nel negozio?';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'][0] = 'Chiave accesso UPS XML/HTML';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'][1] = 'Questa è una chiave alfanumerica fornita da UPS insieme all\'account UPS per accedere agli strumenti online UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'][0] = 'Nome utente UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'][1] = 'Il nome utente UPS scelto per collegarsi al sito UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'][0] = 'Password UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'][1] = 'Questa è la password UPS scelta per collegarsi al sito web UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'][0] = 'Tipo servizio UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'][1] = 'Selezioni un tipo di spedizione UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'][0] = 'Tipo Servizio UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'][1] = 'Selezioni un tipo di spedizione UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'][0] = 'Nome utente UPS';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'][1] = 'Il nome utente UPS scelto per collegarsi al sito UPS.';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['title_legend'] = 'Titolo e tipo';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note_legend'] = 'Nota spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['config_legend'] = 'Configurazione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_legend'] = 'Impostazioni UPS API';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_legend'] = 'Impostazioni USPS API';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price_legend'] = 'Soglia prezzo e classe aliquota applicabile';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['expert_legend'] = 'Impostazioni esperto';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled_legend'] = 'Impostazioni attivate';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'][0] = 'Nuovo metodo spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'][1] = 'Crei un nuovo metodo di spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'][0] = 'Modifica spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'][1] = 'Modifica il metodo di spedizioneID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'][0] = 'Copia spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'][1] = 'Copia il metodo di spedizione ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'][0] = 'Cancella spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'][1] = 'Cancella metodo spedizione ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'][0] = 'Dettagli spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'][1] = 'Mostri i dettagli di spedizione ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates'][0] = 'Modifica Regole';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates'][1] = 'Modifica tariffe spedizione';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat'] = 'Tassa fissa';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct'] = 'Per prodotto';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem'] = 'Per pezzo';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['01'] = 'Giorno seguente sull\'aereo';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['02'] = 'Dal secondo giorno sull\'aereo';
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
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY'] = 'USPS Priority Mail (2-3 days average)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS'] = 'USPS Express Mail (Overnight Guaranteed)';
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

