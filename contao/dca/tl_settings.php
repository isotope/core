<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('iso_timeout_legend', 'uploads_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField('iso_cartTimeout', 'timeout_legend', PaletteManipulator::POSITION_APPEND, 'iso_timeout_legend')
    ->addField('iso_orderTimeout', 'timeout_legend', PaletteManipulator::POSITION_APPEND, 'iso_timeout_legend')
    ->applyToPalette('default', 'tl_settings')
;

/*
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_cartTimeout'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'nospace' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['iso_orderTimeout'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'nospace' => true, 'tl_class' => 'w50'],
];
