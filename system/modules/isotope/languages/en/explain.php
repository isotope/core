<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


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

$GLOBALS['TL_LANG']['XPL']['noVariantAttributes']   = 'To use product variants with Isotope eCommerce, you must first configure the attributes.<br>Please refer to <a href="http://isotopeecommerce.org/en/manual.html" target="_blank" style="text-decoration:underline">the manual</a> for more information about attribute setup.';

$GLOBALS['TL_LANG']['XPL']['elevatezoom_position'] = 'Positioning the window can be done in by setting a default position, and then using x and y offset to adjust. You can also position the window into a container - See more at: <a href="http://www.elevateweb.co.uk/image-zoom/examples#window-position" target="_blank">http://www.elevateweb.co.uk/image-zoom/examples#window-position</a><br><br><img src="system/modules/isotope/assets/images/elevatezoom-positions.png" alt="">';
