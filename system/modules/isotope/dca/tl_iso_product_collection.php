<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Load tl_iso_product data container and language files
 */
$this->loadDataContainer('tl_iso_product');
\System::loadLanguageFile('tl_iso_product');


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
        'ctable'                    => array(\Isotope\Model\ProductCollectionItem::getTable(), \Isotope\Model\ProductCollectionSurcharge::getTable(), \Isotope\Model\Address::getTable()),
        'closed'                    => true,
        'notDeletable'              => (\Input::get('act') == 'select'),
        'onload_callback' => array
        (
            array('Isotope\Backend\ProductCollection\Callback', 'checkPermission'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend\ProductCollection\Callback', 'executeSaveHook'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'member,store_id,type' => 'index',
                'uniqid,store_id,type' => 'index',
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
            'panelLayout'           => 'filter;sort,search,limit',
            'filter'                => array(array('type=?', 'order'), array('order_status>?', '0'), array("locked!=?", '')),
        ),
        'label' => array
        (
            'fields'                => array('document_number', 'locked', 'billing_address_id', 'grandTotal', 'order_status'),
            'showColumns'           => true,
            'label_callback'        => array('Isotope\Backend\ProductCollection\Callback', 'getOrderLabel')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif',
            ),
            'payment' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['payment'],
                'href'              => 'key=payment',
                'icon'              => 'system/modules/isotope/assets/images/money-coin.png',
                'button_callback'   => array('\Isotope\Backend\ProductCollection\Callback', 'paymentButton'),
            ),
            'shipping' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping'],
                'href'              => 'key=shipping',
                'icon'              => 'system/modules/isotope/assets/images/box-label.png',
                'button_callback'   => array('\Isotope\Backend\ProductCollection\Callback', 'shippingButton'),
            ),
            'print_document' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document'],
                'href'              => 'key=print_document',
                'icon'              => 'system/modules/isotope/assets/images/document-pdf-text.png'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{status_legend},order_status,date_paid,date_shipped;{details_legend},details,notes;{email_legend:hide},email_data;{billing_address_legend:hide},billing_address_data;{shipping_address_legend:hide},shipping_address_data',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
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
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['member'],
            'foreignKey'            => "tl_member.CONCAT(firstname, ' ', lastname)",
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'locked' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['locked'],
            'flag'                  => 8,
            'filter'                => true,
            'sorting'               => true,
            'eval'                  => array('rgxp'=>'date'),
            'sql'                   => "varchar(10) NOT NULL default ''",
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
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_number'],
            'search'                => true,
            'sorting'               => true,
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'uniqid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['uniqid'],
            'search'                => true,
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'order_status' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['order_status'],
            'exclude'               => true,
            'filter'                => true,
            'sorting'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\OrderStatus::getTable().'.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'save_callback' => array
            (
                array('Isotope\Backend\ProductCollection\Callback', 'updateOrderStatus'),
            ),
        ),
        'date_paid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['date_paid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'datim', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(10) NOT NULL default ''"
        ),
        'date_shipped' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['date_shipped'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'config_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['config_id'],
            'foreignKey'            => \Isotope\Model\Config::getTable().'.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'payment_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['payment_id'],
            'filter'                => true,
            'foreignKey'            => \Isotope\Model\Payment::getTable().'.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'shipping_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping_id'],
            'filter'                => true,
            'foreignKey'            => \Isotope\Model\Shipping::getTable().'.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'billing_address_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['billing_address_id'],
            'foreignKey'            => \Isotope\Model\Address::getTable().'.label',
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'shipping_address_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping_address_id'],
            'foreignKey'            => \Isotope\Model\Address::getTable().'.label',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'details' => array
        (
            'input_field_callback'  => array('Isotope\Backend\ProductCollection\Callback', 'generateOrderDetails'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'subtotal' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['subtotal'],
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_free_subtotal' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['tax_free_subtotal'],
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['total'],
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_free_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['tax_free_total'],
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'currency' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['currency'],
            'sql'                   => "varchar(4) NOT NULL default ''",
        ),
        'language' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['language'],
            'sql'                   => "varchar(5) NOT NULL default ''"
        ),
        'notes' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['notes'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:80px;'),
            'sql'                   => "text NULL",
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
