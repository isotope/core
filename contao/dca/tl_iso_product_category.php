<?php

use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_product_category'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_page',
        'notCreatable' => true,
        'notCopyable' => true,
        'notDeletable' => true,
        'doNotCopyRecords' => true,
        'backlink' => 'do=iso_products',
        'backendSearchIgnore' => true,
        'permissions' => [],
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'product_id' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'limit',
            'headerFields' => ['title', 'type'],
        ],
        'label' => [
            'label' => '%s',
            'fields' => ['title'],
        ],
        'operations' => [
            '!cut',
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_page.title',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'sorting' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'product_id' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'product_sorting' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
    ],
];
