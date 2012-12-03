<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Blair Winans <blair@winanscreative.com>
 * @author     Paolo B. <paolob@contaocms.it>
 * @author     Dan N <dan@dss.uniud.it>
 */

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][0] = 'Gateway Tipo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][1] = 'Selezioni un gateway di pagamento (ad es. Authorize.net)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][0] = 'Nome Metodo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][1] = 'Inserisca un nome per questo metodo di pagamento. Questo sarà utilizzato soltanto nel backend.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][0] = 'Etichetta Metodo di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][1] = 'L\'etichetta sarà mostrata ai clienti al checkout.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][0] = 'Nota di Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][1] = 'Questa nota può essere inviata nella mail di conferma (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'][0] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'][0] = 'Classe Aliquota';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][0] = 'Stato nuovi ordini';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][1] = 'Scelga uno stato corrispondente per i nuovi ordini.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postsale_mail'][0] = 'Template Email per cambiamenti di stato';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postsale_mail'][1] = 'Selezioni una template email per dar notizia alll\'amministratore del negozio sui cambiamenti di stato.';
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
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][0] = 'Tipo transazione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][1] = 'Selezioni se vuole avere subito i soldi o autorizzare (e tenere) per una transazione successiva (ad es. alla spedizione).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][0] = 'Conto PayPal';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][1] = 'Inserisca il suo conto di paypal (indirizzo di mail).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_user'][0] = 'Nome utente Paypal Payflow';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'][0] = 'Venditore Paypal Payflow';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'][1] = 'Una stringa alfanumerica di circa 10 caratteri.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'][0] = 'Socio Paypal Payflow';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'][1] = 'Attenzione MAIUSCOLE/minuscole! Normalmente l\'Id dei soci è o "PayPal" o "PayPalUK".';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'][0] = 'Password API Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'][1] = 'Una stringa alfanumerica di circa 11 caratteri';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'][0] = 'Tipo transazione Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'][1] = 'Per cortesia selezioni il tipo di transazione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'][0] = 'Numero negoziante';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'][1] = 'Il numero negoziante unico creato in ePay. Questo numero negoziante si trova sul contratto con la PBS.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'][0] = 'Chiave segreta';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'][1] = 'La chiave segreta inserita nella sua configurazione ePay';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'][0] = 'Bottone Checkout';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'][1] = 'Può mostrare un pulsante checkout al posto di quello preimpostato.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][0] = 'Necessita del Numero di Verifica Codice Card (CCV)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][1] = 'Scelga questa opzione se vuole aumentare la sicurezza della transazione chiedendo il numero di verifica dlla card.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'][0] = 'Id negoziante Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'][1] = 'Inserisca qui l\'id negoziante Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'][0] = 'Chiave transazione Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'][1] = 'Fornita quando ha richiesto accesso al gateway';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type'][0] = 'Tipo transazione Cybersource';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type'][1] = 'Autorize & Capture, ad esempio - la prima fase è autorizzare validando i dati inseriti dal cliente e il passo successivo è inviare per completare la transazione, operazione chiamata "capture".';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][0] = 'Tipi Carte di Credito Ammesse';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][1] = 'Selezioni quele modulo pagamento carta di credito è accettato.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'][0] = 'Login Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'][1] = 'Fornita quando ha richiesto accesso al gateway';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'][0] = 'Chiave Transazione Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'][1] = 'Fornita quando ha richiesto accesso al gateway';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter'][0] = 'Delimitatore Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter'][1] = 'Che carattere deve essere inserito come delimitatore dati per la rispota?';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'][0] = 'Tipo Transazione Authorize.net';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'][1] = 'Autorize & Capture, ad esempio - la prima fase è autorizzare validando i dati inseriti dal cliente e il passo successivo è inviare per completare la transazione, operazione chiamata "capture".';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_clearingtype'][0] = 'Tipo liquidazione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_clearingtype'][1] = 'Per cortesia scelga un tipo di liquidazione.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_aid'][0] = 'ID conto PAYONE';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_aid'][1] = 'Per cortesia inserisca il suo ID unico per il conto PAYONE';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_portalid'][0] = 'ID Portale PAYONE';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_portalid'][1] = 'Per cortesia inserisca il suo ID unico per il portale PAYONE';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_key'][0] = 'Chiave segreta';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_key'][1] = 'Inserisca la chiave segreta che ha specificato per questo portale.';
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
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['capture'] = 'Autorizza & Cattura';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['auth'] = 'Solo Autorizza';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_CAPTURE'] = 'Autorizza e Cattura';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_ONLY'] = 'Solo Autorizza';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping'] = 'Ordini senza spedizione';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][0] = 'Autorizza & Cattura';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][1] = 'Le transazioni di questo tipo saranno inviate all\'autorizzazione. La transazione sarà automaticamente ccolta per la transazione se approvata. Questo è il tipo di transazione preimpostato nella gateway. Se nessun tipo è selezionato quando si invia la transazione al gateway, esso supporrà che la transazione è di questo tipo.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][0] = 'Solo Autorizza';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][1] = 'Le transazioni di questo tipo sono inviate se il negoziante desidera validare la carta di credito per la quantità di beni venduti. Se il negoziante non ha beni in magazzino o vuole rivedere l\'ordine prima di inviare i beni, questa tipo di transazione deve essere inviata. Il gateway spedirà questo tipo di transazione all\'istituto finanziario per l\'approvazione. Ad ogni modo, questo tipo di transazine non sarà completata. Se il negoziante non fa niente con la transazione entro 30 giorni, la transazione non sarà più valida per la cattura.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][0] = 'Solo Cattura';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][1] = 'Questo è richiesto per completare una transazione che non è stata inviata per l\'autorizzazione attraverso il gateway di pagamento. Il gateway accetterà questa transazione se sarà inviato un codice di autorizzazione. x_auth_code è un campo richiesto per i tipi di transazione CAPTURE_ONLY';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][0] = 'Credito';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][1] = 'La transazione è anche riferita come "Rimborso" e indica al gateway che i soldi devono fluire dal negoziante al cliente. Il gateway accetterà una carta di credito per una richiesta di rimborso se la transazione inviata soddisfa le seguenti condizioni:<ul><li>La transazione è inviata con lo stesso ID della transazione originale associata alla stessa carta di credito</li><li>Il gateway ha registrato la transazione originale</li><li>La transazione originale si è conclusa</li><li>La somma della transazione con la carta di credito e tutti i crediti inviati con la transazione originale è minore della quantita della transazione iniziale</li><li>L\'intera sequenza o le ultime quattro cifre della carta di credito inviate con la carta di credito corrispondono quelle usate nella transazione originale</li><li>La transazione è inviata entro 120 giorni dalla data e l\'ora del completamento della transazione</li></ul> Una chiave di transazione è necessaria per inviare credito al sistema.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID'][0] = 'Vuoto';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID'][1] = 'La transazione è un\'azione su una transazione precedente ed è usata per cancellare la transazione precedente e assicurare che non sarà inviata al completamento. Può essere eseguita su qualsiasi tipo di transazione( ad es. CREDIT, AUTH_CAPTURE, CAPTURE_ONLY, e AUTH_ONLY). La transazione sarà accettata dal gateway se le seguenti condizioni saranno soddisfatte: <ul><li>La transazione è inviata con l\'ID della transazione che deve essere vuotata</li><li>Il gateway ha registrato la transazione con un ID.</li><li>La transazione non è stata completata.</li></ul>';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE'][0] = 'Autorizzazione e Cattura Precedente';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE'][1] = 'Questa transazione è usata per richiedere il completamento di una transazione che precedentemente è stata inviata come AUTH_ONLY. Il gateway accetterà questa transazione e inizierà il completamento se le seguenti condizioni saranno soddisfatte:<ul> <li>La transazione è inviata con l\'ID della transazione di sola autorizzazione, che ha bisogno di essere completata.</li> <li>L\'ID della transazione è valido e il sistema ha registrato la transazione inviata.</li> <li>La transazione originale alla quale ci riferiamo non è stata ancora completata, scaduta o ha dato errore.</li><li>La quantità richiesta per il completamento della transazione è minore o ugulae in questa della quantità autorizzata.</li></ul>Se nessuna quantià è stata inviata in questa transazione, il gateway inizierà a dar corso alla transazione per il massimo della quantità ammessa nella transazione. <em>Nota: se sono state inserite informazioni dettagliate riferite ai prodotti, aliquota, quantità e/o informazioni sulla dogana insieme alla transazione originale, si possono modificare le informazioni se la quantià della transazione cambia. Se non si invia nessun genere di informazione, sarà applicata l\'informazione inviata insieme alla transazione originale.</em>';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Sale'] = 'Autorizza e Cattura';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Authorization'] = 'Solo Autorizza';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend'] = 'Nome & Tipo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend'] = 'Note Aggiuntive';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend'] = 'Configurazione Generale';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend'] = 'Configurazione Gateway Pagamento';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend'] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend'] = 'Template';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend'] = 'Impostazioni Avanzate';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend'] = 'Impostazioni Abilitate';
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

