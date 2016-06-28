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
$GLOBALS['TL_LANG']['tl_iso_gallery']['name']                           = array('Name', 'Enter a name for this gallery.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['type']                           = array('Type', 'Please select a gallery type.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['anchor']                         = array('Image anchor', 'Select what anchor type should be generated on the image.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['placeholder']                    = array('Placeholder image', 'This image will be used if an image file cannot be found or none are associated with a product.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['main_size']                      = array('Main image size', 'Please enter a width and height for the main product image.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_size']                   = array('Gallery image size', 'Please enter a width and height for the additional images.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_template']              = array('Lightbox template', 'Please select the lightbox template from your page-layout to reload the image gallery after an AJAX request.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_size']                  = array('Lightbox image size', 'Please enter a width and height for lightbox images.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_size']                      = array('Zoom image size', 'Please enter a width and height for zoom images.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_windowSize']                = array('Zoom window size', 'Optionally enter a width and height for the zoom window (defaults to 400x400).');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']                  = array('Zoom window position', 'Optionally enter a horizontal and vertical offset and a position for the zoom window.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_windowFade']                = array('Zoom window fade effect', 'Optionally enter miliseconds to fade in and fade out the zoom window.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_border']                    = array('Zoom window border', 'Optionally enter a hex color and size in pixels for the zoom window.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['main_watermark_image']           = array('Main watermark image', 'Select an image if you want to add a watermark to the main product image.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['main_watermark_position']        = array('Main watermark position', 'Select the position where to apply the watermark to.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_watermark_image']        = array('Gallery watermark image', 'Select an image if you want to add a watermark to the gallery images.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['gallery_watermark_position']     = array('Gallery watermark position', 'Select the position where to apply the watermark to.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_watermark_image']       = array('Lightbox watermark image', 'Select an image if you want to add a watermark to the lightbox images.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_watermark_position']    = array('Lightbox watermark position', 'Select the position where to apply the watermark to.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_watermark_image']           = array('Zoom watermark image', 'Select an image if you want to add a watermark to the zoom images.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_watermark_position']        = array('Zoom watermark position', 'Select the position where to apply the watermark to.');
$GLOBALS['TL_LANG']['tl_iso_gallery']['customTpl']                      = array('Custom gallery template', 'Here you can overwrite the default gallery template.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_gallery']['new']            = array('New gallery', 'Create a new gallery');
$GLOBALS['TL_LANG']['tl_iso_gallery']['edit']           = array('Edit gallery', 'Edit gallery ID %s');
$GLOBALS['TL_LANG']['tl_iso_gallery']['copy']           = array('Duplicate gallery', 'Duplicate gallery ID %s');
$GLOBALS['TL_LANG']['tl_iso_gallery']['delete']         = array('Delete gallery', 'Delete gallery ID %s');
$GLOBALS['TL_LANG']['tl_iso_gallery']['show']           = array('Gallery details', 'Show details of gallery ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_gallery']['name_legend']        = 'Name &amp; Type';
$GLOBALS['TL_LANG']['tl_iso_gallery']['size_legend']        = 'Image sizes';
$GLOBALS['TL_LANG']['tl_iso_gallery']['config_legend']      = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_gallery']['watermark_legend']   = 'Watermark';
$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_legend']    = 'Lightbox/Mediabox';
$GLOBALS['TL_LANG']['tl_iso_gallery']['template_legend']    = 'Template settings';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_gallery']['none']                   = 'No link action';
$GLOBALS['TL_LANG']['tl_iso_gallery']['reader']                 = 'Link to the product reader';
$GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox']               = 'Open lightbox/mediabox';
$GLOBALS['TL_LANG']['tl_iso_gallery']['includeJQuery']          = 'Make sure to include jQuery in your page layout for this gallery to work correctly.';
$GLOBALS['TL_LANG']['tl_iso_gallery']['pictureNotSupported']    = 'Be aware that this gallery type does not support &lt;picture&gt; (responsive images).';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos1']  = 'Right Top (Pos. #1)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos2']  = 'Right Center (Pos. #2)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos3']  = 'Right Bottom (Pos. #3)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos4']  = 'Right Bottom Corner (Pos. #4)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos5']  = 'Bottom Right (Pos. #5)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos6']  = 'Bottom Center (Pos. #6)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos7']  = 'Bottom Left (Pos. #7)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos8']  = 'Left Bottom Corner (Pos. #8)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos9']  = 'Left Bottom (Pos. #9)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos10'] = 'Left Center (Pos. #10)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos11'] = 'Left Top (Pos. #11)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos12'] = 'Left Top Corner (Pos. #12)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos13'] = 'Top Left (Pos. #13)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos14'] = 'Top Center (Pos. #14)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos15'] = 'Top Right (Pos. #15)';
$GLOBALS['TL_LANG']['tl_iso_gallery']['zoom_position']['pos16'] = 'Top Right Corner (Pos. #16)';
