<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\CoreBundle\Checkout\ProductTypeCondition;

$GLOBALS['TL_DCA']['tl_iso_payment'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'userRoot' => 'iso_payments',
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
        '__selector__' => ['type'],
        'default' => '{type_legend},name,label,type',
        'cash' => '{type_legend},type,name,label;{note_legend:hide},note;{checkout_legend},processing_status;{config_legend},shipping_modules,product_types,product_types_condition,rule;{price_legend:hide},price,tax_class;{enabled_legend},enabled',
        'paypal' => '{type_legend},type,name,label;{note_legend:hide},note;{checkout_legend},processing_status,paid_status,failed_status;{config_legend},shipping_modules,product_types,product_types_condition,rule;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{enabled_legend},enabled,debug,logging',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'cash',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'],
            'eval' => ['includeBlankOption' => true, 'helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'note' => [
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE'],
            'translation' => ['domain' => 'isotope'],
            'sql' => 'text NULL',
        ],
        'processing_status' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w33'],
            'sql' => 'varchar(32) NULL',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'paid_status' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w33'],
            'sql' => 'varchar(32) NULL',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'failed_status' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w33'],
            'sql' => 'varchar(32) NULL',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'shipping_modules' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'clr w50', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'rule' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_rule.name',
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne'],
        ],
        'product_types' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_producttype.name',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'clr w50', 'chosen' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'product_types_condition' => [
            'inputType' => 'select',
            'enum' => ProductTypeCondition::class,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'paypal_account' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'email', 'tl_class' => 'w50'],
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
        'debug' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'logging' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'enabled' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
    ],
];
