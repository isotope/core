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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Rebecca Jutzi <rebecca.jutzi@bluewin.ch>
 * @author     Simon Moos <cc@simonmoos.com>
 * @author     Cyril Ponce <cyril@contao.fr>
 * @author     Stéphane Cagni <stephane@cagni.fr>
 * @author     Katelle Ave <contact@graphikat.net>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name'][0]            = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name'][1]            = 'Saisir un nom pour ce produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['class'][0]           = 'Catégorie de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['class'][1]           = 'Sélectionner une catégorie de produit. Différentes catégories de produits se chargeront des produits différemment.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback'][0]        = 'Type de produit par défaut';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback'][1]        = 'Cocher si c\'est le type de produit par défaut.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['description']        = array('Description', 'A hint to product managers what this product type is for.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices'][0]          = 'Tarification avancée';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices'][1]          = 'Permet de définir plusieurs prix par produit, par exemple, pour les différentes configuration de boutiques, des groupes de membres ou de dates.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['show_price_tiers']   = array('Show price tiers', 'Show highest tier as lowest product price.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template'][0]   = 'Modèles de liste';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template'][1]   = 'Sélectionner un modèle pour les listes de produits.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template'][0] = 'Modèles de lecteur';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template'][1] = 'Sélectionner un modèle pour les détails du produit.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes'][0]      = 'Attributs';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes'][1]      = 'Sélectionner la collection d\'attributs qui doit être incluse pour ce type de produit.';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants'][0]        = 'Activer les variantes';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants'][1]        = 'Vérifier si ce type de produit a des variantes';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variant_attributes'] = array('Attributs des variantes', 'Choisissez une collection d\'attributs de variante à ajouter à ce type de produit. Les attributs des variantes non sélectionnés sont invisibles et ne seront pas hérités du produit parent.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads'][0]       = 'Activer les téléchargements';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads'][1]       = 'Cocher si ce type de produit a des téléchargements.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['new'][0]    = 'Nouveau type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['new'][1]    = 'Créer un nouveau type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit'][0]   = 'Éditer le type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit'][1]   = 'Éditer le type de produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy'][0]   = 'Dupliquer le type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy'][1]   = 'Dupliquer le type de produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'][0] = 'Effacer le type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'][1] = 'Effacer le type de produit ID %s';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['show'][0]   = 'Détails du type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['show'][1]   = 'Montrer les détails du type de produit ID %s';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name_legend']        = 'Paramètres du type de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['description_legend'] = 'Description';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices_legend']      = 'Prix';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['template_legend']    = 'Modèles';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes_legend']  = 'Attributs de produit';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants_legend']    = 'Attributs de variante';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['expert_legend']      = 'Paramètres avancés';

//$GLOBALS['TL_LANG']['tl_iso_producttypes']['language_legend'] = 'Paramètres des langues';


/**
 * AttributeWizard
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['tl_class_select']   = 'Ici, vous pouvez choisir parmi quelques classes CSS prédéfinies de Contao';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['tl_class_text']     = 'Ici, vous pouvez écrire vos propres classes CSS qui doivent être appliquées au champ';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['mandatory_default'] = 'Obligatoire : Prendre la valeur par défaut';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['mandatory_no']      = 'Obligatoire : Non, jamais';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz']['mandatory_yes']     = 'Obligatoire : Oui, toujours';

