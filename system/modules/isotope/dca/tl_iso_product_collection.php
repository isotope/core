<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2020 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Load tl_iso_product data container and language files
 */
$this->loadDataContainer('tl_iso_product');
\Contao\System::loadLanguageFile('tl_iso_product');


/**
 * Table tl_iso_product_collection
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => false,
        'ctable'                    => array(\Isotope\Model\ProductCollectionItem::getTable(), \Isotope\Model\ProductCollectionLog::getTable(), \Isotope\Model\ProductCollectionSurcharge::getTable(), \Isotope\Model\Address::getTable()),
        'closed'                    => true,
        'notCreatable'              => true,
        'notCopyable'               => true,
        'notSortable'               => true,
        'notDeletable'              => ('select' === \Contao\Input::get('act')),
        'onload_callback' => array
        (
            array('Isotope\Backend\ProductCollection\Callback', 'checkPermission'),
            array('Isotope\Backend\ProductCollection\Callback', 'prepareOrderLog'),
            array('Isotope\Backend\ProductCollection\Panel', 'applyAdvancedFilters'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'                        => 'primary',
                'uniqid'                    => 'unique',
                'member,store_id,type'      => 'index',
                'uniqid,store_id,type'      => 'index',
                'source_collection_id,type' => 'index',
            ),
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 2,
            'fields'                => array('locked DESC'),
            'panelLayout'           => 'iso_filters,filter;sort,search,limit',
            'filter'                => array(array('type=?', 'order'), array('order_status>?', '0'), array("locked!=?", '')),
            'panel_callback'        => array
            (
                'iso_filters' => array('Isotope\Backend\ProductCollection\Panel', 'generateFilterButtons'),
            )
        ),
        'label' => array
        (
            'fields'                => array('id', 'document_number', 'locked', 'billing_address_id', 'total', 'order_status'),
            'showColumns'           => true,
            'label_callback'        => array('Isotope\Backend\ProductCollection\Callback', 'getOrderLabel')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'              => 'act=edit',
                'icon'              => 'edit.svg',
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.svg',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'payment' => array
            (
                'href'              => 'key=payment',
                'icon'              => 'system/modules/isotope/assets/images/money-coin.png',
                'button_callback'   => array('\Isotope\Backend\ProductCollection\Callback', 'paymentButton'),
            ),
            'shipping' => array
            (
                'href'              => 'key=shipping',
                'icon'              => 'system/modules/isotope/assets/images/box-label.png',
                'button_callback'   => array('\Isotope\Backend\ProductCollection\Callback', 'shippingButton'),
            ),
            'print_document' => array
            (
                'href'              => 'key=print_document',
                'icon'              => 'system/modules/isotope/assets/images/document-pdf-text.png',
                'button_callback'   => array('\Isotope\Backend\ProductCollection\Callback', 'printButton'),
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__' => ['sendNotification'],
        'default'                   => '{status_legend},order_status,date_paid,date_shipped,notes,sendNotification,submit_buttons;{log_legend},order_log;{details_legend},details;{show_legend:hide},show;{email_legend:hide},email_data;{billing_address_legend:hide},billing_address_data;{shipping_address_legend:hide},shipping_address_data',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'sendNotification' => 'notification,notification_shipping_tracking,notification_customer_notes',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'search'                => true,
            'sql'                   =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'type'  =>  array
        (
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'member'  =>  array
        (
            'search'                => true,
            'foreignKey'            => "tl_member.CONCAT_WS(' ', company, firstname, lastname, street, postal, city)",
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'locked' => array
        (
            'flag'                  => 8,
            'filter'                => true,
            'sorting'               => true,
            'eval'                  => array('rgxp'=>'datim', 'doNotShow'=>true),
            'sql'                   => "int(10) NULL",
        ),
        'store_id' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "int(2) unsigned NOT NULL default '0'",
        ),
        'settings' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "blob NULL",
        ),
        'checkout_info' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "blob NULL"
        ),
        'payment_data' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "blob NULL"
        ),
        'shipping_data' => array
        (
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "blob NULL"
        ),
        'source_collection_id' => array
        (
            // Not necessarily a cart (as the label says), but useful for the backend order view
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['source_collection_id'],
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_iso_product_collection'),
        ),
        'document_number' => array
        (
            'search'                => true,
            'sorting'               => true,
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'uniqid' => array
        (
            'search'                => true,
            'sql'                   => "varchar(64) NULL",
        ),
        'order_status' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'sorting'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\OrderStatus::getTable().'.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'date_paid' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'clr w50 wizard'),
            'sql'                   => 'int(10) NULL'
        ),
        'date_shipped' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                   => 'int(10) NULL',
        ),
        'sendNotification' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange' => true, 'logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'notification' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_nc_notification.title',
            'options_callback'      => array('Isotope\Backend\ProductCollection\Callback', 'onNotificationOptionsCallback'),
            'eval'                  => array('mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow'=>true),
        ),
        'notification_shipping_tracking' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow'=>true),
        ),
        'notification_customer_notes' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('logAlwaysVisible' => true, 'tl_class' => 'clr', 'doNotShow'=>true),
        ),
        'submit_buttons' => array
        (
            'input_field_callback' => array('Isotope\Backend\ProductCollection\Callback', 'onSubmitButtonsInputFieldCallback'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'config_id' => array
        (
            'filter'                => true,
            'foreignKey'            => \Isotope\Model\Config::getTable().'.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'payment_id' => array
        (
            'filter'                => true,
            'foreignKey'            => \Isotope\Model\Payment::getTable().'.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'shipping_id' => array
        (
            'filter'                => true,
            'foreignKey'            => \Isotope\Model\Shipping::getTable().'.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'billing_address_id' => array
        (
            'search'                => true,
            'foreignKey'            => \Isotope\Model\Address::getTable().".CONCAT_WS(' ', label, company, firstname, lastname, street_1, street_2, street_3, postal, city)",
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'shipping_address_id' => array
        (
            'search'                => true,
            'foreignKey'            => \Isotope\Model\Address::getTable().".CONCAT_WS(' ', label, company, firstname, lastname, street_1, street_2, street_3, postal, city)",
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'order_log' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'onLogInputFieldCallback'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'subtotal' => array
        (
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_free_subtotal' => array
        (
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'total' => array
        (
            'sorting'               => true,
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_free_total' => array
        (
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'currency' => array
        (
            'sql'                   => "varchar(4) NOT NULL default ''",
        ),
        'language' => array
        (
            'options_callback' => static function () {
                return \Contao\System::getLanguages();
            },
            'sql'                   => "varchar(5) NOT NULL default ''"
        ),
        'notes' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:80px;', 'tl_class' => 'clr'),
            'sql'                   => 'text NULL',
        ),
        'details' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'generateOrderDetails'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'show' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'generateOrderShow'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'email_data' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'generateEmailData'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'billing_address_data' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'generateBillingAddressData'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'shipping_address_data' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'generateShippingAddressData'),
            'eval'                  => array('doNotShow'=>true),
        ),
    )
);
