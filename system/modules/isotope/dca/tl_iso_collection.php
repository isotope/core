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
$this->loadLanguageFile('tl_iso_products');


/**
 * Table tl_iso_collection
 */
$GLOBALS['TL_DCA']['tl_iso_collection'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => false,
        'ctable'                      => array('tl_iso_collection_product', 'tl_iso_collection_surcharge', 'tl_iso_collection_download'),
        'closed'                      => true,
        'onload_callback'               => array
        (
            array('Isotope\tl_iso_collection', 'checkPermission'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\tl_iso_collection', 'executeSaveHook'),
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                => array('date DESC'),
            'panelLayout'            => 'filter;sort,search,limit',
            'filter'                => array(array('type=?', 'Order'), array('order_status>?', '0')),
        ),
        'label' => array
        (
            'fields'                => array('order_id', 'date', 'billing_address', 'grandTotal', 'order_status'),
            'showColumns'            => true,
            'label_callback'        => array('Isotope\tl_iso_collection', 'getOrderLabel')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'                => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'tools' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['tools'],
                'href'                => '',
                'class'                => 'header_isotope_tools',
                'attributes'        => 'onclick="Backend.getScrollOffset();" style="display:none"',
            ),
            'export_emails' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['export_emails'],
                'href'                => 'key=export_emails',
                'class'                => 'header_iso_export_csv isotope-tools',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'print_invoices' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['print_invoices'],
                'href'                => 'key=print_invoices',
                'class'                => 'header_print_invoices isotope-tools',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'delete' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'info' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['info'],
                'icon'                => 'show.gif',
                'attributes'        => 'class="invisible isotope-contextmenu"',
            ),
            'show' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif',
                'attributes'        => 'class="isotope-tools"',
            ),
            'payment' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['payment'],
                'href'                => 'key=payment',
                'icon'                => 'system/modules/isotope/assets/money-coin.png',
                'attributes'        => 'class="isotope-tools"',
            ),
            'shipping' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['shipping'],
                'href'                => 'key=shipping',
                'icon'                => 'system/modules/isotope/assets/box-label.png',
                'attributes'        => 'class="isotope-tools"',
            ),
            'print_order' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_iso_collection']['print_order'],
                'href'                => 'key=print_order',
                'icon'                => 'system/modules/isotope/assets/document-pdf-text.png'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                    => '{status_legend},order_status,date_paid,date_shipped;{details_legend},details,notes;{email_legend:hide},email_data;{billing_address_legend:hide},billing_address_data;{shipping_address_legend:hide},shipping_address_data',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'eval'                    => array('doNotShow'=>true),
        ),
        'order_id' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['order_id'],
            'search'                => true,
            'sorting'                => true,
        ),
        'uniqid' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['uniqid'],
            'search'                => true,
        ),
        'order_status' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_collection']['order_status'],
            'exclude'               => true,
            'filter'                => true,
            'sorting'                => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_orderstatus.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'save_callback'            => array
            (
                array('Isotope\tl_iso_collection', 'updateOrderStatus'),
            ),
        ),
        'date' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['date'],
            'flag'                    => 8,
            'filter'                => true,
            'sorting'                => true,
            'eval'                    => array('rgxp'=>'date', 'tl_class'=>'clr'),
        ),
        'date_paid' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['date_paid'],
            'exclude'               => true,
            'inputType'                => 'text',
            'eval'                    => array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
        ),
        'date_shipped' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['date_shipped'],
            'exclude'               => true,
            'inputType'                => 'text',
            'eval'                    => array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
        ),
        'config_id' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['config_id'],
            'foreignKey'            => 'tl_iso_config.name',
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'payment_id' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['payment_id'],
            'filter'                => true,
            'foreignKey'            => 'tl_iso_payment_modules.name',
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'shipping_id' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['shipping_id'],
            'filter'                => true,
            'foreignKey'            => 'tl_iso_shipping_modules.name',
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'billing_address' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['billing_address'],
            'search'                => true,
        ),
        'shipping_address' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['shipping_address'],
            'search'                => true,
        ),
        'details' => array
        (
            'input_field_callback'    => array('Isotope\tl_iso_collection', 'generateOrderDetails'),
            'eval'                    => array('doNotShow'=>true),
        ),
        'grandTotal' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'],
        ),
        'notes' => array
        (
            'label'                    => &$GLOBALS['TL_LANG']['tl_iso_collection']['notes'],
            'exclude'               => true,
            'inputType'                => 'textarea',
            'eval'                    => array('style'=>'height:80px;')
        ),
        'email_data' => array
        (
            'input_field_callback'    => array('Isotope\tl_iso_collection', 'generateEmailData'),
            'eval'                    => array('doNotShow'=>true),
        ),
        'billing_address_data' => array
        (
            'input_field_callback'    => array('Isotope\tl_iso_collection', 'generateBillingAddressData'),
            'eval'                    => array('doNotShow'=>true),
        ),
        'shipping_address_data' => array
        (
            'input_field_callback'    => array('Isotope\tl_iso_collection', 'generateShippingAddressData'),
            'eval'                    => array('doNotShow'=>true),
        ),
    )
);
