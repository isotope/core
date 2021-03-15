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
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['tstamp'],
            'eval' => ['showInOrderView' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'author' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['author'],
            'foreignKey' => 'tl_user.username',
            'eval' => ['showInOrderView' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'data' => [
            'sql' => "blob NULL",
        ],
        'order_status' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['order_status'],
            'foreignKey' => \Isotope\Model\OrderStatus::getTable().'.name',
            'eval' => ['showInOrderView' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'date_paid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['date_paid'],
            'eval' => ['showInOrderView' => true],
            'sql' => 'int(10) NULL',
        ],
        'date_shipped' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['date_shipped'],
            'eval' => ['showInOrderView' => true],
            'sql' => 'int(10) NULL',
        ],
        'notes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notes'],
            'eval' => ['showInOrderView' => true],
            'sql' => "text NULL",
        ],
        'sendNotification' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['sendNotification'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'notification' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notification'],
            'eval' => ['showInOrderView' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'notification_shipping_tracking' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notification_shipping_tracking'],
            'eval' => ['showInOrderView' => true],
            'sql' => "text NULL",
        ],
        'notification_customer_notes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notification_customer_notes'],
            'eval' => ['showInOrderView' => true],
            'sql' => "text NULL",
        ],
    ],
];

