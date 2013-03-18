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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['isotope']                   = ($_GET['do'] == '' ? '<a href="http://www.isotopeecommerce.com/"'.LINK_NEW_WINDOW.' class="isotope-logo" style="display:none"><img src="system/modules/isotope/html/isotope-logo.png" alt="Isotope eCommerce Logo" /></a><span class="isotope-title">Isotope eCommerce</span>' : 'Isotope eCommerce');
$GLOBALS['TL_LANG']['MOD']['iso_products'][0]           = 'Produits';
$GLOBALS['TL_LANG']['MOD']['iso_orders'][0]             = 'Commandes';
$GLOBALS['TL_LANG']['MOD']['iso_setup'][0]              = 'Configuration';


/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['isotope']                   = 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter'][0]      = 'Filtre de produit';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter'][1]      = 'Permet de définir les filtres individuels pour Isotope tels que l\'arborescence des catégories et les filtres d\'attributs des produits.';
$GLOBALS['TL_LANG']['FMD']['iso_cumulativefilter']      = array('Cumulative Filter', 'Provides a cumulative filter so visitors can cut down the product choice by clicking on multiple conditions.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist'][0]        = 'Liste de produits';
$GLOBALS['TL_LANG']['FMD']['iso_productlist'][1]        = 'Peut être utilisé pour lister des produits ou des valeurs d\'attributs. Peut être combiné avec d\'autres modules (ex. : le module de filtrage).';
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist'][0] = 'Liste de variantes d\'un produit';
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist'][1] = 'Liste de chaque variante d\'un produit. Assurez-vous d\'utiliser le modèle iso_list_variants.';
$GLOBALS['TL_LANG']['FMD']['iso_productreader'][0]      = 'Détails d\'un produit';
$GLOBALS['TL_LANG']['FMD']['iso_productreader'][1]      = 'Module lecteur de produits. Il est utilisé pour afficher les détails d\'un produit.';
$GLOBALS['TL_LANG']['FMD']['iso_cart'][0]               = 'Panier d\'achat';
$GLOBALS['TL_LANG']['FMD']['iso_cart'][1]               = 'Module panier d\'achat. En box ou un plein affichage peut être réglé par la sélection de modèle.';
$GLOBALS['TL_LANG']['FMD']['iso_checkout'][0]           = 'Commander';
$GLOBALS['TL_LANG']['FMD']['iso_checkout'][1]           = 'Permet aux clients de finaliser leur commande.';
$GLOBALS['TL_LANG']['FMD']['iso_addressbook'][0]        = 'Carnet d\'adresses';
$GLOBALS['TL_LANG']['FMD']['iso_addressbook'][1]        = 'Permet aux clients de gérer leur carnet d\'adresses.';
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory'][0]       = 'Historique des commandes';
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory'][1]       = 'Permet aux clients de consulter l\'historique de leurs commandes.';
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails'][0]       = 'Détails des commandes';
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails'][1]       = 'Permet aux clients de consulter le détail de leurs commandes dans l\'historique.';
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher'][0]     = 'Changeur de configuration de boutique';
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher'][1]     = 'Basculer entre les configurations de boutique pour changer de monnaie et d\'autres paramètres.';
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts'][0]    = 'Produits similaires';
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts'][1]    = 'Lister les produits similaires au produit courant.';


/**
 * Isotope Modules
 */
$GLOBALS['TL_LANG']['ISO']['config_module']             = 'Configuration Isotope eCommerce (Version: %s)';
$GLOBALS['TL_LANG']['IMD']['product']                   = 'Produits';
$GLOBALS['TL_LANG']['IMD']['attributes'][0]             = 'Attributs';
$GLOBALS['TL_LANG']['IMD']['attributes'][1]             = 'Gérer et créer des attributs de produits telles que la taille, la couleur, etc.';
$GLOBALS['TL_LANG']['IMD']['producttypes'][0]           = 'Types de produits';
$GLOBALS['TL_LANG']['IMD']['producttypes'][1]           = 'Gérer et créer des types de produits à partir d\'ensembles d\'attributs.';
$GLOBALS['TL_LANG']['IMD']['related_categories'][0]     = 'Catégories similaires';
$GLOBALS['TL_LANG']['IMD']['related_categories'][1]     = 'Choisir des catégories pour définir les relations de produits.';
$GLOBALS['TL_LANG']['IMD']['checkout']                  = 'Déroulement de la commande';
$GLOBALS['TL_LANG']['IMD']['shipping'][0]               = 'Modes de livraison';
$GLOBALS['TL_LANG']['IMD']['shipping'][1]               = 'Mise en place de modes de livraison tels que UPS, USPS, DHL, etc.';
$GLOBALS['TL_LANG']['IMD']['payment'][0]                = 'Modes de paiement';
$GLOBALS['TL_LANG']['IMD']['payment'][1]                = 'Mise en place de modes de paiement tels que Authorize.net, PayPal Pro, et plus encore.';
$GLOBALS['TL_LANG']['IMD']['tax_class'][0]              = 'Taxes';
$GLOBALS['TL_LANG']['IMD']['tax_class'][1]              = 'Mise en place de taxes, qui contiennent des ensembles de taux de taxe.';
$GLOBALS['TL_LANG']['IMD']['tax_rate'][0]               = 'Taux de taxe';
$GLOBALS['TL_LANG']['IMD']['tax_rate'][1]               = 'Mise en place des taux de taxe basés sur des choses comme le lieu de livraison/facturation et le total de la commande.';
$GLOBALS['TL_LANG']['IMD']['config']                    = 'Paramètres généraux';
$GLOBALS['TL_LANG']['IMD']['orderstatus']               = array('État des commandes', 'Définir l\'état des commandes.');
$GLOBALS['TL_LANG']['IMD']['baseprice']                 = array('Prix de base', 'Définir les prix de base.');
$GLOBALS['TL_LANG']['IMD']['iso_mail'][0]               = 'Gestion des e-mails';
$GLOBALS['TL_LANG']['IMD']['iso_mail'][1]               = 'Personnaliser les e-mails de notification de l\'administrateur et des clients.';
$GLOBALS['TL_LANG']['IMD']['configs'][0]                = 'Configuration de boutique';
$GLOBALS['TL_LANG']['IMD']['configs'][1]                = 'Configurer les paramètres généraux de la boutique.';

