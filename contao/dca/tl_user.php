<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/*
 * Extend tl_user palettes
 */
PaletteManipulator::create()
    ->addLegend('isotope_legend', 'account_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('iso_modules', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_producttypes', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_payments', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_shippings', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_tax_classes', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_tax_rates', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_configs', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_member_groups', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user')
;

/*
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['iso_modules'] = [
    'filter' => true,
    'inputType' => 'checkbox',
    'reference' => &$GLOBALS['TL_LANG']['IMD'],
    'eval' => ['multiple' => true, 'helpwizard' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_producttypes'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_iso_producttype.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_payments'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_iso_payment.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_shippings'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_iso_shipping.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_tax_classes'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_iso_tax_class.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_tax_rates'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_iso_tax_rate.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_configs'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_iso_config.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_member_groups'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_member_group.name',
    'eval' => ['multiple' => true, 'isAssociative' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
