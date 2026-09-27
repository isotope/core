<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Checkout\Shipping\FlatCalculation;
use Isotope\CoreBundle\Checkout\Shipping\GroupCalculation;
use Isotope\CoreBundle\Checkout\Shipping\ProductCalculation;

$GLOBALS['TL_DCA']['tl_iso_shipping'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
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
        '__selector__' => ['type', 'flatCalculation', 'group_calculation', 'inherit'],
        'default' => '{title_legend},name,label,type',
        'flat' => '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},price,tax_class,shipping_weight,flatCalculation;{config_legend},minimum_weight,maximum_weight,rule;{order_legend},tracking_url;{enabled_legend},enabled',
        'product_price' => '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},tax_class,shipping_weight,productCalculation;{config_legend},minimum_weight,maximum_weight,rule;{order_legend},tracking_url;{enabled_legend},enabled',
        'group' => '{title_legend},type,inherit,name,label;{note_legend:hide},note;{config_legend},group_methods,rule;{price_legend},group_calculation;{enabled_legend},enabled',
        'groupinherit' => '{title_legend},type,inherit,name;{config_legend},group_methods,rule;{price_legend},group_calculation;{enabled_legend},enabled',
    ],
    'subpalettes' => [
        'flatCalculation_perProduct' => 'flatProductTypes',
        'flatCalculation_perItem' => 'flatProductTypes',
        'flatCalculation_perWeight' => 'flatWeight',
        'group_calculation_summarize' => 'tax_class',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'flat',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'inherit' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'note' => [
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'decodeEntities' => true],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'tracking_url' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr long'],
        ],
        'rule' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_rule.name',
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne'],
        ],
        'minimum_weight' => [
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'maximum_weight' => [
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'price' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'rgxp' => 'surcharge', 'nullIfEmpty' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'notnull' => false],
        ],
        'tax_class' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_tax_class.name',
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'default' => 0],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'flatCalculation' => [
            'inputType' => 'select',
            'enum' => FlatCalculation::class,
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['flat'], 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'flatWeight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'flatProductTypes' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_producttype.name',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'shipping_weight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'productCalculation' => [
            'inputType' => 'select',
            'enum' => ProductCalculation::class,
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['productCalculationOptions'],
            'eval' => ['helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
        ],
        'group_methods' => [
            'inputType' => 'checkboxWizard',
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ',', 'tl_class' => 'clr w50'],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'group_calculation' => [
            'inputType' => 'select',
            'enum' => GroupCalculation::class,
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'debug' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'enabled' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
    ],
];
