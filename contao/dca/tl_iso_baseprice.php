<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_baseprice'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,amount,label',
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
        'amount' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'label' => [
            'search' => true,
            'default' => '%s',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr long'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
    ],
];
