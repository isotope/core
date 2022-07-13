<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Extend a tl_settings default palette
 */
\Haste\Dca\PaletteManipulator::create()
    ->addLegend('iso_timeout_legend', 'uploads_legend', \Haste\Dca\PaletteManipulator::POSITION_AFTER, true)
    ->addField('iso_cartTimeout', 'timeout_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND, 'iso_timeout_legend')
    ->addField('iso_orderTimeout', 'timeout_legend', \Haste\Dca\PaletteManipulator::POSITION_APPEND, 'iso_timeout_legend')
    ->applyToPalette('default', 'tl_settings')
;


/**
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_cartTimeout'] = array
(
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_orderTimeout'] = array
(
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
);
