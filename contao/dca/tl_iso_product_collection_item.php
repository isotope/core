<?php

use Contao\DC_Table;

/*
 * Table tl_iso_product_collection_item
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_item'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'notEditable' => true,
        'ptable' => 'tl_iso_product_collection',
        'ctable' => ['tl_iso_product_collection_download'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'product_id' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_iso_product_collection.document_number',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'product_id' => [
            'foreignKey' => 'tl_iso_product.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'type' => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'sku' => [
            'sql' => 'varchar(128) NULL',
        ],
        'name' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'configuration' => [
            'sql' => 'blob NULL',
        ],
        'quantity' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'price' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'tax_free_price' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'tax_id' => [
            // Not the ID of a tax class or rate, this is the CSV list of applicable taxes (incremental numeric)
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'jumpTo' => [
            'foreignKey' => 'tl_page.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
    ],
];
