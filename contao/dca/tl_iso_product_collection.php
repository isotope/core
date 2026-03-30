<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Contao\System;
use Isotope\Backend\ProductCollection\Callback;
use Isotope\CompatibilityHelper;

/*
 * Load tl_iso_product data container and language files
 */
$this->loadDataContainer('tl_iso_product');
System::loadLanguageFile('tl_iso_product');

$GLOBALS['TL_DCA']['tl_iso_product_collection'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'ctable' => ['tl_iso_product_collection_item', 'tl_iso_product_collection_log', 'tl_iso_address'],
        'closed' => true,
        'notCreatable' => true,
        'notCopyable' => true,
        'notDeletable' => ('select' === Input::get('act')),
        'permissions' => ['update'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uniqid' => 'unique',
                'member,store_id,type' => 'index',
                'uniqid,store_id,type' => 'index',
                'source_collection_id,type' => 'index',
                'type,order_status,locked,config_id,member' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['locked DESC'],
            'panelLayout' => 'filter;sort,search,limit',
            'filter' => [['type=?', 'order'], 'order_status IS NOT NULL', ['locked!=?', '']],
        ],
        'label' => [
            'fields' => ['document_number', 'locked', 'billing_address_id', 'total', 'order_status'],
            'showColumns' => true,
        ],
        'operations' => [
            'details' => [
                'href' => 'act=edit',
                'icon' => 'images/order.svg',
                'primary' => true,
                'prefetch' => true,
                'attributes' => 'data-contao--deeplink-target="primary"',
            ],
            'delete',
            'print_document' => [
                'href' => 'key=print_document',
                'icon' => 'images/document-pdf-text.png',
                'button_callback' => [Callback::class, 'printButton'],
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['sendNotification'],
        'default' => '{status_legend},transaction_status,delivery_status,order_status,date_paid,date_shipped,notes,sendNotification,submit_buttons;{log_legend},order_log;{details_legend},details;{show_legend:hide},show;{email_legend:hide},email_data;{billing_address_legend:hide},billing_address_data;{shipping_address_legend:hide},shipping_address_data',
    ],
    'subpalettes' => [
        'sendNotification' => 'notification,notification_shipping_tracking,notification_customer_notes',
    ],
    'fields' => [
        'id' => [
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'eval' => ['doNotShow' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'member' => [
            'search' => true,
            'foreignKey' => "tl_member.CONCAT_WS(' ', company, firstname, lastname, street, postal, city)",
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'locked' => [
            'flag' => DataContainer::SORT_MONTH_DESC,
            'filter' => true,
            'sorting' => true,
            'eval' => ['rgxp' => 'datim', 'doNotShow' => true],
            'sql' => 'int(10) NULL',
        ],
        'store_id' => [
            'eval' => ['doNotShow' => true],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],
        'settings' => [
            'eval' => ['doNotShow' => true],
            'sql' => 'blob NULL',
        ],
        'checkout_info' => [
            'eval' => ['doNotShow' => true],
            'sql' => 'blob NULL',
        ],
        'payment_data' => [
            'eval' => ['doNotShow' => true],
            'sql' => 'blob NULL',
        ],
        'shipping_data' => [
            'eval' => ['doNotShow' => true],
            'sql' => 'blob NULL',
        ],
        'source_collection_id' => [
            // Not necessarily a cart (as the label says), but useful for the backend order view
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['source_collection_id'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_iso_product_collection'],
        ],
        'document_number' => [
            'search' => true,
            'sorting' => true,
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'uniqid' => [
            'search' => true,
            'sql' => 'varchar(64) NULL',
        ],
        'order_status' => [
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w33'],
            'sql' => 'varchar(32) NULL',
        ],
        'transaction_status' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w33'],
            'sql' => 'varchar(32) NULL',
        ],
        'delivery_status' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w33'],
            'sql' => 'varchar(32) NULL',
        ],
        'date_paid' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'clr w33 wizard'],
            'sql' => 'int(10) NULL',
        ],
        'date_shipped' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w33 wizard'],
            'sql' => 'int(10) NULL',
        ],
        'sendNotification' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'notification' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_nc_notification.title',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'notification_shipping_tracking' => [
            'inputType' => 'textarea',
            'eval' => ['logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow' => true],
        ],
        'notification_customer_notes' => [
            'inputType' => 'textarea',
            'eval' => ['logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow' => true],
        ],
        'submit_buttons' => [
            // input_field_callback from OrderLogListener
            'eval' => ['doNotShow' => true],
        ],
        'config_id' => [
            'filter' => true,
            'foreignKey' => 'tl_iso_config.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'payment_id' => [
            'filter' => true,
            'foreignKey' => 'tl_iso_payment.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'shipping_id' => [
            'filter' => true,
            'foreignKey' => 'tl_iso_shipping.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'billing_address_id' => [
            'search' => true,
            'foreignKey' => "tl_iso_address.CONCAT_WS(' ', label, company, firstname, lastname, street_1, street_2, street_3, postal, city)",
            'eval' => ['doNotShow' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'shipping_address_id' => [
            'search' => true,
            'foreignKey' => "tl_iso_address.CONCAT_WS(' ', label, company, firstname, lastname, street_1, street_2, street_3, postal, city)",
            'eval' => ['doNotShow' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'order_log' => [
            // input_field_callback from OrderLogListener
            'eval' => ['doNotShow' => true],
        ],
        'subtotal' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'tax_free_subtotal' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'total' => [
            'sorting' => true,
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'tax_free_total' => [
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'currency' => [
            'sql' => "varchar(4) NOT NULL default ''",
        ],
        'language' => [
            'options_callback' => static fn () => System::getContainer()->get('contao.intl.locales')->getLocales(null, true),
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'notes' => [
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:80px;', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'details' => [
            'input_field_callback' => [Callback::class, 'generateOrderDetails'],
            'eval' => ['doNotShow' => true],
        ],
        'show' => [
            'input_field_callback' => [Callback::class, 'generateOrderShow'],
            'eval' => ['doNotShow' => true],
        ],
        'email_data' => [
            'input_field_callback' => [Callback::class, 'generateEmailData'],
            'eval' => ['doNotShow' => true],
        ],
        'billing_address_data' => [
            'input_field_callback' => [Callback::class, 'generateBillingAddressData'],
            'eval' => ['doNotShow' => true],
        ],
        'shipping_address_data' => [
            'input_field_callback' => [Callback::class, 'generateShippingAddressData'],
            'eval' => ['doNotShow' => true],
        ],
    ],
];
