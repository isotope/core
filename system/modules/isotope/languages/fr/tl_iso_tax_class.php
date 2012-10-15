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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name'][0]                = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name'][1]                = 'Donner à cette taxe un nom qui explique pourquoi elle est utilisée.';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback'][0]            = 'Taxe par défaut';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback'][1]            = 'Définir cette taxe  par défaut.';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement'] = array('Apply rounding increment', 'Check here if you want to apply the rounding increment of your shop config.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes'][0]            = 'Taux de taxe inclus avec le prix du produit';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes'][1]            = 'Sélectionner si les prix des produits avec cette taxe possèdent un taux de taxe. Ce taux de taxe sera alors déduit du prix des produits si cela ne correspond pas.';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['label']                  = array('Inclure un libellé', 'A label for orders to present for subtracted taxes (if included tax does not match). Default tax rate label will be used if this is blank.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates'][0]               = 'Taux de taxe à appliquer';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates'][1]               = 'Ajouter ces taux de taxe aux produits qui auront cette taxe.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['new'][0]    = 'Nouvelle taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['new'][1]    = 'Créer un nouvelle taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit'][0]   = 'Éditer une taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit'][1]   = 'Éditer la taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy'][0]   = 'Dupliquer une taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy'][1]   = 'Dupliquer la taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'][0] = 'Supprimer une taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'][1] = 'Supprimer la taxe ID %s';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['show'][0]   = 'Détails de la taxe';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['show'][1]   = 'Afficher les détails de la taxe ID %s';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name_legend'] = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rate_legend'] = 'Taux de taxe';

