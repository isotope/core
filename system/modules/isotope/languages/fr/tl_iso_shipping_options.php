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
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'][0]          = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'][1]          = 'Une brève description de ce taux. Utilisé en front office.';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'][0]          = 'Taux';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'][1]          = 'Le taux du port au format monétaire.';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description'][0]   = 'Description';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description'][1]   = 'Une description du taux qui peut être utilisé pour expliquer à la clientèle comment ce taux est calculé.';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total'][0] = 'Total minimum';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total'][0] = 'Total maximum';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_from'][0]   = array('Poids à partir de', 'If overall weight of all products in cart is more than this, the rate will match. Make sure you set the correct weight unit in module settings.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_to'][0]     = array('Poids jusqu\'à', 'If overall weight of all products in cart is less than this, the rate will match. Make sure you set the correct weight unit in module settings.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['enabled']          = array('Enabled', 'Is the rate available for use in the store?');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['general_legend'] = 'Information générale';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['config_legend']  = 'Configuration';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['new'][0]    = 'Nouveau taux de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['new'][1]    = 'Créer un nouveau taux de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit'][0]   = 'Éditer un taux de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit'][1]   = 'Éditer le taux de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy'][0]   = 'Dupliquer un taux de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy'][1]   = 'Dupliquer le taux de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete'][0] = 'Supprimer un taux de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete'][1] = 'Supprimer le taux de livraison ID %s';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show'][0]   = 'Détails du taux de livraison';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show'][1]   = 'Afficher les détails du taux de livraison ID %s';
