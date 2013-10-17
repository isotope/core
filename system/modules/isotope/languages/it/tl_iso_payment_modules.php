<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2013 Isotope eCommerce Workgroup
 * 
 * Core translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/it/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][0] = 'Nome Metodo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][1] = 'Inserisca un nome per questo metodo di pagamento. Questo sarà utilizzato soltanto nel backend.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][0] = 'Etichetta Metodo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][1] = 'L\'etichetta sarà mostrata ai clienti al checkout.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][0] = 'Gateway Tipo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][1] = 'Selezioni un gateway di pagamento (ad es. Authorize.net)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][0] = 'Nota di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][1] = 'Questa nota può essere inviata nella mail di conferma (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][0] = 'Stato nuovi ordini';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][1] = 'Scelga uno stato corrispondente per i nuovi ordini.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'][0] = 'Totale minimo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'][1] = 'Inserisca un numero maggiore di zero per escludere questo metodo di pagamento per ordini con prezzi bassi.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'][0] = 'Totale massimo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'][1] = 'Inserisca un numero maggiore di zero per escludere questo metodo di pagamento per ordini con prezzi alti.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'][0] = 'Paesi disponibili';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'][1] = 'Selezioni i paesi in cui questo metodo di pagamento può essere usato (indirizzo fatturazione del cliente)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'][0] = 'Metodi di spedizione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'][1] = 'Può limitare questo metodo di pagamento a certi metodi di spedizione (es. Contanti alla consegna).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'][0] = 'Tipi prodotto';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'][1] = 'Può limitare questo metodo di pagamento a certi tipi di prodotto. Se il carello contiene un tipo di prodotto non selezionato, il modulo di pagamento non sarà disponibile.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'][0] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'][0] = 'Classe Aliquota';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][0] = 'Tipo transazione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][1] = 'Selezioni se vuole avere subito i soldi o autorizzare (e tenere) per una transazione successiva (ad es. alla spedizione).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][0] = 'Conto PayPal';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][1] = 'Inserisca il suo conto di paypal (indirizzo di mail).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][0] = 'Necessita del Numero di Verifica Codice Card (CCV)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][1] = 'Scelga questa opzione se vuole aumentare la sicurezza della transazione chiedendo il numero di verifica dlla card.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][0] = 'Tipi Carte di Credito Ammesse';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][1] = 'Selezioni quele modulo pagamento carta di credito è accettato.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][0] = 'Gruppi Membri';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][1] = 'Limiti questo metodo di pagamento a certi gruppi membri.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][0] = 'Proteggi modulo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][1] = 'Mostri il metodo di pagamento solo ad alcuni gruppi membri.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][0] = 'Mostri solo agli ospiti';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][1] = 'Nasconda il metodo di pagamento se un membro è loggato.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][0] = 'Modo debug';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][1] = 'Per testare senza eseguire il pagamento.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][0] = 'Abilitato';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][1] = 'Spunti qui se il modulo pagamento deve essere abilitato nel negozio.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][0] = 'Nuovo metodo di pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][1] = 'Crea un nuovo metodo di pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][0] = 'Modifica metodo di pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][1] = 'Modifica metodo di pagamento ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][0] = 'Copia metodo di pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][1] = 'Copia metodo di pagamento ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][0] = 'Cancella metodo di pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][1] = 'Cancella metodo di pagamento ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][0] = 'Dettagli Metodo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][1] = 'Mostra dettagli metodo di pagamento ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping'] = 'Ordini senza spedizione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend'] = 'Nome & Tipo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend'] = 'Note Aggiuntive';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend'] = 'Configurazione Generale';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend'] = 'Configurazione Gateway Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend'] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend'] = 'Template';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend'] = 'Impostazioni Avanzate';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend'] = 'Impostazioni Abilitate';
