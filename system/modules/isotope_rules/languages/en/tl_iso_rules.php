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
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['type']					= array('Type', 'Please choose the type of rule.');
$GLOBALS['TL_LANG']['tl_iso_rules']['name']					= array('Name', 'Please enter a name for this rule.');
$GLOBALS['TL_LANG']['tl_iso_rules']['label']				= array('Label', 'The label will be show in cart. If you do not enter a label, the name will be used.');
$GLOBALS['TL_LANG']['tl_iso_rules']['discount']				= array('Discount', 'Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.');
$GLOBALS['TL_LANG']['tl_iso_rules']['tax_class']			= array('Tax Class');
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']				= array('Apply discount to', 'Select how the discount is applied.');
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode']			= array('Enable coupon code', 'Require a code to be entered to invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rules']['code']					= array('Rule (coupon) code', 'Please enter a code by which a customer will invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember']		= array('Uses per member', 'This will be used to see if the rule has already been redeemed. If this is set to 0, it can be used unlimited times for each customer.');
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig']		= array('Uses per store config', 'This will be used to see if the rule has already been redeemed. If this is set to 0, it can be used unlimited times for each store config.');
$GLOBALS['TL_LANG']['tl_iso_rules']['minSubtotal']		    = array('Minimum subtotal', 'Please specify a minimum subtotal this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['maxSubtotal']		    = array('Maximum subtotal', 'Please specify a maximum subtotal this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity']		= array('Minimum item quantity', 'Please specify a minimum quantity of an item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity']		= array('Maximum item quantity', 'Please specify a maximum quantity of a single item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']			= array('Quantity calculation mode', 'Select a calculation mode for min/max quantity.');
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate']			= array('Start date', 'If desired, please specify the date this rule will become eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate']				= array('End date', 'If desired, please specify the date this rule will no longer be eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime']			= array('Start time', 'If desired, please specify the time this rule will become eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime']				= array('End time', 'If desired, please specify the time this rule will not longer be eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions']	= array('Store config restrictions', 'Restrict a rule to certain store configs.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configCondition']		= array('Store config condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configs']				= array('Store configs', 'Select configs this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']	= array('Member restrictions', 'Restrict a rule to certain groups or members.');
$GLOBALS['TL_LANG']['tl_iso_rules']['memberCondition']		= array('Member condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rules']['members']				= array('Members', 'Select members this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['groups']				= array('Groups', 'Select groups this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']	= array('Product restrictions', 'Restrict this rule to certain product types, categories, or to individual products.');
$GLOBALS['TL_LANG']['tl_iso_rules']['productCondition']		= array('Product condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes']			= array('Product Types', 'Select the product types this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['products']				= array('Products', 'Select products this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['variants']				= array('Products & Variants', 'Select products & variants this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeName']		= array('Attribute name', 'Select the product attribute you want to restrict.');
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']	= array('Attribute condition', 'Select the product attribute you want to restrict.');
$GLOBALS['TL_LANG']['tl_iso_rules']['pages']				= array('Categories', 'Select categories this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled']				= array('Enabled', 'Please select whether this rule is currently enabled or not.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['basic_legend']				= 'Basic rule setup';
$GLOBALS['TL_LANG']['tl_iso_rules']['coupon_legend']			= 'Coupon Code';
$GLOBALS['TL_LANG']['tl_iso_rules']['limit_legend']				= 'Limit Uses';
$GLOBALS['TL_LANG']['tl_iso_rules']['datim_legend']				= 'Date &amp; Time Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['advanced_legend']			= 'Advanced Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled_legend']			= 'Availability';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['product']						= 'Product';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['cart']							= 'Cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['none']			= 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['guests']			= 'Guests';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['groups']			= 'Specific groups';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['members']		= 'Specific members';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['none']			= 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['producttypes']	= 'Product types';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['pages']			= 'Categories';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['products']		= 'Products';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['variants']		= 'Products &amp; Variants';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['attribute']		= 'Product attribute';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['all']				= 'Exclude all other rules';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['none']				= 'No rule exclusions';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['rules']			= 'Exclude certain rules';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['products']					= 'to each product';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['items']						= 'to each unit of a product';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['subtotal']					= 'to the cart subtotal';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['product_quantity']		= 'Quantity of product in cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_products']		= 'Total products in cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['quantityMode']['cart_items']			= 'Total quantity in cart';
$GLOBALS['TL_LANG']['tl_iso_rules']['condition_true']						= 'true';
$GLOBALS['TL_LANG']['tl_iso_rules']['condition_false']						= 'false';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['eq']				= 'equals';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['neq']			= 'not equal';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['lt']				= 'less than';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['gt']				= 'greater than';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['elt']			= 'less than or equal to';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['egt']			= 'greater than or equal to';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['starts']			= 'starts with';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['ends']			= 'ends with';
$GLOBALS['TL_LANG']['tl_iso_rules']['attributeCondition']['contains']		= 'contains';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['new']		= array('Add rule', 'Create a new rule');
$GLOBALS['TL_LANG']['tl_iso_rules']['edit']		= array('Edit rule', 'Edit rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['copy']		= array('Duplicate rule', 'Duplicate rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['delete']	= array('Delete rule', 'Delete rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['toggle']	= array('Publish/unpublish rule', 'Publish/unpublish rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['show']		= array('Rule details', 'Show the details of rule ID %s');
