<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Table tl_iso_product_collection_log
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_log'] = [

    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => \Isotope\Model\ProductCollection::getTable(),
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'author' => [
            'foreignKey' => 'tl_user.username',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'data' => [
            'sql' => "blob NULL",
        ],
    ],
];
