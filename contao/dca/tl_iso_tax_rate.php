<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Backend\TaxRate\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\Config;
use Isotope\Model\TaxClass;

$GLOBALS['TL_DCA']['tl_iso_tax_rate'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
            [Callback::class, 'addCurrencyRate'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['name'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
            'label_callback' => [Callback::class, 'listRow'],
        ],
    ],
    'palettes' => [
        '__selector__' => ['protected'],
        'default' => '{name_legend},name,label;{rate_legend},rate;{location_legend},address,countries,subdivisions,postalCodes;{condition_legend},amount;{config_legend:hide},config,exemptOnValidVAT,stop,protected',
    ],
    'subpalettes' => [
        'protected' => 'groups',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => TaxClass::getTable().'.name',
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
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
        'address' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'default' => ['shipping'],
            'options' => ['billing', 'shipping'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_tax_rate'],
            'eval' => ['mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
        ],
        'countries' => [
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['multiple' => true, 'size' => 10, 'csv' => ',', 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'text NULL',
        ],
        'subdivisions' => [
            'filter' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['conditionField' => 'countries', 'multiple' => true, 'size' => 10, 'csv' => ',', 'tl_class' => 'w50 w50h'],
            'sql' => 'text NULL',
        ],
        'postalCodes' => [
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:40px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'rate' => [
            'inputType' => 'inputUnit',
            'options' => ['%' => '%'],
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'digit'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'amount' => [
            'inputType' => 'text',
            'eval' => ['multiple' => true, 'size' => 2, 'maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'config' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => Config::getTable().'.name',
            'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'exemptOnValidVAT' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'stop' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'protected' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
    ],
];
