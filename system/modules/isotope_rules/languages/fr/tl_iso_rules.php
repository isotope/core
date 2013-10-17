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
 * @link https://www.transifex.com/projects/i/isotope/language/fr/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_LANG']['tl_iso_rules']['type'][0] = 'Type';
$GLOBALS['TL_LANG']['tl_iso_rules']['type'][1] = 'Veuillez, s\'il vous plaît, choisir le type de règle.';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['product'] = 'Produit';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['cart'] = 'Panier';
$GLOBALS['TL_LANG']['tl_iso_rules']['name'][0] = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_rules']['name'][1] = 'Veuillez, s\'il vous plaît, insérer un nom pour cette règle.';
$GLOBALS['TL_LANG']['tl_iso_rules']['label'][0] = 'Label';
$GLOBALS['TL_LANG']['tl_iso_rules']['label'][1] = 'Le label sera affiché dans le panier. Si vous n\'entrez pas un label, le nom sera utilisé.';
$GLOBALS['TL_LANG']['tl_iso_rules']['discount'][0] = 'Discount';
$GLOBALS['TL_LANG']['tl_iso_rules']['discount'][1] = 'Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.';
$GLOBALS['TL_LANG']['tl_iso_rules']['tax_class'][0] = 'Taxe';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo'][0] = 'Appliquer la réduction à';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo'][1] = 'Sélectionnez la façon dont la réduction est appliquée.';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['products'] = 'à chaque produit';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['items'] = 'à chaque unité d\'un produit';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['subtotal'] = 'to the cart subtotal';
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode'][0] = 'Enable coupon code';
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode'][1] = 'Require a code to be entered to invoke this rule, as a coupon.';
$GLOBALS['TL_LANG']['tl_iso_rules']['code'][0] = 'Rule (coupon) code';
$GLOBALS['TL_LANG']['tl_iso_rules']['code'][1] = 'Please enter a code by which a customer will invoke this rule, as a coupon.';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember'][0] = 'Uses per member';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember'][1] = 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each customer.';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig'][0] = 'Uses per store config';
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig'][1] = 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each store config.';
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity'][0] = 'Minimum item quantity';
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity'][1] = 'Please specify a minimum quantity of an item this rule applies to.';
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity'][0] = 'Maximum item quantity';
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity'][1] = 'Please specify a maximum quantity of a single item this rule applies to.';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode'][0] = 'Quantity calculation mode';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode'][1] = 'Select a calculation mode for min/max quantity.';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['product_quantity'] = 'Quantité de produit dans le panier';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_products'] = 'Total des produits dans le panier';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_items'] = 'Quantité totale dans le panier';
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate'][0] = 'Start date';
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate'][1] = 'If desired, please specify the date this rule will become eligible on.';
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate'][0] = 'End date';
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate'][1] = 'If desired, please specify the date this rule will no longer be eligible on.';
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime'][0] = 'Start time';
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime'][1] = 'If desired, please specify the time this rule will become eligible at.';
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime'][0] = 'End time';
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime'][1] = 'If desired, please specify the time this rule will not longer be eligible at.';
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions'][0] = 'Store config restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions'][1] = 'Restrict a rule to certain store configs.';
$GLOBALS['TL_LANG']['tl_iso_rules']['configCondition'][0] = 'Store config condition';
$GLOBALS['TL_LANG']['tl_iso_rules']['configCondition'][1] = 'Define if the selection should match or not.';
$GLOBALS['TL_LANG']['tl_iso_rules']['configs'][0] = 'Store configs';
$GLOBALS['TL_LANG']['tl_iso_rules']['configs'][1] = 'Select configs this rule is restricted to.';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions'][0] = 'Member restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions'][1] = 'Restrict a rule to certain groups or members.';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['none'] = 'Aucune restriction';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['guests'] = 'Invités';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['groups'] = 'Groupes spécifiques';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['members'] = 'Membres spécifiques';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberCondition'][0] = 'Member condition';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberCondition'][1] = 'Define if the selection should match or not.';
$GLOBALS['TL_LANG']['tl_iso_rules']['members'][0] = 'Members';
$GLOBALS['TL_LANG']['tl_iso_rules']['members'][1] = 'Select members this rule is restricted to.';
$GLOBALS['TL_LANG']['tl_iso_rules']['groups'][0] = 'Groups';
$GLOBALS['TL_LANG']['tl_iso_rules']['groups'][1] = 'Select groups this rule is restricted to.';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions'][0] = 'Product restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions'][1] = 'Restrict this rule to certain product types, categories, or to individual products.';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['none'] = 'Aucune restriction';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['producttypes'] = 'Types de produits';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['pages'] = 'Catégories';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['products'] = 'Produits';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['variants'] = 'Produits &amp; Variantes';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['attribute'] = 'Product attribute';
$GLOBALS['TL_LANG']['tl_iso_rules']['productCondition'][0] = 'Product condition';
$GLOBALS['TL_LANG']['tl_iso_rules']['productCondition'][1] = 'Define if the selection should match or not.';
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes'][0] = 'Product Types';
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes'][1] = 'Select the product types this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rules']['products'][0] = 'Products';
$GLOBALS['TL_LANG']['tl_iso_rules']['products'][1] = 'Select products this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rules']['variants'][0] = 'Products & Variants';
$GLOBALS['TL_LANG']['tl_iso_rules']['variants'][1] = 'Select products & variants this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeName'][0] = 'Attribute name';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeName'][1] = 'Select the product attribute you want to restrict.';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition'][0] = 'Attribute condition';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition'][1] = 'Select the product attribute you want to restrict.';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['eq'] = 'est égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['neq'] = 'n\'est pas égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['lt'] = 'inférieur à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['gt'] = 'supérieur à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['elt'] = 'inférieur ou égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['egt'] = 'supérieur ou égal à';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['starts'] = 'débute par';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['ends'] = 'se termine par';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['contains'] = 'contient';
$GLOBALS['TL_LANG']['tl_iso_rules']['pages'][0] = 'Categories';
$GLOBALS['TL_LANG']['tl_iso_rules']['pages'][1] = 'Select categories this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled'][0] = 'Enabled';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled'][1] = 'Please select whether this rule is currently enabled or not.';
$GLOBALS['TL_LANG']['tl_iso_rules']['basic_legend'] = 'Basic rule setup';
$GLOBALS['TL_LANG']['tl_iso_rules']['coupon_legend'] = 'Coupon Code';
$GLOBALS['TL_LANG']['tl_iso_rules']['limit_legend'] = 'Limit Uses';
$GLOBALS['TL_LANG']['tl_iso_rules']['datim_legend'] = 'Date &amp; Time Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['advanced_legend'] = 'Restrictions avancées';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled_legend'] = 'Disponibilité';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['all'] = 'Exclure toutes les autres règles';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['none'] = 'Aucune exclusion de règles';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['rules'] = 'Exclure certaines règles';
$GLOBALS['TL_LANG']['tl_iso_rules']['condition_true'] = 'vrai';
$GLOBALS['TL_LANG']['tl_iso_rules']['condition_false'] = 'faux';
$GLOBALS['TL_LANG']['tl_iso_rules']['new'][0] = 'Créer une règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['new'][1] = 'Créer une nouvelle règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['edit'][0] = 'Éditer une règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['edit'][1] = 'Éditer la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['copy'][0] = 'Dupliquer une règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['copy'][1] = 'Dupliquer la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['delete'][0] = 'Supprimer une règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['delete'][1] = 'Supprimer la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['toggle'][0] = 'Publier/dé-publier une règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['toggle'][1] = 'Publier/dé-publier la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rules']['show'][0] = 'Détails de la règle';
$GLOBALS['TL_LANG']['tl_iso_rules']['show'][1] = 'Afficher les détails de la règle ID %s';
