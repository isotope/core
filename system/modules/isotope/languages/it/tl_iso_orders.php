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
 * @author     Blair Winans <blair@winanscreative.com>
 * @author     Angelica Schempp <aschempp@gmx.net>
 * @author     Paolo B. <paolob@contaocms.it>
 * @author     Dan N <dan@dss.uniud.it>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */

$GLOBALS['TL_LANG']['tl_iso_orders']['order_id'][0] = 'ID Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['uniqid'][0] = 'ID Unico';
$GLOBALS['TL_LANG']['tl_iso_orders']['status'][0] = 'Stato Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['status'][1] = 'Selezioni lo stato di questo ordine.';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_payed'][0] = 'Data pagamento';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_payed'][1] = 'Inserisca una data quando questo ordine sarà stato pagato';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped'][0] = 'Data spedizione';
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped'][1] = 'Inserisca una data quando questo ordine sarà spedito';
$GLOBALS['TL_LANG']['tl_iso_orders']['date'][0] = 'Data';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address'][0] = 'Indirizzo Spedizione';
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address'][0] = 'Indirizzo Fatturazione';
$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'][0] = 'Subtotale';
$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax'][0] = 'Costo Tasse';
$GLOBALS['TL_LANG']['tl_iso_orders']['shippingTotal'][0] = 'Costo Spedizione';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'][0] = 'Metodo Spedizione';
$GLOBALS['TL_LANG']['tl_iso_orders']['surcharges'][0] = 'Sovrapprezzo';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'][0] = 'Numero Card';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'][1] = 'Il numero della carta di credito';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'][0] = 'Numero CCV';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'][1] = 'Numero vierifica di 3 o 4 cifre della Numero Verifica Carta di Credito (CCVN)';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'][0] = 'Tipo Card';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'][1] = 'Tipo della carta di credito';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'][0] = 'Scadenza';
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'][1] = 'La data di scadenza della carta di credito';
$GLOBALS['TL_LANG']['tl_iso_orders']['notes'][0] = 'Note ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['notes'][1] = 'Se vuole inviare informazioni ad altri utenti backend per cortesia lo faccia qui.';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels']['ups_ground'] = 'UPS Terra';
$GLOBALS['TL_LANG']['tl_iso_orders']['opLabel'] = 'Nome del Sovrapprezzo';
$GLOBALS['TL_LANG']['tl_iso_orders']['opPrice'] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_orders']['opTaxClass'] = 'Classe Tassa';
$GLOBALS['TL_LANG']['tl_iso_orders']['opAddTax'] = 'Aggiungere Tassa?';
$GLOBALS['TL_LANG']['tl_iso_orders']['new'][0] = 'Nuovo Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['new'][1] = 'Crea un Nuovo ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit'][0] = 'Modifica Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit'][1] = 'Modifica ordine ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['copy'][0] = 'Copia Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['copy'][1] = 'Copia ordine ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'][0] = 'Cancella Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'][1] = 'Cancella ordine ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['show'][0] = 'Dettagli Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['show'][1] = 'Mostra i dettagli dell\'ordine ID %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit_order'][0] = 'Modifica Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit_order'][1] = 'Modifica pezzi ordine, aggiunga o rimuova prodotti.';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit_order_items'][0] = 'Modifica Pezzi Ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['edit_order_items'][1] = 'Modifica pezzi ordine %s';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'][0] = 'Stampi questo ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'][1] = 'Stampi una fattura per l\'ordine corrente';
$GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'][0] = 'Authorize.net Point-of-sale Terminal';
$GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'][1] = 'Esegua una transazione usando il Authorize.net Point-of-sale Terminal';
$GLOBALS['TL_LANG']['tl_iso_orders']['tools'][0] = 'Strumenti';
$GLOBALS['TL_LANG']['tl_iso_orders']['tools'][1] = 'Più opzioni per amministrazione ordini';
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'][0] = 'Esporta Email Ordini';
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'][1] = 'Esporta tutte le email di quelli che hanno ordinato.';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'][0] = 'Stampa Fatture';
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'][1] = 'Stampa una o più fatture in un solo documento di un certo stato dell\'ordine.';
$GLOBALS['TL_LANG']['tl_iso_orders']['status_legend'] = 'Stato ordine';
$GLOBALS['TL_LANG']['tl_iso_orders']['details_legend'] = 'Dettagli ordine';

