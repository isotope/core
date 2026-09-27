<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_tax_rate'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'userRoot' => 'iso_tax_rates',
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
        'default' => '{name_legend},name,label,rate;{config_legend:hide},rule',
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
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'rate' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'rule' => [
            'filter' => true,
            'inputType' => 'picker',
            'foreignKey' => 'tl_iso_rule.name',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne'],
        ],
    ],
];
