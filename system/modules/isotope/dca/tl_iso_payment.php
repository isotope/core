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
 * Table tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
            array('Isotope\Backend\Payment\Callback', 'checkPermission'),
            array('Isotope\Backend\Payment\Callback', 'loadShippingModules'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 1,
            'fields'                => array('name'),
            'flag'                  => 1,
            'panelLayout'           => 'sort,filter;search,limit'
        ),
        'label' => array
        (
            'fields'                => array('name', 'type'),
            'format'                => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'              => 'mod=&table=',
                'class'             => 'header_back',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\Backend\Payment\Callback', 'copyPaymentModule'),
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Payment\Callback', 'deletePaymentModule'),
            ),
            'toggle' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['toggle'],
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'   => array('Isotope\Backend\Payment\Callback', 'toggleIcon')
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'protected'),
        'default'                   => '{type_legend},name,label,type',
        'cash'                      => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'paybyway'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},paybyway_merchant_id,paybyway_private_key;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'paypal'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'postfinance'               => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'viveum'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'datatrans'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},datatrans_id,datatrans_sign;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'sparkasse'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},sparkasse_paymentmethod,trans_type,sparkasse_sslmerchant,sparkasse_sslpassword,sparkasse_merchantref;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'sofortueberweisung'        => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},trans_type,sofortueberweisung_user_id,sofortueberweisung_project_id,sofortueberweisung_project_password;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'saferpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},saferpay_accountid,trans_type,saferpay_description,saferpay_vtconfig;{price_legend:hide},price,tax_class;{enabled_legend},enabled',
        'expercash'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},expercash_popupId,expercash_profile,expercash_popupKey,expercash_paymentMethod;{price_legend:hide},price,tax_class;{template_legend},expercash_css;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'payone'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},trans_type,payone_clearingtype,payone_aid,payone_portalid,payone_key;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled',
        'worldpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types,config_ids;{gateway_legend},worldpay_instId,worldpay_callbackPW,worldpay_signatureFields,worldpay_md5secret,worldpay_description;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'protected'                 => 'groups',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['name'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['label'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['type'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'default'               => 'cash',
            'options_callback'      => function() {
                return \Isotope\Model\Payment::getModelTypeOptions();
            },
            'eval'                  => array('includeBlankOption'=>true, 'helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'note' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['note'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('rte'=>'tinyMCE'),
            'sql'                   => "text NULL",
        ),
        'new_order_status' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\OrderStatus::getTable().'.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'price' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['price'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>16, 'rgxp'=>'surcharge', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'tax_class' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\TaxClass::getTable().'.name',
            'options_callback'      => array('\Isotope\Model\TaxClass', 'getOptionsWithSplit'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'allowed_cc_types' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['allowed_cc_types'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'options_callback'      => array('Isotope\Backend\Payment\Callback', 'getAllowedCCTypes'),
            'eval'                  => array('multiple'=>true, 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'trans_type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'],
            'exclude'               => true,
            'default'               => 'capture',
            'inputType'             => 'select',
            'options'               => array('capture', 'auth'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'helpwizard'=>true),
            'reference'             => $GLOBALS['TL_LANG']['tl_iso_payment'],
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'minimum_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'],
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 0,
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'clr w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'maximum_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'],
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 0,
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'countries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['countries'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \System::getCountries();
            },
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'shipping_modules' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'],
            'exclude'               => true,
            'inputType'             => 'select',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'product_types' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\ProductType::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'config_ids' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['config_ids'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\Config::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'paybyway_merchant_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['paybyway_merchant_id'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) NOT NULL default '0'",
        ),
        'paybyway_private_key' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['paybyway_private_key'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'paypal_account' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_account'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'psp_pspid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_pspid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'psp_http_method' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_http_method'],
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'POST',
            'options'               => array('POST', 'GET'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(4) NOT NULL default ''",
        ),
        'psp_hash_method' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'],
            'exclude'               => true,
            'default'               => 'sha1',
            'inputType'             => 'select',
            'options'               => array('sha1', 'sha256', 'sha512'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(6) NOT NULL default ''",
        ),
        'psp_hash_in' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_in'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''", // Max is 512 bit hash = 128 hex digits
        ),
        'psp_hash_out' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_out'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''", // Max is 512 bit hash = 128 hex digits
        ),
        'psp_dynamic_template' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_dynamic_template'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>128, 'rgxp'=>'url', 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'datatrans_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_id'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>100, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'datatrans_sign' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_sign'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'sparkasse_paymentmethod' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('creditcard', 'maestro', 'directdebit'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'sparkasse_sslmerchant' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslmerchant'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sparkasse_sslpassword' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'sparkasse_merchantref' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_merchantref'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'sofortueberweisung_user_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_user_id'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sofortueberweisung_project_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_id'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sofortueberweisung_project_password' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['sofortueberweisung_project_password'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_accountid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_accountid'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'saferpay_description' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_description'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_vtconfig' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['saferpay_vtconfig'],
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'expercash_popupId' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupId'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''"
        ),
        'expercash_profile' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_profile'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>3, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(3) NOT NULL default '0'"
        ),
        'expercash_popupKey' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupKey'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'expercash_paymentMethod' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('automatic_payment_method', 'elv_buy', 'elv_authorize', 'cc_buy', 'cc_authorize', 'giropay', 'sofortueberweisung'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'expercash_css' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_css'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'css', 'tl_class'=>'clr'),
            'sql'                   => "binary(16) NULL"
        ),
        'payone_clearingtype' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone_clearingtype'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('elv', 'cc', 'dc', 'vor', 'rec', 'sb', 'wlt'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone'],
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''"
        ),
        'payone_aid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone_aid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(6) NOT NULL default ''"
        ),
        'payone_portalid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone_portalid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>7, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(7) NOT NULL default ''"
        ),
        'payone_key' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone_key'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'worldpay_instId' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_instId'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(6) NOT NULL default '0'",
        ),
        'worldpay_callbackPW' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_callbackPW'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'worldpay_signatureFields' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_signatureFields'],
            'exclude'               => true,
            'default'               => 'instId:cartId:amount:currency',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'worldpay_md5secret' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_md5secret'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'worldpay_description' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_description'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'requireCCV' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['requireCCV'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'guests' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['guests'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'protected' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['protected'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'groups' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['groups'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('multiple'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'debug' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['debug'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'enabled' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment']['enabled'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
    )
);
