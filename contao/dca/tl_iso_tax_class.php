<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_tax_class'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'userRoot' => 'iso_tax_classes',
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'fallback'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,fallback;{rate_legend},includes,label,rates',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'fallback' => [
            'inputType' => 'checkbox',
            'eval' => ['fallback' => true, 'tl_class' => 'w50 m12'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'includes' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_tax_rate.name',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne'],
        ],
        'label' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'rates' => [
            'inputType' => 'checkboxWizard',
            'foreignKey' => 'tl_iso_tax_rate.name',
            'eval' => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr w50'],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
            'relation' => ['type' => 'hasMany'],
        ],
    ],
];
