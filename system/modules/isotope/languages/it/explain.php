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
 * @link https://www.transifex.com/projects/i/isotope/language/it/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['XPL']['isoReaderJumpTo'] = "\n<p class=\"tl_help_table\">\nA differenza di qualsiasi altro modulo Contao, un utente non viene reindirizzato alla pagina di lettura quando si visualizzano i dettagli del prodotto. Per risolvere il problema di bei alias e per conoscere la pagina di dettaglio di un prodotto, siamo arrivati ​​a una nuova soluzione.<br>\n<br>\nLa pagina di lettore (alias) sarà sempre la stessa pagina da voi selezionato come categoria per il prodotto. Ci sono due opzioni per visualizzare i dettagli di un prodotto:<br>\n<br>\n<strong>Opzione 1:</strong><br>\nNon impostare una pagina lettore nella struttura del sito. Posizionare la lista e modulo lettore sulla stessa pagina. Dite al modulo elenco di nascondersi se viene trovato un alias di prodotto (c'è una casella di controllo nelle impostazioni del modulo). Il lettore sarà automaticamente visibile se non viene trovato nessun lettore.<br>\n<u>Vantaggio:</u>Semplice da settare<br>\n<u>Svantaggio:</u> Il layout del lettore e della lista sarà identico, e non si può avere un contenuto articolo diverso per i due casi.<br>\n<br>\n<strong>Opzione 2:</strong><br>\nImpostare una pagina lettore per ogni pagina dell'elenco (categoria prodotto) nella struttura del sito. <i> Essere consapevoli del fatto che l'impostazione del lettore non viene ereditata!</ i> aggiungere il modulo di lettura a questa pagina, come al solito.<br>\nIsotope ora userà questa pagina per generare il sito se un alias prodotto è trovato nell'URL. L'alias sarà ancora quello della pagina di elenco però.<br>\n<u>Vantaggio:</u> Si possono avere contenuti  pagina e il layout diversi (ad esempio diverse colonne) per la pagina lettore poi la pagina di elenco.<br>\n<u>Svantaggio:</u> È NECESSARIO impostare una pagina lettore per ogni pagina di elenco (categoria) che avete. L'impostazione non è ereditata.\n</p>";
$GLOBALS['TL_LANG']['XPL']['mediaManager'] = '<p class="tl_help_table">Per caricare una nuova immagine selezioni il file e salvi il prodotto. Dopo aver caricato con successo, viene visualizzata un\'anteprima del messaggio e vicino ad essa puo\' inserire il testo alternativo e una descrizione. Per immagini multipli, puo\' fare click sulle frecce e cambiare la loro posizione, l\'immagine in cima è quella usata come immagine principale per ogni prodotto.</p>';
