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


$this->loadLanguageFile('tl_iso_groups');

/**
 * Extend a tl_user_group palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('{alexf_legend}', '{isotope_legend:hide},iso_modules,iso_product_types,iso_product_typep,iso_payment_modules,iso_payment_modulep,iso_shipping_modules,iso_shipping_modulep,iso_tax_classes,iso_tax_classp,iso_tax_rates,iso_tax_ratep,iso_mails,iso_mailp,iso_configs,iso_configp,iso_groups,iso_groupp;{alexf_legend}', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_modules'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_modules'],
    'exclude'                => true,
    'filter'                => true,
    'inputType'                => 'checkbox',
    'options_callback'        => array('Isotope\Backend', 'getIsotopeModules'),
    'reference'                => &$GLOBALS['TL_LANG']['IMD'],
    'eval'                    => array('multiple'=>true, 'helpwizard'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_product_types'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_product_types'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_producttypes.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_product_typep'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_product_typep'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_payment_modules'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_payment_modules'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_payment_modules.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_payment_modulep'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_payment_modulep'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_shipping_modules'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_shipping_modules'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_shipping_modules.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_shipping_modulep'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_shipping_modulep'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_classes'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_tax_classes'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_tax_class.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_classp'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_tax_classp'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_rates'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_tax_rates'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_tax_rate.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_tax_ratep'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_tax_ratep'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_mails'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_mails'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_mail.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_mailp'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_mailp'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_configs'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_configs'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'foreignKey'            => 'tl_iso_config.name',
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_configp'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_configp'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h')
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_groups'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_groups'],
    'exclude'                => true,
    'inputType'                => 'tableTree',
    'reference'                => array
    (
        'icon'    => array
        (
            'tl_iso_groups'    => array('system/modules/isotope/assets/folders.png', 'system/modules/isotope/assets/folder-network.png')
        )
    ),
    'eval'                    => array
    (
            'tableColumn'    => 'tl_iso_groups.name',
            'fieldType'        => 'checkbox',
            'title'            => &$GLOBALS['TL_LANG']['tl_iso_groups']['label'],
            'children'        => true,
            'tl_class'        => 'clr'
    )
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iso_groupp'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_user']['iso_groupp'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'options'                => array('create', 'delete'),
    'reference'                => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true, 'tl_class'=>'clr')
);
