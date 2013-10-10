<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

/**
 * Help Wizard explanations
 */
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][0]      = array('##document_number##', 'Unique number for this order');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][1]      = array('##items##', 'Number of items in cart');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][2]      = array('##products##', 'Products in cart');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][3]      = array('##subTotal##', 'Subtotal of order');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][6]      = array('##grandTotal##', 'Grand Total');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][7]      = array('##cart_text##', 'List of products in text format');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][8]      = array('##cart_html##', 'List of products in HTML format');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][9]      = array('##billing_address##<br />##billing_address_text##', 'Invoice address as HTML or plain text.');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][10]     = array('##shipping_address##<br />##shipping_address_text##', 'Shipping address as HTML or plain text.');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][11]     = array('##shipping_method##', 'Name of shipping method (as entered in the backend)');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][12]     = array('##shipping_note##<br />##shipping_note_text##', 'Note the chosen shipping method message (also known as plain text available).');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][13]     = array('##payment_method##', 'Name of payment method (as entered in the backend)');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][14]     = array('##payment_note##<br />##payment_note_text##', 'Note the chosen payment method message (also as plain text available).');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][15]     = array('##billing_firstname##<br />##billing_lastname##<br />...', 'Individual fields of the billing address.');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][16]     = array('##shipping_firstname##<br />##shipping_lastname##<br />...', 'Individual fields of the shipping address.');
$GLOBALS['TL_LANG']['XPL']['isoMailTokens'][17]     = array('##form_...##', 'Retrieve condition form data. Use the prefix "form_" and the field\'s name.');


$GLOBALS['TL_LANG']['XPL']['isoAttributeWizard'][0] = array('a)', 'Choose from a few predefined Contao CSS classes e.g. "w50" for input fields that should float left and get 50% width.');
$GLOBALS['TL_LANG']['XPL']['isoAttributeWizard'][1] = array('b)', 'Write your own CSS classes that should be applied to the field.');
$GLOBALS['TL_LANG']['XPL']['isoAttributeWizard'][2] = array('c)', 'Mandatory: Use default value.');
$GLOBALS['TL_LANG']['XPL']['isoAttributeWizard'][3] = array('d)', 'Mandatory: No, never.');
$GLOBALS['TL_LANG']['XPL']['isoAttributeWizard'][4] = array('e)', 'Mandatory: Yes, always.');

$GLOBALS['TL_LANG']['XPL']['isoFieldWizard'][0]     = array('a)', 'Enable or disable field.');
$GLOBALS['TL_LANG']['XPL']['isoFieldWizard'][1]     = array('b)', 'Custom label.');
$GLOBALS['TL_LANG']['XPL']['isoFieldWizard'][2]     = array('c)', 'Mandatory or not.');

$GLOBALS['TL_LANG']['XPL']['isoReaderJumpTo']       = '
<p class="tl_help_table">
    Unlike any other Contao module, a user is not redirected to the reader page when viewing the product details. To solve the issue of nice aliases and to know the detail page of a product, we came up with a new solution.<br>
    <br>
    The reader page (alias) will always be the same page as you selected as a category for the product. There are two options to display the details of a product:<br>
    <br>
    <strong>Option 1:</strong><br>
    Do not set a reader page in the site structure. Place the list and reader module on the same page. Tell the list module to hide if a product alias is found (there\'s a checkbox in the module settings). The reader will automatically be invisible if no reader is found.<br>
    <u>Advantage:</u> Pretty simple to set up<br>
    <u>Disadvantage:</u> The layout of reader and list will be identical, and you cannot have different article content for the two cases.<br>
    <br>
    <strong>Option 2:</strong><br>
    Set a reader page for every list page (product category) in the site structure. <i>Be aware that the reader setting is not inherited!</i> Add the reader module to this page as usual.<br>
    Isotope will now use this page to generate the site if a product alias is found in the URL. The alias will still be the one from the list page though.<br>
    <u>Advantage:</u> You can have different page content and layout (e.g. different columns) for the reader page then the list page.<br>
    <u>Disadvantage:</u> You MUST set a reader page for every list page (category) you have. The setting is NOT INHERITED.
</p>';

$GLOBALS['TL_LANG']['XPL']['mediaManager']          = '<p class="tl_help_table">To upload a new picture, select the file and save the product. After successfully uploading, a preview of the image is displayed and next to it you can enter its alternative text and a description. For multiple pictures, you can click on the arrows to the right and change their order, the top image is used as the main image of each product.</p>';