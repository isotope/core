<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Isotope\Model\Config;

PaletteManipulator::create()
    ->addLegend('isotope_legend', 'account_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField('iso_priceDisplay', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member_group')
;

$GLOBALS['TL_DCA']['tl_member_group']['fields']['iso_priceDisplay'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [Config::PRICE_DISPLAY_NET, Config::PRICE_DISPLAY_GROSS, Config::PRICE_DISPLAY_FIXED],
    'reference' => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'helpwizard' => true],
    'sql' => "varchar(8) NOT NULL default ''",
];
