<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * Language file for table tl_product__product_attributes (en).
 *
 * PHP version 5
 * @copyright  Martin Komara 2007 
 * @author     Martin Komara 
 * @package    CatalogModule 
 * @license    GPL 
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['name'] = array('Attribute Name', '');
$GLOBALS['TL_LANG']['tl_product_attributes']['description'] = array('Attribute Description', '');
$GLOBALS['TL_LANG']['tl_product_attributes']['fieldGroup'] = array('Field Group','Select a field group that this attribute relates to (used to organize related fields into collapsible fieldset groups when editing products.');
$GLOBALS['TL_LANG']['tl_product_attributes']['field_name'] = array('Internal name', 'Internal field name must be unique to this attribute set. Some field labels are mandatory (product name etc.) and cannot be edited.');
$GLOBALS['TL_LANG']['tl_product_attributes']['type'] = array('Attribute Type', '');
$GLOBALS['TL_LANG']['tl_product_attributes']['option_list'] = array('Options', 'Please enter one or more options. Use the buttons to add, move or delete an option. If you are working without JavaScript assistance, you should save your changes before you modify the order!');
$GLOBALS['TL_LANG']['tl_product_attributes']['show_files'] = array('Show Files','If checked, users will be able to select individual files, if false, they may only select folders.');
$GLOBALS['TL_LANG']['tl_product_attributes']['attr_use_mode'] = array('Attribute Use Mode','');
$GLOBALS['TL_LANG']['tl_product_attributes']['attr_default_value'] = array('Default Value','Set a default value for this attribute which can be overridden later.');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_visible_on_front'] = array('Visible to Customers','Is this attribute shown to customers?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_hidden_on_backend'] = array('Not visible when managing product data','Is this attribute hidden from people who are editing product data?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_customer_defined'] = array('Defined by Customer','Is this value defined by the customer?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_required'] = array('Required','Is this attribute required?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_filterable'] = array('Filterable','Can this attribute be used in a filter?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_searchable'] = array('Searchable','Should the search engine look in this field for search terms?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_multiple_select'] = array('Allow Multiple Selections','Stores multiple selections instead of single ones.');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_used_for_price_rules'] = array('Used for price or shopping cart rules','Will this attribute be used to create price or cart rules?');
$GLOBALS['TL_LANG']['tl_product_attributes']['use_rich_text_editor'] = array('Use Full HTML Editor','Allows you to include html in your description.');
$GLOBALS['TL_LANG']['tl_product_attributes']['field_size'] = array('Field Size','How many characters should this field accommodate? Leave blank for unlimited.');
$GLOBALS['TL_LANG']['tl_product_attributes']['storeTable'] = array('Storage Table','What table does this attribute produce a field for?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_listing_field'] = array('Used as a Listing Field','Is this field to be displayed in the product listing?');
$GLOBALS['TL_LANG']['tl_product_attributes']['is_order_by_enabled'] = array('Add to "Order By" option list','This field will be sortable in the listing module provided the attribute is visible to customers.');
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxp'] = array('Check Field Values', 'Ensure user input is valid based on a rule you select.');
$GLOBALS['TL_LANG']['tl_product_attributes']['load_callback'] = array('Load Callback Functions', 'Enter one or more functions separated by commas in the ClassName.functionName format, for example MyClassName.myFunctionName,MySecondClassName.mySecondFunctionName');
$GLOBALS['TL_LANG']['tl_product_attributes']['save_callback'] = array('Save Callback Functions', 'Enter one or more functions separated by commas in the ClassName.functionName format, for example MyClassName.myFunctionName,MySecondClassName.mySecondFunctionName');
$GLOBALS['TL_LANG']['tl_product_attributes']['list_source_table'] = array('List Source Table','the table selected will be used to populate the list using the ID value of this table');
$GLOBALS['TL_LANG']['tl_product_attributes']['list_source_field'] = array('List Source Field','this field will be the label for each value that comes from the list source table.');
$GLOBALS['TL_LANG']['tl_product_attributes']['use_alternate_source'] = array('Alternate List Source','Check this if you would like to use a database table as the data source.');
$GLOBALS['TL_LANG']['tl_product_attributes']['text_collection_rows'] = array('Number of Text Box Rows','Number of text box controls to provide the customer for leaving additional text information related to the product purchase.');
$GLOBALS['TL_LANG']['tl_product_attributes']['disabled'] = array('Global Disable','Disabled this attribute completely.');

$GLOBALS['TL_LANG']['tl_product_attributes']['template_key'] = 'Template Key: ';
$GLOBALS['TL_LANG']['tl_product_attributes']['template_visibility_title'] = 'Available In The Following Templates: ';

$GLOBALS['TL_LANG']['tl_product_attributes']['listing_enabled'] = 'Product Lister Template';
$GLOBALS['TL_LANG']['tl_product_attributes']['reader_enabled'] = 'Product Reader Template';



/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['text'] = 'Text (up to 255 characters)';
$GLOBALS['TL_LANG']['tl_product_attributes']['integer'] = 'Integer/Whole Numbers';
$GLOBALS['TL_LANG']['tl_product_attributes']['decimal'] = 'Decimal';
$GLOBALS['TL_LANG']['tl_product_attributes']['shorttext'] = 'Short Text (up to 128 characters)';
$GLOBALS['TL_LANG']['tl_product_attributes']['longtext'] = 'Long Text (more than 255 characters)';
$GLOBALS['TL_LANG']['tl_product_attributes']['datetime'] = 'Date/Time value';
$GLOBALS['TL_LANG']['tl_product_attributes']['select'] = 'Select List';
$GLOBALS['TL_LANG']['tl_product_attributes']['checkbox'] = 'Checkbox';
$GLOBALS['TL_LANG']['tl_product_attributes']['options'] = 'Option List';
$GLOBALS['TL_LANG']['tl_product_attributes']['file'] = 'File Attachment';
$GLOBALS['TL_LANG']['tl_product_attributes']['media'] = 'Media (Images, Movies, Mp3s, etc.)';
$GLOBALS['TL_LANG']['tl_product_attributes']['label'] = 'Label/Fixed Display';
$GLOBALS['TL_LANG']['tl_product_attributes']['input'] = 'Accept Input From Customer';
$GLOBALS['TL_LANG']['tl_product_attributes']['required_val']['0'] = 'Not Required';
$GLOBALS['TL_LANG']['tl_product_attributes']['required_val']['1'] = 'Required';

$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['alnum'] = 'Numbers and Letters Only';
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['extnd'] = 'All standard characters except: #&()/>=<';
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['email'] = 'Valid email address';
$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']['url'] = 'Valid html link';
#$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions'][''] = '';
#$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions'][''] = '';
#$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions'][''] = '';
#$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions'][''] = '';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['new']    = array('New Attribute', 'Create a New Attribute.');
$GLOBALS['TL_LANG']['tl_product_attributes']['edit']   = array('Edit Attribute', 'Edit Attribute ID %s.');
$GLOBALS['TL_LANG']['tl_product_attributes']['copy']   = array('Copy Attribute', 'Copy Attribute ID %s.');
$GLOBALS['TL_LANG']['tl_product_attributes']['delete'] = array('Delete Attribute', 'Delete Attribute ID %s.  This will not delete related assets but rather the initial Attribute configuration.');
$GLOBALS['TL_LANG']['tl_product_attributes']['show']   = array('Show Attribute Details', 'Show details for Attribute ID %s.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_product_attributes']['general_legend'] = 'General Information';
$GLOBALS['TL_LANG']['tl_product_attributes']['type_legend'] = 'Attribute Type';
$GLOBALS['TL_LANG']['tl_product_attributes']['options_legend'] = 'Option Collection Data';
$GLOBALS['TL_LANG']['tl_product_attributes']['visibility_legend'] = 'Visibility Settings';
$GLOBALS['TL_LANG']['tl_product_attributes']['use_mode_legend'] = 'Use Mode';
$GLOBALS['TL_LANG']['tl_product_attributes']['search_filters_legend'] = 'Search & Filtering Settings';
$GLOBALS['TL_LANG']['tl_product_attributes']['validation_legend'] = 'Validation Settings';
$GLOBALS['TL_LANG']['tl_product_attributes']['developer_tools_legend'] = 'Developer Tools';

//$GLOBALS['TL_LANG']['tl_product_attributes']['_legend'] = '';
//$GLOBALS['TL_LANG']['tl_product_attributes']['_legend'] = '';
?>