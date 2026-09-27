<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Store\Price\PriceDisplay;

PaletteManipulator::create()
    ->addLegend('isotope_legend', 'account_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField('iso_priceDisplay', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member_group')
;

$GLOBALS['TL_DCA']['tl_member_group']['fields']['iso_priceDisplay'] = [
    'inputType' => 'select',
    'enum' => PriceDisplay::class,
    'reference' => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'helpwizard' => true],
    'sql' => ['type' => Types::STRING, 'length' => 8, 'default' => ''],
];
