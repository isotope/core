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
$GLOBALS['TL_LANG']['tl_product_attributes']['name']					= array('Name', 'Please enter a name for this attribute.');
$GLOBALS['TL_LANG']['tl_product_attributes']['description']				= array('Description', 'Please enter a description for this attribute.');
$GLOBALS['TL_LANG']['tl_product_attributes']['legend']					= array('Field Group', 'Select a field group that this attribute relates to (used to organize related fields into collapsible fieldset groups when editing products.');
$GLOBALS['TL_LANG']['tl_product_attributes']['field_name']				= array('Internal name', 'Internal field name must be unique to this attribute set. Some field labels are mandatory (product name etc.) and cannot be edited.');
$GLOBALS['TL_LANG']['tl_product_attributes']['type']					= array('Type', 'Please select a type for this attribute.');
$GLOBALS['TL_LANG']['tl_product_attributes']['option_list']				= array('Options', 'Please enter one or more options. Use the buttons to add, move or delete an option. If you are working without JavaScript assistance, you should save your changes before you modify the order!');
$GLOBALS['TL_LANG']['tl_product_attributes']['show_files']				= array('Show Files','If checked, users will be able to select individual files, if false, they may only select folders.');
$GLOBALS['TL_LANG']['tl_product_attributes']['attr_use_mode']			= array('Attribute Use Mode','');
$GLOBALS['TL_LANG']['tl_product_attributes']['attr_default_value']		= array('Default Value','Set a default value for this attribute which can be overridden later.');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_visible_on_front']		= array('Visible to Customers','Is this attribute shown to customers?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_customer_defined']		= array('Defined by Customer','Is this value defined by the customer?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_required']				= array('Required','Is this attribute required?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_filterable']			= array('Filterable','Can this attribute be used in a filter?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_searchable']			= array('Searchable','Should the search engine look in this field for search terms?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_multiple_select']		= array('Allow Multiple Selections','Stores multiple selections instead of single ones.');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_used_for_price_rules']	= array('Used for price or shopping cart rules','Will this attribute be used to create price or cart rules?');
$GLOBALS['TL_LANG']['tl_product_attributes']['use_rich_text_editor']	= array('Use Full HTML Editor','Allows you to include html in your description.');
$GLOBALS['TL_LANG']['tl_product_attributes']['field_size']				= array('Field Size','How many characters should this field accommodate? Leave blank for unlimited.');
$GLOBALS['TL_LANG']['tl_product_attributes']['storeTable']				= array('Storage Table','What table does this attribute produce a field for?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_listing_field']		= array('Used as a Listing Field','Is this field to be displayed in the product listing?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_order_by_enabled']		= array('Add to "Order By" option list','This field will be sortable in the listing module provided the attribute is visible to customers.');
$GLOBALS['TL_LANG']['tl_product_attributes']['multilingual']			= array('Multilingual', 'Check here if this field should be translated.');
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxp']					= array('Check Field Values', 'Ensure user input is valid based on a rule you select.');
$GLOBALS['TL_LANG']['tl_product_attributes']['list_source_table']		= array('List Source Table','the table selected will be used to populate the list using the ID value of this table');
$GLOBALS['TL_LANG']['tl_product_attributes']['list_source_field']		= array('List Source Field','this field will be the label for each value that comes from the list source table.');
$GLOBALS['TL_LANG']['tl_product_attributes']['use_alternate_source']	= array('Alternate List Source','Check this if you would like to use a database table as the data source.');
$GLOBALS['TL_LANG']['tl_product_attributes']['text_collection_rows']	= array('Number of Text Box Rows','Number of text box controls to provide the customer for leaving additional text information related to the product purchase.');
$GLOBALS['TL_LANG']['tl_product_attributes']['conditionField']			= array('Parent Field', 'Please select the parent field, which must be of type "Select-Menu". For parent-child relation to work, define each option of this parent field as group of the conditional select-menu.');

$GLOBALS['TL_LANG']['tl_product_attributes']['template_key']			= 'Template Key: ';
$GLOBALS['TL_LANG']['tl_product_attributes']['template_visibility_title']	= 'Available In The Following Templates: ';

$GLOBALS['TL_LANG']['tl_product_attributes']['listing_enabled']			= 'Product Lister Template';
$GLOBALS['TL_LANG']['tl_product_attributes']['reader_enabled']			= 'Product Reader Template';
$GLOBALS['TL_LANG']['tl_product_attributes']['add_to_product_variants']	= array('Add to product variants wizard','If selected, this attribute will be added to the product variants wizard for use as a product variant option.');



/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['alnum']	= 'Numbers and Letters Only';
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['extnd']	= 'All standard characters except: #&()/>=<';
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['email']	= 'Valid email address';
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['url']		= 'Valid html link';
$GLOBALS['TL_LANG']['tl_product_attributes']['opValue']					= 'Value';
$GLOBALS['TL_LANG']['tl_product_attributes']['opLabel']					= 'Label';
$GLOBALS['TL_LANG']['tl_product_attributes']['opDefault']				= 'Default';
$GLOBALS['TL_LANG']['tl_product_attributes']['opGroup']					= 'Group';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['new']    					= array('New Attribute', 'Create a new attribute.');
$GLOBALS['TL_LANG']['tl_product_attributes']['edit']   					= array('Edit Attribute', 'Edit attribute ID %s.');
$GLOBALS['TL_LANG']['tl_product_attributes']['copy']   					= array('Copy Attribute', 'Copy attribute ID %s.');
$GLOBALS['TL_LANG']['tl_product_attributes']['delete'] 					= array('Delete Attribute', 'Delete attribute ID %s. The database column is not dropped, you need to manually update the database using the install tool or repository manager.');
$GLOBALS['TL_LANG']['tl_product_attributes']['show']   					= array('Show Attribute Details', 'Show details for attribute ID %s.');
$GLOBALS['TL_LANG']['tl_product_attributes']['deleteConfirm'] 			= 'Do you really want to delete ID %s. Database field is not dropped, you need to manually update the database using the install tool or repository manager.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['attribute_legend']		= 'Attribut name & type';
$GLOBALS['TL_LANG']['tl_product_attributes']['options_legend']			= 'Option Collection Data';
$GLOBALS['TL_LANG']['tl_product_attributes']['visibility_legend']		= 'Visibility Settings';
$GLOBALS['TL_LANG']['tl_product_attributes']['use_mode_legend']			= 'Use Mode';
$GLOBALS['TL_LANG']['tl_product_attributes']['search_filters_legend']	= 'Search & Filtering Settings';
$GLOBALS['TL_LANG']['tl_product_attributes']['validation_legend']		= 'Validation Settings';

