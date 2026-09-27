<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_payment'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
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
        'cash' => '{type_legend},type,name,label;{note_legend:hide},note;{checkout_legend},processing_status;{config_legend},shipping_modules,rule;{price_legend:hide},price,tax_class;{enabled_legend},enabled',
        'paypal' => '{type_legend},type,name,label;{note_legend:hide},note;{checkout_legend},processing_status,paid_status,failed_status;{config_legend},shipping_modules,rule;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{enabled_legend},enabled,debug',
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
            'default' => 'cash',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'],
            'eval' => ['includeBlankOption' => true, 'helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
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
            'eval' => ['rte' => 'tinyMCE'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'processing_status' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w33'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'notnull' => false],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'paid_status' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w33'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'notnull' => false],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'failed_status' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_orderstatus.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w33'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'notnull' => false],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'shipping_modules' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'clr w50', 'chosen' => true],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'rule' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_rule.name',
            'eval' => ['tl_class' => 'clr'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne'],
        ],
        'paypal_account' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'email', 'tl_class' => 'w50'],
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
        'enabled' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'debug' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
    ],
];
