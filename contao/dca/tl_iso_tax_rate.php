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
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
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
            'fields' => ['name', 'rate'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s%%]</span>',
        ],
    ],
    'palettes' => [
        '__selector__' => ['protected'],
        'default' => '{name_legend},name,label,rate;{location_legend},address,countries,subdivisions,postalCodes;{config_legend:hide},config,exemptOnValidVAT,stop,protected',
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
        'rate' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'address' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'default' => ['shipping'],
            'options' => ['billing', 'shipping'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_tax_rate'],
            'eval' => ['mandatory' => true, 'multiple' => true, 'csv' => ','],
            'sql' => 'text NULL',
        ],
        'countries' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 10, 'csv' => ',', 'tl_class' => 'w50', 'chosen' => true],
            'sql' => 'text NULL',
        ],
        'subdivisions' => [
            'filter' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['conditionField' => 'countries', 'multiple' => true, 'size' => 10, 'csv' => ',', 'tl_class' => 'w50'],
            'sql' => 'text NULL',
        ],
        'postalCodes' => [
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:40px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
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
