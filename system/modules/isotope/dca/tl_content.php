<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_list_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_list_layout'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'		  => array('Isotope\tl_content', 'getListTemplates'),
	'eval'					  => array('includeBlankOption'=>true),
);
