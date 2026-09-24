<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_iso_order_document'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'closed' => true,
        'notEditable' => true,
        'notCopyable' => true,
        'notSortable' => true,
        'ptable' => 'tl_iso_product_collection',
        'permissions' => ['create', 'delete'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'uniqid' => 'unique',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'headerFields' => ['id', 'document_number', 'order_status', 'transaction_status', 'delivery_status'],
            'defaultSearchField' => 'document_number',
            'fields' => ['document_number'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,filter,limit',
        ],
        'label' => [
            'fields' => ['document_number'],
        ],
        'global_operations' => [
            'create' => [
                'icon' => 'new.svg',
                'primary' => true,
            ],
            'all',
        ],
        'operations' => [
            'download' => [
                'href' => 'act=show',
                'icon' => 'images/download.svg',
                'primary' => true,
            ],
            'delete',
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => 'type',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'uniqid' => [
            'sql' => ['type' => Types::STRING, 'length' => 36, 'notnull' => false],
        ],
        'tstamp' => [
            'sorting' => true,
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'type' => [
            'filter' => true,
            'foreignKey' => 'tl_iso_document.name',
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'document_number' => [
            'search' => true,
            'sorting' => true,
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'file_id' => [
            'sql' => ['type' => Types::STRING, 'length' => 64, 'notnull' => false],
        ],
        'file_name' => [
            'sql' => ['type' => Types::STRING, 'length' => 64, 'notnull' => false],
        ],
        'mime_type' => [
            'sql' => ['type' => Types::STRING, 'length' => 32, 'notnull' => false],
        ],
    ],
];
