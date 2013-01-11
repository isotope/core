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
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name'][0]        = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name'][1]        = 'Saisir un nom pour le taux de taxe.';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label'][0]       = 'Libellé';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label'][1]       = 'Ce libellé sera utilisé en front office lors du processus de paiement.';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address'][0]     = 'Adresse à utiliser pour le calcul';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address'][1]     = 'Sélectionner quelle adresse sera utilisée pour appliquer le calcul de ce taux.';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postal'][0]      = 'Plage de codes postaux';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postal'][1]      = 'Spécifier une plage de codes postaux auxquels s\'applique ce taux de taxe. (Ex. : 10000 à 20000)';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount'][0]      = 'Restriction au montant du total partiel';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount'][1]      = 'Facultatif : restreindre cette taxe à un sous-total spécifique (comme par exemple une taxe de luxe.)';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate'][0]        = 'Taux de taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate'][1]        = 'Définir un taux de taxe en pourcentage.';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'][0]      = 'Configuration de boutique';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'][1]      = 'Sélectionner la configuration de boutique à laquelle s\'applique le taux de taxe.';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'][0]        = 'Stopper les calculs au déclenchement ?';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'][1]        = 'Arrêter les autres calculs si ce taux de taxe est déclenché';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new'][0]    = 'Nouveau taux de taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new'][1]    = 'Créer un nouveau taux de taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit'][0]   = 'Éditer un taux de taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit'][1]   = 'Éditer le taux de taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy'][0]   = 'Dupliquer un taux de taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy'][1]   = 'Dupliquer le taux de taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete'][0] = 'Supprimer un taux de taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete'][1] = 'Supprimer le taux de taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show'][0]   = 'Détails du taux de taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show'][1]   = 'Afficher les détails du taux de taxe ID %s';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['billing']  = 'Adresse de facturation';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['shipping'] = 'Adresse de livraison';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name_legend']      = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate_legend']      = 'Taux';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['location_legend']  = 'Lieu';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['condition_legend'] = 'Conditions';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config_legend']    = 'Configuration';
