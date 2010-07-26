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
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.typolight.org>
 * @package    Faq
 * @license    LGPL
 * @filesource
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['title']          		= array('Title', 'Please enter the rule title.');
$GLOBALS['TL_LANG']['tl_iso_rule']['type']					= array('Type','Please choose the type of rule.');
$GLOBALS['TL_LANG']['tl_iso_rule']['collectionType']		= array('Collection (Cart) type','Please select the type of product collection object to restrict this rule to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['description']       	= array('Description', 'Please enter the rule description.');
$GLOBALS['TL_LANG']['tl_iso_rule']['discount']				= array('Discount','Valid values are decimals or whole numbers, minus a numerical value or minus a percentage.');
$GLOBALS['TL_LANG']['tl_iso_rule']['enableCode']			= array('Enable coupon code','Require a code to be entered to invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rule']['code']					= array('Rule (coupon) code','Please enter a code by which a customer will invoke this rule, as a coupon.');
$GLOBALS['TL_LANG']['tl_iso_rule']['numUses']       		= array('Number of uses', 'This will be used to see if the rule has already been redeemed.  If this is set to 0, it can be used unlimited times for each customer.');
$GLOBALS['TL_LANG']['tl_iso_rule']['minSubTotal']			= array('Minimum Subtotal','Specify a minimum subtotal for items in cart that this rule can be applied to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['minCartQuantity']		= array('Minimum Cart Quantity','Specify a minimum quantity of items in cart that this rule can be applied to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['maxCartQuantity']		= array('Maximum Cart Quantity','Specify a maximum quantity of items in cart that this rule can be applied to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['minItemQuantity']		= array('Minimum item quantity','Please specify a minimum quantity of a an item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['maxItemQuantity']		= array('Maximum item quantity','Please specify a maximum quantity of a single item this rule applies to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['collectionTypeRestrictions']  	= array('Collection (Cart) type restrictions', 'Restrict a rule to apply only to a certain collection (cart) type.');

$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']  	= array('Rule restrictions', 'Restrict a rule to be only usable alone or with certain rule.');
$GLOBALS['TL_LANG']['tl_iso_rule']['rules']     			= array('Rules', 'Select other rule this rule is usable with, or you may indicate that this rule is usable with no other rule.');
$GLOBALS['TL_LANG']['tl_iso_rule']['dateRestrictions']		= array('Date restrictions','If desired, please specify a start and end date this rule is eligible for.');
$GLOBALS['TL_LANG']['tl_iso_rule']['timeRestrictions']		= array('Time restrictions','If desired, please specify a start and end time this rule is eligible for.');
$GLOBALS['TL_LANG']['tl_iso_rule']['startDate']      		= array('Start date', 'If desired, please specify the date this rule will become eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rule']['endDate']        		= array('End date', 'If desired, please specify the date this rule will no longer be eligible on.');
$GLOBALS['TL_LANG']['tl_iso_rule']['protected']				= array('Protected','Further restrict the eligibility properties of this rule.');

$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']	= array('Member restrictions','Restrict a rule to certain groups or members');
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']	= array('Product restrictions','Restrict this rule to certain product types, categories, or to individual products.');
$GLOBALS['TL_LANG']['tl_iso_rule']['members']        		= array('Members', 'Select members this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['groups']         		= array('Groups', 'Select groups this rule is restricted to.');
$GLOBALS['TL_LANG']['tl_iso_rule']['pages']     			= array('Categories', 'Select categories this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['products']				= array('Products','Select products this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['productTypes']			= array('Product Types','Select the product types this rule is restricted to. If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['countries']				= array('Countries','Select countries this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['subdivisions']			= array('States & Provinces','Select states or provinces within countries this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['paymentModules']		= array('Payment Modules','Select payment modules this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['shippingModules']		= array('Shipping Modules','Select shipping modules this rule is restricted to.  If none, all are eligible.');
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled']				= array('Enabled','Please select whether this rule is currently enabled or not.');
/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['general_legend']			= 'General Information';
$GLOBALS['TL_LANG']['tl_iso_rule']['type_legend']				= 'Rule Type';
$GLOBALS['TL_LANG']['tl_iso_rule']['restriction_legend']		= 'Rule Restrictions'; 
$GLOBALS['TL_LANG']['tl_iso_rule']['enabled_legend']			= 'Module Enabling Details';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_rule']['type']['product']						= 'Product';
$GLOBALS['TL_LANG']['tl_iso_rule']['type']['product_collection']			= 'Product Collection';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['none']			= 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['groups']			= 'Specific groups';
$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']['members']			= 'Specific members';

$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['none']			= 'No restrictions';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['productTypes']	= 'Product types';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['pages']			= 'Specific categories';
$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']['products']		= 'Specific products';

$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['all']				= 'Exclude all other rules';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['none']				= 'No rule exclusions';
$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']['rules']				= 'Exclude certain rules';

$GLOBALS['TL_LANG']['tl_iso_rule']['numUses']['customer']					= 'Per customer';
$GLOBALS['TL_LANG']['tl_iso_rule']['numUses']['store']						= 'Per store';


/**
 * Buttons
 */

$GLOBALS['TL_LANG']['tl_iso_rule']['new']        = array('New rule', 'Create a new rule');
$GLOBALS['TL_LANG']['tl_iso_rule']['show']       = array('Rule details', 'Show the details of rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['edit']       = array('Edit rule', 'Edit rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['editheader'] = array('Edit rule settings', 'Edit the settings of rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['copy']       = array('Duplicate rule', 'Duplicate rule ID %s');
$GLOBALS['TL_LANG']['tl_iso_rule']['delete']     = array('Delete rule', 'Delete rule ID %s');

?>