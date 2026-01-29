<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Isotope\Backend\Shipping\Callback;
use Isotope\CoreBundle\Checkout\ProductTypeCondition;
use Isotope\CoreBundle\Checkout\Shipping\FlatCalculation;
use Isotope\CoreBundle\Checkout\Shipping\GroupCalculation;
use Isotope\CoreBundle\Checkout\Shipping\ProductCalculation;
use Isotope\CoreBundle\Model\Shipping;

/*
 * Load tl_iso_product data container and language files
 */
$this->loadDataContainer('tl_iso_product');
System::loadLanguageFile('tl_iso_product');

$GLOBALS['TL_DCA']['tl_iso_shipping'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            [Callback::class, 'hideLabelAndNotes'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'userRoot' => 'iso_shippings',
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'flatCalculation'],
        'default' => '{title_legend},name,label,type',
        'flat' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,shipping_weight,flatCalculation;{config_legend},minimum_weight,maximum_weight,product_types,product_types_condition,rule;{enabled_legend},enabled',
        'product_price' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class,shipping_weight,productCalculation;{config_legend},minimum_weight,maximum_weight,product_types,product_types_condition,rule;{enabled_legend},enabled',
        'group' => '{title_legend},name,label,type,inherit;{note_legend:hide},note;{config_legend},group_methods,rule;{price_legend},group_calculation,tax_class;{enabled_legend},enabled',
    ],
    'subpalettes' => [
        'flatCalculation_perProduct' => 'flatProductTypes',
        'flatCalculation_perItem' => 'flatProductTypes',
        'flatCalculation_perWeight' => 'flatWeight',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'flat',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'inherit' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'note' => [
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'decodeEntities' => true],
            'sql' => 'text NULL',
        ],
        'rule' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_rule.name',
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne']
        ],
        'minimum_weight' => [
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'maximum_weight' => [
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'product_types' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_producttype.name',
            'eval' => ['multiple' => true, 'size' => 8, 'chosen' => true, 'tl_class' => 'clr w50'],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'product_types_condition' => [
            'inputType' => 'select',
            'enum' => ProductTypeCondition::class,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'price' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'rgxp' => 'surcharge', 'nullIfEmpty' => true, 'tl_class' => 'w50'],
            'sql' => 'varchar(16) NULL',
        ],
        'tax_class' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_tax_class.name',
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'flatCalculation' => [
            'inputType' => 'select',
            'enum' => FlatCalculation::class,
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['flat'], 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'flatWeight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'flatProductTypes' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_producttype.name',
            'eval' => ['multiple' => true, 'size' => 8, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'shipping_weight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'productCalculation' => [
            'inputType' => 'select',
            'enum' => ProductCalculation::class,
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['productCalculationOptions'],
            'eval' => ['helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'group_methods' => [
            'inputType' => 'checkboxWizard',
            'options_callback' => static function ($dc) {
                $objShipping = Shipping::findBy([$dc->table.'.id!=?'], $dc->id);

                return null === $objShipping ? [] : $objShipping->fetchEach('name');
            },
            'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr w50'],
            'sql' => 'blob NULL',
        ],
        'group_calculation' => [
            'inputType' => 'select',
            'enum' => GroupCalculation::class,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'debug' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'logging' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enabled' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
