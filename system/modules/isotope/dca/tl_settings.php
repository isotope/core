<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Extend a tl_settings default palette
 */
\Haste\Dca\PaletteManipulator::create()
    ->addField('iso_cartTimeout', 'timeout_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND)
    ->addField('iso_orderTimeout', 'timeout_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings')
;


/**
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_cartTimeout'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_settings']['iso_cartTimeout'],
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_orderTimeout'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_settings']['iso_orderTimeout'],
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
);
