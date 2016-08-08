<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['type']                                 = array('Type', 'Please choose the type of rule.');
$GLOBALS['TL_LANG']['tl_iso_rule']['name']                                 = array('Name', 'Please enter a name for this rule.');
$GLOBALS['TL_LANG']['tl_iso_rule']['label']                                = array('Label', 'The label will be show in cart. If you do not enter a label, the name will be used.');
$GLOBALS['TL_LANG']['tl_iso_rule']['discount']                             = array('Discount', 'Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.');
$GLOBALS['TL_LANG']['tl_iso_rule']['tax_class']                            = array('Tax Class');
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']                              = array('Apply discount to', 'Select how the discount is applied.');
$GLOBALS['TL_LANG']['tl_iso_rule']['enableCode']                           = array('Enable coupon code', 'Require a code to be entered to invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rule']['code']                                 = array('Rule (coupon) code', 'Please enter a code by which a customer will invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerMember']                       = array('Uses per member', 'This will be used to see if the rule has already been redeemed. If this is set to 0, it can be used unlimited times for each customer.');
$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerConfig']                       = array('Uses per store config', 'This will be used to see if the rule has already been redeemed. If this is set to 0, it can be used unlimited times for each store config.');
$GLOBALS['TL_LANG']['tl_iso_rule']['minSubtotal']                          = array('Minimum subtotal', 'Please specify a minimum subtotal this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['maxSubtotal']                          = array('Maximum subtotal', 'Please specify a maximum subtotal this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['minWeight']                            = array('Minimum weight', 'Enter a minimum weight to control availability based on the products in cart.');
$GLOBALS['TL_LANG']['tl_iso_rule']['maxWeight']                            = array('Maximum weight', 'Enter a maximum weight to control availability based on the products in cart.');
$GLOBALS['TL_LANG']['tl_iso_rule']['minItemQuantity']                      = array('Minimum item quantity', 'Please specify a minimum quantity of an item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['maxItemQuantity']                      = array('Maximum item quantity', 'Please specify a maximum quantity of a single item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']                         = array('Quantity calculation mode', 'Select a calculation mode for min/max quantity.');
$GLOBALS['TL_LANG']['tl_iso_rule']['startDate']                            = array('Start date', 'If desired, please specify the date this rule will become eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rule']['endDate']                              = array('End date', 'If desired, please specify the date this rule will no longer be eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rule']['startTime']                            = array('Start time', 'If desired, please specify the time this rule will become eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rule']['endTime']                              = array('End time', 'If desired, please specify the time this rule will not longer be eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rule']['configRestrictions']                   = array('Store config restrictions', 'Restrict a rule to certain store configs.');
$GLOBALS['TL_LANG']['tl_iso_rule']['configCondition']                      = array('Store config condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rule']['configs']                              = array('Store configs', 'Select configs this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']                   = array('Member restrictions', 'Restrict a rule to certain groups or members.');
$GLOBALS['TL_LANG']['tl_iso_rule']['memberCondition']                      = array('Member condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rule']['members']                              = array('Members', 'Select members this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['groups']                               = array('Groups', 'Select groups this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']                  = array('Product restrictions', 'Restrict this rule to certain product types, categories, or to individual products.');
$GLOBALS['TL_LANG']['tl_iso_rule']['productCondition']                     = array('Product condition', 'Define if the selection should match or not.');
$GLOBALS['TL_LANG']['tl_iso_rule']['producttypes']                         = array('Product Types', 'Select the product types this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['products']                             = array('Products', 'Select products this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['variants']                             = array('Products & Variants', 'Enter a comma-separated list of product or variant IDs to limit this rule to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeName']                        = array('Attribute name', 'Select the product attribute you want to restrict.');
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']                   = array('Attribute condition', 'Select the product attribute you want to restrict.');
$GLOBALS['TL_LANG']['tl_iso_rule']['pages']                                = array('Categories', 'Select categories this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled']                              = array('Enabled', 'Please select whether this rule is currently enabled or not.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['basic_legend']                         = 'Basic rule setup';
$GLOBALS['TL_LANG']['tl_iso_rule']['coupon_legend']                        = 'Coupon Code';
$GLOBALS['TL_LANG']['tl_iso_rule']['limit_legend']                         = 'Limit Uses';
$GLOBALS['TL_LANG']['tl_iso_rule']['datim_legend']                         = 'Date &amp; Time Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['advanced_legend']                      = 'Advanced Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled_legend']                       = 'Availability';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['type']['product']                      = 'Product';
$GLOBALS['TL_LANG']['tl_iso_rule']['type']['cart']                         = 'Cart';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['none']           = 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['guests']         = 'Guests';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['groups']         = 'Specific groups';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['members']        = 'Specific members';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['none']          = 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['producttypes']  = 'Product types';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['pages']         = 'Categories';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['products']      = 'Products';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['variants']      = 'Products &amp; Variants';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['attribute']     = 'Product attribute';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['all']              = 'Exclude all other rules';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['none']             = 'No rule exclusions';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['rules']            = 'Exclude certain rules';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']['products']                  = 'to each product';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']['items']                     = 'to each unit of a product';
$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo']['subtotal']                  = 'to the cart subtotal';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']['product_quantity']     = 'Quantity of product in cart';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']['cart_products']        = 'Total products in cart';
$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode']['cart_items']           = 'Total quantity in cart';
$GLOBALS['TL_LANG']['tl_iso_rule']['condition_true']                       = 'true';
$GLOBALS['TL_LANG']['tl_iso_rule']['condition_false']                      = 'false';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['eq']             = 'equals';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['neq']            = 'not equal';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['lt']             = 'less than';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['gt']             = 'greater than';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['elt']            = 'less than or equal to';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['egt']            = 'greater than or equal to';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['starts']         = 'starts with';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['ends']           = 'ends with';
$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition']['contains']       = 'contains';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['new']                                  = array('Add rule', 'Create a new rule');
$GLOBALS['TL_LANG']['tl_iso_rule']['edit']                                 = array('Edit rule', 'Edit rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['copy']                                 = array('Duplicate rule', 'Duplicate rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['delete']                               = array('Delete rule', 'Delete rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['toggle']                               = array('Publish/unpublish rule', 'Publish/unpublish rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['show']                                 = array('Rule details', 'Show the details of rule ID %s');
