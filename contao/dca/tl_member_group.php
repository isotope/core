<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('isotope_legend', 'account_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField('iso_priceDisplay', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member_group')
;

$GLOBALS['TL_DCA']['tl_member_group']['fields']['iso_priceDisplay'] = array
(
    'exclude'               => true,
    'inputType'             => 'select',
    'options'               => array(\Isotope\Model\Config::PRICE_DISPLAY_NET, \Isotope\Model\Config::PRICE_DISPLAY_GROSS, \Isotope\Model\Config::PRICE_DISPLAY_FIXED),
    'reference'             => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
    'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'helpwizard'=>true),
    'sql'                   => "varchar(8) NOT NULL default ''",
);
