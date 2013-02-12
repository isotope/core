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
 * Extend tl_page palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = str_replace(';{publish_legend}', ';{isotope_legend},iso_config;{publish_legend}', $GLOBALS['TL_DCA']['tl_page']['palettes']['root']);
$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace(';{publish_legend}', ';{isotope_legend},iso_setReaderJumpTo;{publish_legend}', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']);


/**
 * Add a selector to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'iso_setReaderJumpTo';


/**
 * Add subpalettes to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['iso_setReaderJumpTo'] = 'iso_readerJumpTo';


/**
 * Add fields to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['iso_config'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_config'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'foreignKey'              => 'tl_iso_config.name',
    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_setReaderJumpTo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_setReaderJumpTo'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr')
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_readerJumpTo'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    // @todo: only show the pages from this page root (a reader page in a different page tree than the current doesn't make sense) as soon as http://dev.contao.org/issues/3563 is implemented
    'eval'                      => array('fieldType'=>'radio', 'mandatory'=>true),
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
);


/**
 * Disable header edit button
 */
if ($_GET['table'] == 'tl_iso_product_categories')
{
    $GLOBALS['TL_DCA']['tl_page']['config']['notEditable'] = true;
}
