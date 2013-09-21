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
 */


/**
 * Table tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules'] = array
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
            array('Isotope\tl_iso_payment_modules', 'checkPermission'),
            array('Isotope\tl_iso_payment_modules', 'loadShippingModules'),
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'],
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\tl_iso_payment_modules', 'copyPaymentModule'),
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\tl_iso_payment_modules', 'deletePaymentModule'),
            ),
            'toggle' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['toggle'],
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'   => array('Isotope\tl_iso_payment_modules', 'toggleIcon')
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'],
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
        'cash'                      => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'paypal'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'postfinance'               => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},postfinance_pspid,postfinance_secret,postfinance_method;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'datatrans'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},datatrans_id,datatrans_sign;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'sparkasse'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},sparkasse_paymentmethod,trans_type,sparkasse_sslmerchant,sparkasse_sslpassword,sparkasse_merchantref;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
        'sofortueberweisung'        => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},trans_type,sofortueberweisung_user_id,sofortueberweisung_project_id,sofortueberweisung_project_password;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'saferpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},saferpay_accountid,trans_type,saferpay_description,saferpay_vtconfig;{price_legend:hide},price,tax_class;{enabled_legend},enabled',
        'expercash'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},expercash_popupId,expercash_profile,expercash_popupKey,expercash_paymentMethod;{price_legend:hide},price,tax_class;{template_legend},expercash_css;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'payone'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},trans_type,payone_clearingtype,payone_aid,payone_portalid,payone_key;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled',
        'worldpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},worldpay_instId,worldpay_callbackPW,worldpay_signatureFields,worldpay_md5secret,worldpay_description;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled',
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
            'sql'                   => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'"
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'default'               => 'cash',
            'options'               => \Isotope\Model\Payment::getModelTypeOptions(),
            'eval'                  => array('includeBlankOption'=>true, 'helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''"
        ),
        'note' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('rte'=>'tinyMCE'),
            'sql'                   => "text NULL"
        ),
        'new_order_status' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_orderstatus.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'sql'                   => "int(10) NOT NULL default '0'"
        ),
        'price' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>16, 'rgxp'=>'surcharge', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''"
        ),
        'tax_class' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_tax_class.name',
            'options_callback'      => array('\Isotope\Backend', 'getTaxClassesWithSplit'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
            'sql'                   => "int(10) NOT NULL default '0'"
        ),
        'allowed_cc_types' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'options_callback'      => array('Isotope\tl_iso_payment_modules', 'getAllowedCCTypes'),
            'eval'                  => array('multiple'=>true, 'tl_class'=>'clr'),
            'sql'                   => "text NULL"
        ),
        'trans_type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'],
            'exclude'               => true,
            'default'               => 'capture',
            'inputType'             => 'select',
            'options'               => array('capture', 'auth'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'helpwizard'=>true),
            'reference'             => $GLOBALS['TL_LANG']['tl_iso_payment_modules'],
            'sql'                   => "varchar(8) NOT NULL default ''"
        ),
        'minimum_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'],
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 0,
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'clr w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'"
        ),
        'maximum_total' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'],
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 0,
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'"
        ),
        'countries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => \System::getCountries(),
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL"
        ),
        'shipping_modules' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'],
            'exclude'               => true,
            'inputType'             => 'select',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL"
        ),
        'product_types' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'],
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => 'tl_iso_producttypes.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr w50 w50h', 'chosen'=>true),
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
            'sql'                   => "blob NULL"
        ),
        'paypal_account' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'postfinance_pspid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'postfinance_secret' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'postfinance_method' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'],
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'POST',
            'options'               => array('POST', 'GET'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(4) NOT NULL default ''"
        ),
        'datatrans_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_id'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>100, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''"
        ),
        'datatrans_sign' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_sign'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''"
        ),
        'sparkasse_paymentmethod' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('creditcard', 'maestro', 'directdebit'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'sparkasse_sslmerchant' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_sslmerchant'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''"
        ),
        'sparkasse_sslpassword' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_sslpassword'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'sparkasse_merchantref' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_merchantref'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'sofortueberweisung_user_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sofortueberweisung_user_id'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sofortueberweisung_project_id' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sofortueberweisung_project_id'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sofortueberweisung_project_password' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sofortueberweisung_project_password'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_accountid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['saferpay_accountid'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'saferpay_description' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['saferpay_description'],
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_vtconfig' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['saferpay_vtconfig'],
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'expercash_popupId' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupId'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''"
        ),
        'expercash_profile' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_profile'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>3, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(3) NOT NULL default '0'"
        ),
        'expercash_popupKey' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_popupKey'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'expercash_paymentMethod' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('automatic_payment_method', 'elv_buy', 'elv_authorize', 'cc_buy', 'cc_authorize', 'giropay', 'sofortueberweisung'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_paymentMethod'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'expercash_css' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expercash_css'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'css', 'tl_class'=>'clr'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'payone_clearingtype' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_clearingtype'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('elv', 'cc', 'dc', 'vor', 'rec', 'sb', 'wlt'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone'],
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''"
        ),
        'payone_aid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_aid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(6) NOT NULL default ''"
        ),
        'payone_portalid' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_portalid'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>7, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(7) NOT NULL default ''"
        ),
        'payone_key' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payone_key'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'worldpay_instId' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_instId'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(6) NOT NULL default '0'",
        ),
        'worldpay_callbackPW' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_callbackPW'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'worldpay_signatureFields' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_signatureFields'],
            'exclude'               => true,
            'default'               => 'instId:cartId:amount:currency',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'worldpay_md5secret' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_md5secret'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'worldpay_description' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['worldpay_description'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'requireCCV' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'guests' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'protected' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'groups' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('multiple'=>true),
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
            'sql'                   => "blob NULL"
        ),
        'debug' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'enabled' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''"
        ),
    )
);
