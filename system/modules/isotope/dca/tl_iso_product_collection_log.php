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
        'notEditable' => \Contao\Input::get('act') === 'select',
        'notCopyable' => true,
        'notSortable' => true,
        'onload_callback' => [
            ['Isotope\Backend\ProductCollectionLog\Callback', 'onLoadCallback'],
        ],
        'onsubmit_callback' => [
            ['Isotope\Backend\ProductCollectionLog\Callback', 'onSubmitCallback'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['tstamp DESC'],
            'headerFields' => ['uniqid', 'date_paid', 'date_shipped'],
            'panelLayout' => 'filter;search,limit',
            'child_record_callback' => ['Isotope\Backend\ProductCollectionLog\Callback', 'onChildRecordCallback'],
        ],
        'label' => [
            'fields' => ['order_status'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{status_legend},order_status,date_paid,date_shipped;{notes_legend},notes;{notification_legend},notification,notification_shipping_tracking,notification_customer_notes',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['tstamp'],
            'flag' => 6,
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'pid' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'author' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['author'],
            'filter' => true,
            'foreignKey' => 'tl_user.username',
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'order_status' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['order_status'],
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'foreignKey' => \Isotope\Model\OrderStatus::getTable().'.name',
            'options_callback' => ['\Isotope\Backend', 'getOrderStatus'],
            'eval' => ['alwaysSave' => true],
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'load_callback' => [
                ['Isotope\Backend\ProductCollectionLog\Callback', 'onOrderStatusLoadCallback'],
            ],
            'save_callback' => [
                ['Isotope\Backend\ProductCollectionLog\Callback', 'onOrderStatusSaveCallback'],
            ],
        ],
        'date_paid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['date_paid'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['alwaysSave' => true, 'rgxp' => 'datim', 'datepicker' => (method_exists($this, 'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class' => 'w50 wizard'],
            'sql' => ['type' => 'integer', 'notnull' => false],
            'load_callback' => [
                ['Isotope\Backend\ProductCollectionLog\Callback', 'onDatePaidLoadCallback'],
            ],
        ],
        'date_shipped' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['date_shipped'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['alwaysSave' => true, 'rgxp' => 'datim', 'datepicker' => (method_exists($this, 'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class' => 'w50 wizard'],
            'sql' => ['type' => 'integer', 'notnull' => false],
            'load_callback' => [
                ['Isotope\Backend\ProductCollectionLog\Callback', 'onDateShippedLoadCallback'],
            ],
        ],
        'notes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notes'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['alwaysSave' => true, 'style' => 'height:80px;', 'tl_class' => 'clr'],
            'sql' => ['type' => 'text', 'notnull' => false],
            'load_callback' => [
                ['Isotope\Backend\ProductCollectionLog\Callback', 'onNotesLoadCallback'],
            ],
        ],
        'notification' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notification'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => ['Isotope\Backend\ProductCollectionLog\Callback', 'onNotificationOptionsCallback'],
            'eval' => ['alwaysSave' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'notification_shipping_tracking' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notification_shipping_tracking'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['alwaysSave' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => 'text', 'notnull' => false],
        ],
        'notification_customer_notes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection_log']['notification_customer_notes'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['alwaysSave' => true, 'tl_class' => 'clr'],
            'sql' => ['type' => 'text', 'notnull' => false],
        ],
    ],
];

