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
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['name']					= array('Name', 'Please enter a name for this attribute.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name']				= array('Internal name', 'Internal name is the database field name and must be unique.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['type']					= array('Type', 'Please select a type for this attribute.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['legend']					= array('Field Group', 'Select a field group that this attribute relates to (used to organize related fields into collapsible fieldset groups when editing products.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option']			= array('Add to product variants wizard', 'If selected, this attribute will be added to the product variants wizard for use as a product variant option.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined']		= array('Defined by Customer', 'Please select if this value defined by the customer (frontend).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['description']				= array('Description', 'The description is shown as a hint to the backend user.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['options']					= array('Options', 'Please enter one or more options. Use the buttons to add, move or delete an option. If you are working without JavaScript assistance, you should save your changes before you modify the order!');
$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory']				= array('Mandatory field', 'The field must be filled when editing a product.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple']				= array('Multiple selection', 'Allow visitors to select more than one option.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['size']					= array('List size', 'Here you can enter the size of the select box.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions']				= array('Allowed file types', 'A comma separated list of valid file extensions.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['rte']						= array('Use HTML Editor', 'Select a tinyMCE configuration file to enable the rich text editor.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual']			= array('Multilingual', 'Check here if this field should be translated.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp']					= array('Input validation', 'Validate the input against a regular expression.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength']				= array('Maximum length', 'Limit the field length to a certain number of characters (text) or bytes (file uploads).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey']				= array('Foreign Table & Field', 'Instead of adding options you can enter a table.field combination to select from database. To use multilingual foreignKeys, enter one per line and specify the language (example: en=tl_table.field)');
$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField']			= array('Parent Field', 'Please select the parent field, which must be of type "Select-Menu". For parent-child relation to work, define each option of this parent field as group of the conditional select-menu.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery']					= array('Image gallery', 'Different image galleries can be developed to present media files in a custom style.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['files']					= array('Show files', 'Show both files and folders.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['filesOnly']				= array('Files only', 'Remove the radio buttons or checkboxes next to folders.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['fieldType']				= array('Field type', 'Display radio buttons or checkboxes next to folders.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['sortBy']					= array('Order by', 'Please choose the sort order.');

$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting']				= array('Add to "Order By" option list', 'This field will be sortable in the listing module provided the attribute is visible to customers.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter']				= array('Backend Filterable', 'Can this attribute be used in a backend filter?');
$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search']				= array('Backend Searchable', 'Should the search engine look in this field for search terms?');
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter']				= array('Frontend Filterable', 'Can this attribute be used in a frontend filter?');
$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search']				= array('Frontend Searchable', 'Should the search engine look in this field for search terms?');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['opValue']					= 'Value';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opLabel']					= 'Label';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opDefault']				= 'Default';
$GLOBALS['TL_LANG']['tl_iso_attributes']['opGroup']					= 'Group';
$GLOBALS['TL_LANG']['tl_iso_attributes']['checkbox']				= 'Checkbox';
$GLOBALS['TL_LANG']['tl_iso_attributes']['radio']					= 'Radio';
$GLOBALS['TL_LANG']['tl_iso_attributes']['digit']					= array('Numeric characters', 'Allows numeric characters, minus (-), full stop (.) and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['alpha']					= array('Alphabetic characters', 'Allows alphabetic characters, minus (-), full stop (.) and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['alnum']					= array('Alphanumeric characters', 'Allows alphabetic and numeric characters, minus (-), full stop (.), underscore (_) and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['extnd']					= array('Extended alphanumeric characters', 'Allows everything except special characters which are usually encoded for security reasons (#/()<=>).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['date']					= array('Date', 'Checks whether the input matches the global date format.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['time']					= array('Time', 'Checks whether the input matches the global time format.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['datim']					= array('Date and time', 'Checks whether the input matches the global date and time format.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['phone']					= array('Phone number', 'Allows numeric characters, plus (+), minus (-), slash (/), parentheses () and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attributes']['email']					= array('E-mail address', 'Checks whether the input is a valid e-mail address.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['url']						= array('URL format', 'Checks whether the input is a valid URL.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['price']					= array('Price', 'Checks whether the input is a valid price.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['discount']				= array('Discount', 'Checks whether the input is a valid discount.<br />Example: -10%, -10, +10, +10%');
$GLOBALS['TL_LANG']['tl_iso_attributes']['surcharge']				= array('Surcharge', 'Checks whether the input is a valid surcharge.<br />Example: 10.00, 10%');
$GLOBALS['TL_LANG']['tl_iso_attributes']['name_asc']				= 'File name (ascending)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['name_desc']				= 'File name (descending)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date_asc']				= 'Date (ascending)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['date_desc']				= 'Date (descending)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['meta']					= 'Meta file (meta.txt)';
$GLOBALS['TL_LANG']['tl_iso_attributes']['random']					= 'Random order';



/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['new']						= array('New Attribute', 'Create a new attribute.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['edit']					= array('Edit Attribute', 'Edit attribute ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['copy']					= array('Copy Attribute', 'Copy attribute ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['delete']					= array('Delete Attribute', 'Delete attribute ID %s. The database column is not dropped, you need to manually update the database using the install tool or repository manager.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['show']					= array('Show Attribute Details', 'Show details for attribute ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attributes']['deleteConfirm']			= 'Do you really want to delete attribute ID %s. Database field is not dropped, you need to manually update the database using the install tool or repository manager.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_attributes']['attribute_legend']		= 'Attribute name & type';
$GLOBALS['TL_LANG']['tl_iso_attributes']['description_legend']		= 'Description';
$GLOBALS['TL_LANG']['tl_iso_attributes']['options_legend']			= 'Options';
$GLOBALS['TL_LANG']['tl_iso_attributes']['config_legend']			= 'Attribute configuration';
$GLOBALS['TL_LANG']['tl_iso_attributes']['validation_legend']		= 'Input validation';
$GLOBALS['TL_LANG']['tl_iso_attributes']['search_filters_legend']	= 'Search & Filtering Settings';

