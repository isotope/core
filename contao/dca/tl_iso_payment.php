<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend\Payment\Callback;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Checkout\ProductTypeCondition;
use Isotope\CoreBundle\Checkout\QuantityMode;
use Isotope\CoreBundle\Model\OrderStatus;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\Model\Config;
use Isotope\Model\TaxClass;

$GLOBALS['TL_DCA']['tl_iso_payment'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            [Callback::class, 'loadShippingModules'],
        ],
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
        '__selector__' => ['type', 'protected'],
        'default' => '{type_legend},name,label,type',
        'cash' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled',
    ],
    'subpalettes' => [
        'protected' => 'groups',
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
            'default' => 'cash',
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'],
            'eval' => ['includeBlankOption' => true, 'helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'note' => [
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE'],
            'sql' => 'text NULL',
        ],
        'new_order_status' => [
            'inputType' => 'select',
            'foreignKey' => OrderStatus::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'price' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'rgxp' => 'surcharge', 'nullIfEmpty' => true, 'tl_class' => 'w50'],
            'sql' => 'varchar(16) NULL',
        ],
        'tax_class' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => TaxClass::getTable().'.name',
            'options_callback' => [TaxClass::class, 'getOptionsWithSplit'],
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'minimum_total' => [
            'inputType' => 'text',
            'default' => 0,
            'eval' => ['maxlength' => 255, 'rgxp' => 'digit', 'tl_class' => 'clr w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'maximum_total' => [
            'inputType' => 'text',
            'default' => 0,
            'eval' => ['maxlength' => 255, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'quantity_mode' => [
            'inputType' => 'select',
            'enum' => QuantityMode::class,
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'minimum_quantity' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'clr w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'maximum_quantity' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'countries' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'w50', 'chosen' => true],
            'sql' => 'text NULL',
        ],
        'shipping_modules' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'product_types' => [
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'clr w50', 'chosen' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'product_types_condition' => [
            'inputType' => 'select',
            'enum' => ProductTypeCondition::class,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'config_ids' => [
            'inputType' => 'select',
            'foreignKey' => Config::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'clr w50', 'chosen' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'protected' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
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
