<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 * 
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/fr/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_rule']['type'][0] = 'Type';
$GLOBALS['TL_LANG']['tl_iso_rule']['type'][1] = 'Veuillez, s\'il vous plaît, choisir le type de règle.';
$GLOBALS['TL_LANG']['tl_iso_rule']['type']['product'] = 'Produit';
$GLOBALS['TL_LANG']['tl_iso_rule']['type']['cart'] = 'Panier';
$GLOBALS['TL_LANG']['tl_iso_rule']['name'][0] = 'Nom';
$GLOBALS['TL_LANG']['tl_iso_rule']['name'][1] = 'Veuillez, s\'il vous plaît, insérer un nom pour cette règle.';
$GLOBALS['TL_LANG']['tl_iso_rule']['label'][0] = 'Label';
$GLOBALS['TL_LANG']['tl_iso_rule']['label'][1] = 'Le label sera affiché dans le panier. Si vous n\'entrez pas un label, le nom sera utilisé.';
$GLOBALS['TL_LANG']['tl_iso_rule']['discount'][0] = 'Discount';
$GLOBALS['TL_LANG']['tl_iso_rule']['discount'][1] = 'Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.';
$GLOBALS['TL_LANG']['tl_iso_rule']['tax_class'][0] = 'Taxe';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo'][0] = 'Appliquer la réduction à';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo'][1] = 'Sélectionnez la façon dont la réduction est appliquée.';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']['products'] = 'à chaque produit';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']['items'] = 'à chaque unité d\'un produit';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']['subtotal'] = 'to the cart subtotal';
$GLOBALS['TL_LANG']['tl_iso_rule']['enableCode'][0] = 'Enable coupon code';
$GLOBALS['TL_LANG']['tl_iso_rule']['enableCode'][1] = 'Require a code to be entered to invoke this rule, as a coupon.';
$GLOBALS['TL_LANG']['tl_iso_rule']['code'][0] = 'Rule (coupon) code';
$GLOBALS['TL_LANG']['tl_iso_rule']['code'][1] = 'Please enter a code by which a customer will invoke this rule, as a coupon.';
$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerMember'][0] = 'Uses per member';
$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerMember'][1] = 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each customer.';
$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerConfig'][0] = 'Uses per store config';
$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerConfig'][1] = 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each store config.';
$GLOBALS['TL_LANG']['tl_iso_rule']['minSubtotal'][0] = 'Sous-total minimum';
$GLOBALS['TL_LANG']['tl_iso_rule']['minSubtotal'][1] = 'Indiquer un sous total minimum auquel s\'applique cette règle.';
$GLOBALS['TL_LANG']['tl_iso_rule']['maxSubtotal'][0] = 'Sous-total maximum';
$GLOBALS['TL_LANG']['tl_iso_rule']['maxSubtotal'][1] = 'Indiquer un sous total maximum auquel s\'applique cette règle.';
$GLOBALS['TL_LANG']['tl_iso_rule']['minItemQuantity'][0] = 'Minimum item quantity';
$GLOBALS['TL_LANG']['tl_iso_rule']['minItemQuantity'][1] = 'Please specify a minimum quantity of an item this rule applies to.';
$GLOBALS['TL_LANG']['tl_iso_rule']['maxItemQuantity'][0] = 'Maximum item quantity';
$GLOBALS['TL_LANG']['tl_iso_rule']['maxItemQuantity'][1] = 'Please specify a maximum quantity of a single item this rule applies to.';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode'][0] = 'Quantity calculation mode';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode'][1] = 'Select a calculation mode for min/max quantity.';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']['product_quantity'] = 'Quantité de produit dans le panier';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']['cart_products'] = 'Total des produits dans le panier';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']['cart_items'] = 'Quantité totale dans le panier';
$GLOBALS['TL_LANG']['tl_iso_rule']['startDate'][0] = 'Start date';
$GLOBALS['TL_LANG']['tl_iso_rule']['startDate'][1] = 'If desired, please specify the date this rule will become eligible on.';
$GLOBALS['TL_LANG']['tl_iso_rule']['endDate'][0] = 'End date';
$GLOBALS['TL_LANG']['tl_iso_rule']['endDate'][1] = 'If desired, please specify the date this rule will no longer be eligible on.';
$GLOBALS['TL_LANG']['tl_iso_rule']['startTime'][0] = 'Start time';
$GLOBALS['TL_LANG']['tl_iso_rule']['startTime'][1] = 'If desired, please specify the time this rule will become eligible at.';
$GLOBALS['TL_LANG']['tl_iso_rule']['endTime'][0] = 'End time';
$GLOBALS['TL_LANG']['tl_iso_rule']['endTime'][1] = 'If desired, please specify the time this rule will not longer be eligible at.';
$GLOBALS['TL_LANG']['tl_iso_rule']['configRestrictions'][0] = 'Store config restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['configRestrictions'][1] = 'Restrict a rule to certain store configs.';
$GLOBALS['TL_LANG']['tl_iso_rule']['configCondition'][0] = 'Store config condition';
$GLOBALS['TL_LANG']['tl_iso_rule']['configCondition'][1] = 'Define if the selection should match or not.';
$GLOBALS['TL_LANG']['tl_iso_rule']['configs'][0] = 'Store configs';
$GLOBALS['TL_LANG']['tl_iso_rule']['configs'][1] = 'Select configs this rule is restricted to.';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions'][0] = 'Member restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions'][1] = 'Restrict a rule to certain groups or members.';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['none'] = 'Aucune restriction';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['guests'] = 'Invités';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['groups'] = 'Groupes spécifiques';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['members'] = 'Membres spécifiques';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberCondition'][0] = 'Member condition';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberCondition'][1] = 'Define if the selection should match or not.';
$GLOBALS['TL_LANG']['tl_iso_rule']['members'][0] = 'Members';
$GLOBALS['TL_LANG']['tl_iso_rule']['members'][1] = 'Select members this rule is restricted to.';
$GLOBALS['TL_LANG']['tl_iso_rule']['groups'][0] = 'Groups';
$GLOBALS['TL_LANG']['tl_iso_rule']['groups'][1] = 'Select groups this rule is restricted to.';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions'][0] = 'Product restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions'][1] = 'Restrict this rule to certain product types, categories, or to individual products.';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['none'] = 'Aucune restriction';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['producttypes'] = 'Types de produits';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['pages'] = 'Catégories';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['products'] = 'Produits';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['variants'] = 'Produits &amp; Variantes';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['attribute'] = 'Product attribute';
$GLOBALS['TL_LANG']['tl_iso_rule']['productCondition'][0] = 'Product condition';
$GLOBALS['TL_LANG']['tl_iso_rule']['productCondition'][1] = 'Define if the selection should match or not.';
$GLOBALS['TL_LANG']['tl_iso_rule']['producttypes'][0] = 'Product Types';
$GLOBALS['TL_LANG']['tl_iso_rule']['producttypes'][1] = 'Select the product types this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rule']['products'][0] = 'Products';
$GLOBALS['TL_LANG']['tl_iso_rule']['products'][1] = 'Select products this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rule']['variants'][0] = 'Products & Variants';
$GLOBALS['TL_LANG']['tl_iso_rule']['variants'][1] = ' Saisir une liste séparée par des virgules des ID de produits ou variantes limitant cette règle.';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeName'][0] = 'Attribute name';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeName'][1] = 'Select the product attribute you want to restrict.';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition'][0] = 'Attribute condition';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition'][1] = 'Select the product attribute you want to restrict.';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['eq'] = 'est égal à';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['neq'] = 'n\'est pas égal à';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['lt'] = 'inférieur à';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['gt'] = 'supérieur à';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['elt'] = 'inférieur ou égal à';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['egt'] = 'supérieur ou égal à';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['starts'] = 'débute par';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['ends'] = 'se termine par';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['contains'] = 'contient';
$GLOBALS['TL_LANG']['tl_iso_rule']['pages'][0] = 'Categories';
$GLOBALS['TL_LANG']['tl_iso_rule']['pages'][1] = 'Select categories this rule is restricted to. If none, all are eligible.';
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled'][0] = 'Enabled';
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled'][1] = 'Please select whether this rule is currently enabled or not.';
$GLOBALS['TL_LANG']['tl_iso_rule']['basic_legend'] = 'Basic rule setup';
$GLOBALS['TL_LANG']['tl_iso_rule']['coupon_legend'] = 'Coupon Code';
$GLOBALS['TL_LANG']['tl_iso_rule']['limit_legend'] = 'Limit Uses';
$GLOBALS['TL_LANG']['tl_iso_rule']['datim_legend'] = 'Date &amp; Time Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['advanced_legend'] = 'Restrictions avancées';
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled_legend'] = 'Disponibilité';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['all'] = 'Exclure toutes les autres règles';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['none'] = 'Aucune exclusion de règles';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['rules'] = 'Exclure certaines règles';
$GLOBALS['TL_LANG']['tl_iso_rule']['condition_true'] = 'vrai';
$GLOBALS['TL_LANG']['tl_iso_rule']['condition_false'] = 'faux';
$GLOBALS['TL_LANG']['tl_iso_rule']['new'][0] = 'Créer une règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['new'][1] = 'Créer une nouvelle règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['edit'][0] = 'Éditer une règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['edit'][1] = 'Éditer la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rule']['copy'][0] = 'Dupliquer une règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['copy'][1] = 'Dupliquer la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rule']['delete'][0] = 'Supprimer une règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['delete'][1] = 'Supprimer la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rule']['toggle'][0] = 'Publier/dé-publier une règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['toggle'][1] = 'Publier/dé-publier la règle ID %s';
$GLOBALS['TL_LANG']['tl_iso_rule']['show'][0] = 'Détails de la règle';
$GLOBALS['TL_LANG']['tl_iso_rule']['show'][1] = 'Afficher les détails de la règle ID %s';
