<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend;

$GLOBALS['TL_DCA']['tl_iso_product_category'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_page',
        'closed' => true,
        'notEditable' => true,
        'notCopyable' => true,
        'notDeletable' => true,
        'backlink' => 'do=iso_products',
        'sql' => [
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
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_page.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'product_id' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'product_sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
