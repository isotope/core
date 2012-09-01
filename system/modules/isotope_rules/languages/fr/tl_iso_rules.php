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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['type']                = array('Type', 'Please choose the type of rule.');
$GLOBALS['TL_LANG']['tl_iso_rules']['name']                = array('Name', 'Please enter a name for this rule.');
$GLOBALS['TL_LANG']['tl_iso_rules']['label']               = array('Label', 'The label will be show in cart. If you do not enter a label, the name will be used.');
$GLOBALS['TL_LANG']['tl_iso_rules']['discount']            = array('Discount', 'Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.');
$GLOBALS['TL_LANG']['tl_iso_rules']['tax_class']           = array('Tax Class');
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']             = array('Apply discount to', 'Select how the discount is applied.');
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode']          = array('Enable coupon code', 'Require a code to be entered to invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rules']['code']                = array('Rule (coupon) code', 'Please enter a code by which a customer will invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember']      = array('Uses per member', 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each customer.');
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig']      = array('Uses per store config', 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each store config.');
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity']     = array('Minimum subtotal', 'Please specify a minimum subtotal this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity']     = array('Maximum subtotal', 'Please specify a maximum subtotal this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity']     = array('Minimum item quantity', 'Please specify a minimum quantity of a an item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity']     = array('Maximum item quantity', 'Please specify a maximum quantity of a single item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']        = array('Quantity calculation mode', 'Select a calculation mode for min/max quantity.');
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate']           = array('Start date', 'If desired, please specify the date this rule will become eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate']             = array('End date', 'If desired, please specify the date this rule will no longer be eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime']           = array('Start time', 'If desired, please specify the time this rule will become eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime']             = array('End time', 'If desired, please specify the time this rule will not longer be eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions']  = array('Store config restrictions', 'Restrict a rule to certain store configs.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configCondition']     = array('Store config condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configs']             = array('Store configs', 'Select configs this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']  = array('Member restrictions', 'Restrict a rule to certain groups or members.');
$GLOBALS['TL_LANG']['tl_iso_rules']['memberCondition']     = array('Member condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rules']['members']             = array('Members', 'Select members this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['groups']              = array('Groups', 'Select groups this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions'] = array('Product restrictions', 'Restrict this rule to certain product types, categories, or to individual products.');
$GLOBALS['TL_LANG']['tl_iso_rules']['productCondition']    = array('Product condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes']        = array('Product Types', 'Select the product types this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['products']            = array('Products', 'Select products this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['variants']            = array('Products & Variants', 'Select products & variants this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeName']       = array('Attribute name', 'Select the product attribute you want to restrict.');
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']  = array('Attribute condition', 'Select the product attribute you want to restrict.');
$GLOBALS['TL_LANG']['tl_iso_rules']['pages']               = array('Categories', 'Select categories this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled']             = array('Enabled', 'Please select whether this rule is currently enabled or not.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['basic_legend']    = 'Basic rule setup';
$GLOBALS['TL_LANG']['tl_iso_rules']['coupon_legend']   = 'Coupon Code';
$GLOBALS['TL_LANG']['tl_iso_rules']['limit_legend']    = 'Limit Uses';
$GLOBALS['TL_LANG']['tl_iso_rules']['datim_legend']    = 'Date &amp; Time Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['advanced_legend'] = 'Restrictions avancées';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled_legend']  = 'Disponibilité';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['product']                     = 'Produit';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['cart']                        = 'Cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['none']          = 'Aucune restriction';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['guests']        = 'Invités';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['groups']        = 'Groupes spécifiques';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['members']       = 'Membres spécifiques';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['none']         = 'Aucune restriction';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['producttypes'] = 'Types de produits';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['pages']        = 'Catégories';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['products']     = 'Produits';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['variants']     = 'Products &amp; Variants';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['attribute']    = 'Product attribute';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['all']             = 'Exclure toutes les autres règles';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['none']            = 'Aucune exclusion de règles';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['rules']           = 'Exclure certaines règles';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['products']                 = 'to each product';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['items']                    = 'to each unit of a product';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['subtotal']                 = 'to the cart subtotal';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['product_quantity']    = 'Quantity of product in cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_products']       = 'Total products in cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_items']          = 'Total quantity in cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['condition_true']                      = 'vrai';
$GLOBALS['TL_LANG']['tl_iso_rules']['condition_false']                     = 'faux';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['eq']            = 'est égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['neq']           = 'n\'est pas égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['lt']            = 'inférieur à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['gt']            = 'supérieur à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['elt']           = 'inférieur ou égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['egt']           = 'supérieur ou égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['starts']        = 'débute par';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['ends']          = 'se termine par';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['contains']      = 'contient';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['new']    = array('Ajouter une règle', 'Créer une nouvelle règle');
$GLOBALS['TL_LANG']['tl_iso_rules']['edit']   = array('Éditer une règle', 'Éditer la règle ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['copy']   = array('Dupliquer une règle', 'Dupliquer la règle ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['delete'] = array('Supprimer une règle', 'Supprimer la règle ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['show']   = array('Rule details', 'Show the details of rule ID %s');

