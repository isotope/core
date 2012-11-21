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
 * @author     Blair Winans <blair@winanscreative.com>
 * @author     Paolo B. <paolob@contaocms.it>
 * @author     Dan N <dan@dss.uniud.it>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_attributes']['name'][0] = 'Nome';
$GLOBALS['TL_LANG']['tl_iso_attributes']['name'][1] = 'Per cortesia inserisca un nome per questo attributo.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'][0] = 'Nome interno';
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'][1] = 'Il nome interno è il nome campo nel database e deve essere unico.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['type'][0] = 'Tipo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['type'][1] = 'Per cortesia selezioni un tipo per questo attributo.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'][0] = 'Grupo Campo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'][1] = 'Selezioni un gruppo campo al quale questo attributo è relazionato (utilizzato per organizzare i campi relazionati in gruppi campo collassabili quando si modiricano i prodotti.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'][0] = 'Aggiungi un wizard versioni prodotto.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'][1] = 'Se selezionato, questo attributo sarà aggiunto al wizard versioni prodotto da usare come un\'opzione versione prodotto.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'][0] = 'Definito dal Cliente';
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'][1] = 'Per cortesia selezioni se questo valore è definito dal cliente (frontend).';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description'][0] = 'Descrizione';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description'][1] = 'La descrizione è mostrata come suggerimento per l\'utilizzatore backend.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options'][0] = 'Opzioni';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options'][1] = 'Per cortesia inserisca una o più opzioni. Usi i pulsatni per aggiungere, spostare o cancellare un\'opzione. Se non ha il JavaScript abilitato, dovrebbe salvare i cambiamenti prima di modificare l\'ordine!';
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'][0] = 'Campo obligatorio';
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'][1] = 'Il prodotto non sarà aggiunto al carrello se il campo è vuoto.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'][0] = 'Selezione multipla';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'][1] = 'Permetta ai visitatori di selezionare più di un\'opzione';
$GLOBALS['TL_LANG']['tl_iso_attributes']['size'][0] = 'Dimensione elenco';
$GLOBALS['TL_LANG']['tl_iso_attributes']['size'][1] = 'Qui può inserire la dimensione del box di selezione.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'][0] = 'Tipi file permessi.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'][1] = 'Un elenco di estensioni valide separate da virgola';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'][0] = 'Usi l\'editor HTML';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'][1] = 'Selezioni una configurazione tinyMCE per abilitare il text editore avanzato.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'][0] = 'Multilingua';
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'][1] = 'Spunti qui se questo campo deve essere tradotto.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp'][0] = 'Validazione imput';
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp'][1] = 'Validare l\'imput con un\'espressione regolare.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'][0] = 'Lunghezza massima';
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'][1] = 'Limiti la lunghezza del campo a un certo numero di caratteri (testo) o byte (caricamenti file)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'][0] = 'Tabella & Campo Esterni';
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'][1] = 'Invece di aggiungere opzioni può inserire una combinazione campo.tablella per selezionare dal database.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField'][0] = 'Campo genitore';
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField'][1] = 'Per cortesia selezioni il campo genitore, chq deve essere del tipo "Menu-Selezione". Per far funzionare le relazioni genitore-figlio, defenisca ogni opzione in questo campo genitore come gruppo del menu selezione condizionale.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'][0] = 'Galleria immagini';
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'][1] = 'Diverse gallerie immagini possono essere sviluppate per mostrare file mediali in stile personalizzato.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting'][0] = 'Aggiungi all\'elenco l\'opzione "Ordina per"';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting'][1] = 'Questo campo sarà ordinabile nel modulo elenco a patto che l\'attributo sia visibile ai clienti.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter'][0] = 'Backend Filtrabile';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter'][1] = 'Può questo attributo essere utilizzato in un filtro backend?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'][0] = 'Ricercabile Backend';
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'][1] = 'Questo motore di ricerca deve usare questo campo per i termini di ricerca?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'][0] = 'Frontend Filtrabile';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'][1] = 'Può questo attributo essere usato in un filtro frontend?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'][0] = 'Frontend Ricercabile';
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'][1] = 'Questo motore di ricerca deve usare questo campo per i termini di ricerca?';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['value'] = array('Valore');
$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['label'] = array('Ettichetta');
$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['default'] = array('Preimpostato');
$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['group'] = array('Gruppo');
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit'][0] = 'Caratteri numerici';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit'][1] = 'Permette caratteri numerici, minus (-), punto (.) e spazio ().';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha'][0] = 'Caratteri alfabetici';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha'][1] = 'Permette caratteri alfabetici, minus (-), punto (.) e spazio ().';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum'][0] = 'Caratteri alfanumerici';
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum'][1] = 'Permette caratteri alfabetici e numerici, minus (-), punto (.), trattino basso (_) e spazio ().';
$GLOBALS['TL_LANG']['tl_iso_attributes']['extnd'][0] = 'Caratteri alfanumerici estesi';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date'][0] = 'Data';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date'][1] = 'Verifica se l\'input corrisponde al formato data globale.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['time'][0] = 'Ora';
$GLOBALS['TL_LANG']['tl_iso_attributes']['time'][1] = 'Verifica se l\'input corrisponde al formato ora globale.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim'][0] = 'Data e ora';
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim'][1] = 'Verifica se l\'input corrisponde al formato data e ora globale.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone'][0] = 'Numero di telefono';
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone'][1] = 'Permette caratteri numerici, (+), minus (-), slash (/), parentesi () e spazio ().';
$GLOBALS['TL_LANG']['tl_iso_attributes']['email'][0] = 'Indirizzo e-mail';
$GLOBALS['TL_LANG']['tl_iso_attributes']['email'][1] = 'Verifichi se l\'input è un indirizzo di mail valido.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['url'][0] = 'Formato URL';
$GLOBALS['TL_LANG']['tl_iso_attributes']['url'][1] = 'Verifica se l\'input è un campo valido URL';
$GLOBALS['TL_LANG']['tl_iso_attributes']['price'][0] = 'Prezzo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['price'][1] = 'Verifica se l\'input è un prezzo valido.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount'][0] = 'Sconto';
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount'][1] = 'Verifica se l\'input è uno scondo valido. <br />Esempio: -10%, -10, +10, +10%';
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge'][0] = 'Sovrattassa';
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge'][1] = 'Verifica se l\'input è una sofrattassa valida. <br />Esempio: 10.00, 10%';
$GLOBALS['TL_LANG']['tl_iso_attributes']['new'][0] = 'Nuovo Attributo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['new'][1] = 'Crea un nuovo attributo.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'][0] = 'Modifica Attributo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'][1] = 'Modifica attributo ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'][0] = 'Copia Attributo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'][1] = 'Copia attributo ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'][0] = 'Cancella Attributo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'][1] = 'Cancella attributo ID %s. La colonna nel database non è stata cancellata, devi aggiornare il database manualmente usando il tool di installazione o il repository manager.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['show'][0] = 'Mostra Dettagli Attributo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['show'][1] = 'Mostra i dettagli per l\'attributo ID %s.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['deleteConfirm'] = 'Vuole veramente cancellare l\'attributo ID %s. La colonna nel database non è stata cancellata, devi aggiornare il database manualmente usando il tool di installazione o il repository manager.';
$GLOBALS['TL_LANG']['tl_iso_attributes']['attribute_legend'] = 'Attributo nome & tipo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description_legend'] = 'Descrizione';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options_legend'] = 'Opzioni';
$GLOBALS['TL_LANG']['tl_iso_attributes']['config_legend'] = 'Configurazione attributo';
$GLOBALS['TL_LANG']['tl_iso_attributes']['validation_legend'] = 'Validazione input';
$GLOBALS['TL_LANG']['tl_iso_attributes']['search_filters_legend'] = 'Impostazioni Cerca & Filtra';

