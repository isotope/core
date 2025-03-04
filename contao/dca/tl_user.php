<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;
use Isotope\Backend;
use Isotope\Backend\User\Callback;
use Isotope\Model\Config;
use Isotope\Model\Group;
use Isotope\Model\Payment;
use Isotope\Model\ProductType;
use Isotope\Model\Shipping;
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate;

System::loadLanguageFile(Group::getTable());

/*
 * Extend tl_user palettes
 */
PaletteManipulator::create()
    ->addLegend('isotope_legend', 'account_legend', PaletteManipulator::POSITION_BEFORE)
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
    ->addField('iso_groups', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_groupp', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('iso_member_groups', 'isotope_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user')
;

/*
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['iso_modules'] = [
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'options_callback' => [Backend::class, 'getIsotopeModules'],
    'reference' => &$GLOBALS['TL_LANG']['IMD'],
    'eval' => ['multiple' => true, 'helpwizard' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_product_types'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => ProductType::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_product_typep'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_payment_modules'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => Payment::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_payment_modulep'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_shipping_modules'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => Shipping::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_shipping_modulep'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_tax_classes'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => TaxClass::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_tax_classp'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_tax_rates'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => TaxRate::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_tax_ratep'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_configs'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => Config::getTable().'.name',
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_configp'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_groups'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => [Callback::class, 'getGroups'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_groupp'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete', 'rootPaste'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true, 'tl_class' => 'w50 w50h'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_member_groups'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_member_group.name',
    'eval' => ['multiple' => true, 'isAssociative' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
