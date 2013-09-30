<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */


/**
 * Load tl_iso_products data container and language files
 */
$this->loadDataContainer('tl_iso_products');
\System::loadLanguageFile('tl_iso_products');


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
        'ctable'                    => array('tl_iso_product_collection_item', 'tl_iso_product_collection_surcharge'),
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\tl_iso_product_collection', 'checkPermission'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\tl_iso_product_collection', 'executeSaveHook'),
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
            'filter'                => array(array('type=?', 'Order'), array('order_status>?', '0')),
        ),
        'label' => array
        (
            'fields'                => array('document_number', 'locked', 'address1_id', 'grandTotal', 'order_status'),
            'showColumns'           => true,
            'label_callback'        => array('Isotope\tl_iso_product_collection', 'getOrderLabel')
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
                'icon'              => 'system/modules/isotope/assets/money-coin.png',
                'button_callback'   => array('\Isotope\tl_iso_product_collection', 'paymentButton'),
            ),
            'shipping' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping'],
                'href'              => 'key=shipping',
                'icon'              => 'system/modules/isotope/assets/box-label.png',
                'button_callback'   => array('\Isotope\tl_iso_product_collection', 'shippingButton'),
            ),
            'print_document' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document'],
                'href'              => 'key=print_document',
                'icon'              => 'system/modules/isotope/assets/document-pdf-text.png'
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
            'eval'                  => array('doNotShow'=>true),
            'sql'                   =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'type'  =>  array
        (
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'member'  =>  array
        (
            'foreignKey'            => 'tl_member.id',
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
            'sql'                   => "int(2) unsigned NOT NULL default '0'",
        ),
        'language' => array
        (
            'sql'                   => "varchar(5) NOT NULL default ''"
        ),
        'settings' => array
        (
            'sql'                   => "blob NULL",
        ),
        'checkout_info' => array
        (
            'sql'                   => "blob NULL"
        ),
        'payment_data' => array
        (
            'sql'                   => "blob NULL"
        ),
        'shipping_data' => array
        (
            'sql'                   => "blob NULL"
        ),
        'source_collection_id' => array
        (
            'foreignKey'            => 'tl_iso_product_collection.type',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
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
            'foreignKey'            => 'tl_iso_orderstatus.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'save_callback' => array
            (
                array('Isotope\tl_iso_product_collection', 'updateOrderStatus'),
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
            'foreignKey'            => 'tl_iso_config.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'payment_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['payment_id'],
            'filter'                => true,
            'foreignKey'            => 'tl_iso_payment_modules.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'shipping_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping_id'],
            'filter'                => true,
            'foreignKey'            => 'tl_iso_shipping_modules.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'address1_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['address1_id'],
            'foreignKey'            => 'tl_iso_addresses.label',
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'address2_id' => array
        (
            'foreignKey'            => 'tl_iso_addresses.label',
            'eval'                  => array('doNotShow'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'details' => array
        (
            'input_field_callback'  => array('Isotope\tl_iso_product_collection', 'generateOrderDetails'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'subTotal' => array
        (
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'grandTotal' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'],
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'currency' => array
        (
            'sql'                   => "varchar(4) NOT NULL default ''",
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
            'input_field_callback'  => array('Isotope\tl_iso_product_collection', 'generateEmailData'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'billing_address_data' => array
        (
            'input_field_callback'  => array('Isotope\tl_iso_product_collection', 'generateBillingAddressData'),
            'eval'                  => array('doNotShow'=>true),
        ),
        'shipping_address_data' => array
        (
            'input_field_callback'  => array('Isotope\tl_iso_product_collection', 'generateShippingAddressData'),
            'eval'                  => array('doNotShow'=>true),
        ),
    )
);
