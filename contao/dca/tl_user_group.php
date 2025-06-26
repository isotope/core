<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\Model\Config;
use Isotope\Model\ProductType;
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate;

/*
 * Extend a tl_user_group palette
 */
PaletteManipulator::create()
    ->addLegend('isotope_legend', 'alexf_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('iso_modules', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_product_types', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_product_typep', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_payment_modules', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_payment_modulep', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_shipping_modules', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_shipping_modulep', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_tax_classes', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_tax_classp', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_tax_rates', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_tax_ratep', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_configs', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_configp', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_member_groups', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;

/*
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_modules'] = [
    'filter' => true,
    'inputType' => 'checkbox',
    'reference' => &$GLOBALS['TL_LANG']['IMD'],
    'eval' => ['multiple' => true, 'helpwizard' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_product_types'] = [
    'inputType' => 'checkbox',
    'foreignKey' => ProductType::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_product_typep'] = [
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_payment_modules'] = [
    'inputType' => 'checkbox',
    'foreignKey' => Payment::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_payment_modulep'] = [
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_shipping_modules'] = [
    'inputType' => 'checkbox',
    'foreignKey' => Shipping::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_shipping_modulep'] = [
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_classes'] = [
    'inputType' => 'checkbox',
    'foreignKey' => TaxClass::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_classp'] = [
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_rates'] = [
    'inputType' => 'checkbox',
    'foreignKey' => TaxRate::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_ratep'] = [
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_configs'] = [
    'inputType' => 'checkbox',
    'foreignKey' => Config::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_configp'] = [
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_member_groups'] = [
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_member_group.name',
    'eval' => ['multiple' => true, 'isAssociative' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
