<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_attribute']['name']                    = array('Name', 'Please enter a name for this attribute.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['field_name']              = array('Internal name', 'Internal name is the database field name and must be unique.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['type']                    = array('Type', 'Please select a type for this attribute.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['legend']                  = array('Field Group', 'Select a field group that this attribute relates to (used to organize related fields into collapsible fieldset groups when editing products.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['variant_option']          = array('Add to product variants wizard', 'If selected, this attribute will be added to the product variants wizard for use as a product variant option.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['customer_defined']        = array('Defined by customer', 'Please select if this value defined by the customer (frontend).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['description']             = array('Description', 'The description is shown as a hint to the backend user.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource']           = array('Options source', 'Choose where the options for this field should be loaded from.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']                 = array('Options', 'Please enter one or more options. Use the buttons to add, move or delete an option. If you are working without JavaScript assistance, you should save your changes before you modify the order!');
$GLOBALS['TL_LANG']['tl_iso_attribute']['mandatory']               = array('Mandatory field', 'The field must be filled when editing a product.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['multiple']                = array('Multiple selection', 'Allow visitors to select more than one option.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['size']                    = array('List size', 'Here you can enter the size of the select box.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['extensions']              = array('Allowed file types', 'A comma separated list of valid file extensions.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['rte']                     = array('Use HTML editor', 'Select a tinyMCE configuration file to enable the rich text editor.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['multilingual']            = array('Multilingual', 'Check here if this field should be translated.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['rgxp']                    = array('Input validation', 'Validate the input against a regular expression.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['placeholder']             = array('Placeholder', 'Show this text as long as the field is empty (requires HTML5).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['minlength']               = array('Minimum length', 'Require the field value to be a certain number of characters long.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['maxlength']               = array('Maximum length', 'Limit the field length to a certain number of characters (text) or bytes (file uploads).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['foreignKey']              = array('Foreign table & field', 'Instead of adding options you can enter a table.field combination to select from database. To use multilingual foreignKeys, enter one per line and specify the language (example: en=tl_table.field)');
$GLOBALS['TL_LANG']['tl_iso_attribute']['conditionField']          = array('Parent field', 'Please select the parent field, which must be of type "Select-Menu". For parent-child relation to work, define each option of this parent field as group of the conditional select-menu.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['files']                   = array('Show files', 'Show both files and folders.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['filesOnly']               = array('Files only', 'Remove the radio buttons or checkboxes next to folders.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['fieldType']               = array('Field type', 'Display radio buttons or checkboxes next to folders.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['sortBy']                  = array('Order by', 'Please choose the sort order.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['path']                    = array('Root directory', 'You can limit the file tree by defining a root directory here.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['storeFile']               = array('Store uploaded files', 'Move the uploaded files to a folder on the server.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['uploadFolder']            = array('Target folder', 'Please select the target folder from the files directory.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['useHomeDir']              = array('Use home directory', 'Store the file in the home directory if there is an authenticated user.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['doNotOverwrite']          = array('Preserve existing files', 'Add a numeric suffix to the new file if the file name already exists.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_sorting']              = array('Add to "Order By" option list', 'This field will be sortable in the listing module provided the attribute is visible to customers.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['be_filter']               = array('Backend filterable', 'Can this attribute be used in a backend filter?');
$GLOBALS['TL_LANG']['tl_iso_attribute']['be_search']               = array('Backend searchable', 'Should the field be available in the backend search?');
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_filter']               = array('Frontend filterable', 'Can this attribute be used in a frontend filter?');
$GLOBALS['TL_LANG']['tl_iso_attribute']['fe_search']               = array('Frontend searchable', 'Should the search engine look in this field for search terms?');
$GLOBALS['TL_LANG']['tl_iso_attribute']['datepicker']              = array('Date picker', 'Show a date picker for this field.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['value']        = array('Value');
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['label']        = array('Label');
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['default']      = array('Default');
$GLOBALS['TL_LANG']['tl_iso_attribute']['options']['group']        = array('Group');
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource']['table'] = 'Options Manager';
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource']['product'] = 'Product';
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource']['foreignKey'] = 'Custom database table (foreignKey)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['optionsSource']['attribute'] = 'Options Wizard (deprecated)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['checkbox']                = 'Checkbox';
$GLOBALS['TL_LANG']['tl_iso_attribute']['radio']                   = 'Radio';
$GLOBALS['TL_LANG']['tl_iso_attribute']['digit']                   = array('Numeric characters', 'Allows numeric characters, minus (-), full stop (.) and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['alpha']                   = array('Alphabetic characters', 'Allows alphabetic characters, minus (-), full stop (.) and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['alnum']                   = array('Alphanumeric characters', 'Allows alphabetic and numeric characters, minus (-), full stop (.), underscore (_) and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['extnd']                   = array('Extended alphanumeric characters', 'Allows everything except special characters which are usually encoded for security reasons (#/()<=>).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['date']                    = array('Date', 'Checks whether the input matches the global date format.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['time']                    = array('Time', 'Checks whether the input matches the global time format.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['datim']                   = array('Date and time', 'Checks whether the input matches the global date and time format.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['phone']                   = array('Phone number', 'Allows numeric characters, plus (+), minus (-), slash (/), parentheses () and space ( ).');
$GLOBALS['TL_LANG']['tl_iso_attribute']['email']                   = array('E-mail address', 'Checks whether the input is a valid e-mail address.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['url']                     = array('URL format', 'Checks whether the input is a valid URL.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['price']                   = array('Price', 'Checks whether the input is a valid price.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['discount']                = array('Discount', 'Checks whether the input is a valid discount.<br />Example: -10%, -10, +10, +10%');
$GLOBALS['TL_LANG']['tl_iso_attribute']['surcharge']               = array('Surcharge', 'Checks whether the input is a valid surcharge.<br />Example: 10.00, 10%');
$GLOBALS['TL_LANG']['tl_iso_attribute']['custom']                  = 'Custom order';
$GLOBALS['TL_LANG']['tl_iso_attribute']['name_asc']                = 'File name (ascending)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['name_desc']               = 'File name (descending)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['date_asc']                = 'Date (ascending)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['date_desc']               = 'Date (descending)';
$GLOBALS['TL_LANG']['tl_iso_attribute']['random']                  = 'Random order';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_attribute']['new']                     = array('New attribute', 'Create a new attribute.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['edit']                    = array('Edit attribute', 'Edit attribute ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['copy']                    = array('Copy attribute', 'Copy attribute ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['delete']                  = array('Delete attribute', 'Delete attribute ID %s. The database column will not be deleted, you need to manually update the database using the install tool or repository manager.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['show']                    = array('Attribute details', 'Show details for attribute ID %s.');
$GLOBALS['TL_LANG']['tl_iso_attribute']['deleteConfirm']           = 'Do you really want to delete attribute ID %s? The database column will not be deleted, you need to manually update the database using the install tool or repository manager.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_attribute']['attribute_legend']        = 'Attribute name & type';
$GLOBALS['TL_LANG']['tl_iso_attribute']['description_legend']      = 'Description';
$GLOBALS['TL_LANG']['tl_iso_attribute']['options_legend']          = 'Options';
$GLOBALS['TL_LANG']['tl_iso_attribute']['config_legend']           = 'Attribute configuration';
$GLOBALS['TL_LANG']['tl_iso_attribute']['search_filters_legend']   = 'Search & Filtering Settings';
$GLOBALS['TL_LANG']['tl_iso_attribute']['store_legend']            = 'Store file';
