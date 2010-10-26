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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['type']					= array('Type','Please choose the type of rule.');
$GLOBALS['TL_LANG']['tl_iso_rules']['name']          		= array('Name', 'Please enter a name for this rule.');
$GLOBALS['TL_LANG']['tl_iso_rules']['label']          		= array('Label', 'The label will be show in cart. If you do not enter a label, the name will be used.');
$GLOBALS['TL_LANG']['tl_iso_rules']['discount']				= array('Discount', 'Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.');
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']				= array('Apply discount to', 'Select how the discount is applied.');
$GLOBALS['TL_LANG']['tl_iso_rules']['enableCode']			= array('Enable coupon code','Require a code to be entered to invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rules']['code']					= array('Rule (coupon) code','Please enter a code by which a customer will invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerMember']		= array('Uses per member', 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each customer.');
$GLOBALS['TL_LANG']['tl_iso_rules']['limitPerConfig']		= array('Uses per store config', 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each store config.');
$GLOBALS['TL_LANG']['tl_iso_rules']['minItemQuantity']		= array('Minimum item quantity','Please specify a minimum quantity of a an item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['maxItemQuantity']		= array('Maximum item quantity','Please specify a maximum quantity of a single item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['startDate']      		= array('Start date', 'If desired, please specify the date this rule will become eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rules']['endDate']        		= array('End date', 'If desired, please specify the date this rule will no longer be eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rules']['startTime']			= array('Start time','If desired, please specify the time this rule will become eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rules']['endTime']				= array('End time','If desired, please specify the time this rule will not longer be eligible at.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configRestrictions']	= array('Store config restrictions', 'Restrict a rule to certain store configs.');
$GLOBALS['TL_LANG']['tl_iso_rules']['configs']         		= array('Store configs', 'Select configs this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']	= array('Member restrictions', 'Restrict a rule to certain groups or members.');
$GLOBALS['TL_LANG']['tl_iso_rules']['members']        		= array('Members', 'Select members this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['groups']         		= array('Groups', 'Select groups this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']	= array('Product restrictions', 'Restrict this rule to certain product types, categories, or to individual products.');
$GLOBALS['TL_LANG']['tl_iso_rules']['producttypes']			= array('Product Types','Select the product types this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['products']				= array('Products','Select products this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['pages']     			= array('Categories', 'Select categories this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled']				= array('Enabled','Please select whether this rule is currently enabled or not.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['basic_legend']				= 'Basic rule setup';
$GLOBALS['TL_LANG']['tl_iso_rules']['coupon_legend']			= 'Coupon Code';
$GLOBALS['TL_LANG']['tl_iso_rules']['limit_legend']				= 'Limit Uses';
$GLOBALS['TL_LANG']['tl_iso_rules']['datim_legend']				= 'Date & Time Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['advanced_legend']			= 'Advanced Restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['enabled_legend']			= 'Availability';


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['product']						= 'Product';
$GLOBALS['TL_LANG']['tl_iso_rules']['type']['cart']							= 'Cart';

$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['none']			= 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['groups']			= 'Specific groups';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['members']		= 'Specific members';
$GLOBALS['TL_LANG']['tl_iso_rules']['memberRestrictions']['guest']			= 'Guests Only';

$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['none']			= 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['producttypes']	= 'Product types';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['pages']			= 'Specific categories';
$GLOBALS['TL_LANG']['tl_iso_rules']['productRestrictions']['products']		= 'Specific products';

$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['all']				= 'Exclude all other rules';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['none']				= 'No rule exclusions';
$GLOBALS['TL_LANG']['tl_iso_rules']['ruleRestrictions']['rules']			= 'Exclude certain rules';

$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['product']					= 'to each product';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['item']						= 'to each unit of a product';
$GLOBALS['TL_LANG']['tl_iso_rules']['applyTo']['cart']						= 'to the cart subtotal';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_rules']['new']        = array('Add rule', 'Create a new rule');
$GLOBALS['TL_LANG']['tl_iso_rules']['edit']       = array('Edit rule', 'Edit rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['copy']       = array('Duplicate rule', 'Duplicate rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['delete']     = array('Delete rule', 'Delete rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rules']['show']       = array('Rule details', 'Show the details of rule ID %s');

