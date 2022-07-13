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
 * Table tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'backlink'                  => 'do=iso_setup',
        'onload_callback' => array
        (
            array('Isotope\Backend\Payment\Callback', 'checkPermission'),
            array('Isotope\Backend\Payment\Callback', 'loadShippingModules'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            ),
        ),
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
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\Backend\Payment\Callback', 'copyPaymentModule'),
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Payment\Callback', 'deletePaymentModule'),
            ),
            'toggle' => array
            (
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'   => array('Isotope\Backend\Payment\Callback', 'toggleIcon')
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type', 'opp_auth', 'protected'),
        'default'                   => '{type_legend},name,label,type',
        'cash'                      => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'concardis'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'paybyway'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paybyway_merchant_id,paybyway_private_key;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'paypal'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'paypal_plus'               => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paypal_client,paypal_secret;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'postfinance'               => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template,psp_payment_method;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'viveum'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template,psp_payment_method;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'datatrans'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},datatrans_id,datatrans_sign,datatrans_hash_method,datatrans_hash_convert;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'innopay'                   => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},vads_site_id,vads_certificate;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'sparkasse'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},sparkasse_paymentmethod,trans_type,sparkasse_sslmerchant,sparkasse_sslpassword,sparkasse_merchantref;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'sofortueberweisung'        => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},trans_type,sofortueberweisung_user_id,sofortueberweisung_project_id,sofortueberweisung_project_password;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,logging',
        'saferpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},saferpay_accountid,trans_type,saferpay_username,saferpay_password,saferpay_description,saferpay_vtconfig,saferpay_paymentmethods;{price_legend:hide},price,tax_class;{enabled_legend},enabled,debug,logging',
        'billpay_saferpay'          => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},saferpay_accountid,trans_type,saferpay_description,saferpay_vtconfig,saferpay_paymentmethods;{price_legend:hide},price,tax_class;{enabled_legend},enabled,debug,logging',
        'expercash'                 => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},expercash_popupId,expercash_profile,expercash_popupKey,expercash_paymentMethod;{price_legend:hide},price,tax_class;{template_legend},expercash_css;{expert_legend:hide},guests,protected;{enabled_legend},enabled,logging',
        'epay'                      => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},trans_type,epay_windowstate,epay_merchantnumber,epay_secretkey;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,logging',
        'payone'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},trans_type,payone_clearingtype,payone_aid,payone_portalid,payone_key;{price_legend:hide},price,tax_class;{enabled_legend},enabled,debug,logging',
        'worldpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},worldpay_instId,worldpay_callbackPW,worldpay_signatureFields,worldpay_md5secret,worldpay_description;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'quickpay'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},quickpay_merchantId,quickpay_agreementId,quickpay_apiKey,quickpay_privateKey,quickpay_paymentMethods;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'opp'                       => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},opp_entity_id,opp_auth,opp_user_id,opp_password,opp_brands;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'opptoken'                  => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},opp_entity_id,opp_auth,opp_token,opp_brands;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'mpay24'                    => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},mpay24_merchant,mpay24_password;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
        'swissbilling'              => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},swissbilling_id,swissbilling_pwd,swissbilling_prescreening,swissbilling_b2b;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
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
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'type' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'default'               => 'cash',
            'options_callback'      => function() {
                return \Isotope\Model\Payment::getModelTypeOptions();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'],
            'eval'                  => array('includeBlankOption'=>true, 'helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'note' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('rte'=>'tinyMCE'),
            'sql'                   => "text NULL",
        ),
        'new_order_status' => array
        (
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
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>16, 'rgxp'=>'surcharge', 'nullIfEmpty'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NULL",
        ),
        'tax_class' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\TaxClass::getTable().'.name',
            'options_callback'      => array('\Isotope\Model\TaxClass', 'getOptionsWithSplit'),
            'eval'                  => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class'=>'w50'),
            'sql'                   => "int(10) NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        // @deprecated Deprecated since 2.2, to be removed in 3.0. Create your own field instead.
        'allowed_cc_types' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'options_callback'      => array('Isotope\Backend\Payment\Callback', 'getAllowedCCTypes'),
            'eval'                  => array('multiple'=>true, 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'trans_type' => array
        (
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
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 0,
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'digit', 'tl_class'=>'clr w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'maximum_total' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'default'               => 0,
            'eval'                  => array('maxlength'=>255, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'quantity_mode' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => [
                \Isotope\Model\Payment::QUANTITY_MODE_ITEMS,
                \Isotope\Model\Payment::QUANTITY_MODE_PRODUCTS,
            ],
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['quantity_mode'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'minimum_quantity' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'clr w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'maximum_quantity' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'countries' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Contao\System::getCountries();
            },
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'shipping_modules' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'product_types' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\ProductType::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'product_types_condition' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('onlyAvailable', 'notAvailable', 'allAvailable', 'oneAvailable'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'config_ids' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\Config::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'paybyway_merchant_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) NOT NULL default '0'",
        ),
        'paybyway_private_key' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'paypal_account' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'paypal_client' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'paypal_secret' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'psp_pspid' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'psp_http_method' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'default'               => 'POST',
            'options'               => array('POST', 'GET'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(4) NOT NULL default ''",
        ),
        'psp_hash_method' => array
        (
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
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''", // Max is 512 bit hash = 128 hex digits
        ),
        'psp_hash_out' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''", // Max is 512 bit hash = 128 hex digits
        ),
        'psp_dynamic_template' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>128, 'rgxp'=>'url', 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'psp_payment_method' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function($dc) {
                /** @var \Isotope\Model\Payment $payment */
                $payment = \Isotope\Model\Payment::findByPk($dc->id);

                if ($payment instanceof \Isotope\Model\Payment\PSP) {
                    return $payment->getPaymentMethods();
                }

                return [];
            },
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'clr'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'datatrans_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'datatrans_sign' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>128, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'datatrans_hash_convert' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default '0'",
            'eval'                  => array('tl_class' => 'w50 m12'),
        ),
        'datatrans_hash_method' => array
        (
            'exclude'               => true,
            'default'               => 'md5',
            'inputType'             => 'select',
            'options'               => array('md5', 'sha256'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_hash_method'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(6) NOT NULL default 'md5'",
        ),
        'vads_site_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>8, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'vads_certificate' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sparkasse_paymentmethod' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('creditcard', 'maestro', 'directdebit'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'sparkasse_sslmerchant' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sparkasse_sslpassword' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'hideInput'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'sparkasse_merchantref' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'sofortueberweisung_user_id' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sofortueberweisung_project_id' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'sofortueberweisung_project_password' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_accountid' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'saferpay_username' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'saferpay_password' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'minlength'=>16, 'maxlength'=>32, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'saferpay_description' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_vtconfig' => array
        (
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'saferpay_paymentmethods' => array
        (
            'inputType'             => 'select',
            'options_callback'      => function($dc) {
                if (!$dc->activeRecord->saferpay_username) {
                    return array(
                        1   => 'MasterCard',
                        2   => 'Visa',
                        3   => 'American Express',
                        4   => 'Diners Club',
                        5   => 'JCB',
                        6   => 'Saferpay Testkarte',
                        7   => 'Laser Card',
                        8   => 'Bonus Card',
                        9   => 'PostFinance E-Finance',
                        10  => 'PostFinance Card',
                        11  => 'Maestro International',
                        12  => 'MyOne',
                        13  => 'Lastschrift',
                        14  => 'Rechnung',
                        15  => 'Sofortüberweisung',
                        16  => 'PayPal',
                        17  => 'giropay',
                        18  => 'iDEAL',
                        19  => 'ClickandBuy',
                        20  => 'Homebanking AT (eps)',
                        21  => 'Mpass',
                        22  => 'ePrzelewy',
                    );
                }

                return [
                    'ALIPAY',
                    'AMEX',
                    'BANCONTACT',
                    'BONUS',
                    'DINERS',
                    'DIRECTDEBIT',
                    'EPRZELEWY',
                    'EPS',
                    'GIROPAY',
                    'IDEAL',
                    'INVOICE',
                    'JCB',
                    'MAESTRO',
                    'MASTERCARD',
                    'MYONE',
                    'PAYPAL',
                    'PAYDIREKT',
                    'POSTCARD',
                    'POSTFINANCE',
                    'SAFERPAYTEST',
                    'SOFORT',
                    'TWINT',
                    'UNIONPAY',
                    'VISA',
                    'VPAY',
                ];
            },
            'eval'                  => array('multiple'=>true, 'size'=>5, 'chosen'=>true, 'csv'=>',', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'expercash_popupId' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''"
        ),
        'expercash_profile' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>3, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(3) NOT NULL default '0'"
        ),
        'expercash_popupKey' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'expercash_paymentMethod' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('automatic_payment_method', 'elv_buy', 'elv_authorize', 'cc_buy', 'cc_authorize', 'giropay', 'sofortueberweisung'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''"
        ),
        'expercash_css' => array
        (
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'css', 'tl_class'=>'clr'),
            'sql'                   => "binary(16) NULL"
        ),
        'epay_windowstate' => array
        (
            'exclude'               => true,
            'default'               => '3',
            'inputType'             => 'select',
            'options'               => array('3', '4'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['epay_windowstate_options'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''"
        ),
        'epay_merchantnumber' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'epay_secretkey' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'payone_clearingtype' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('elv', 'cc', 'dc', 'vor', 'rec', 'sb', 'wlt', 'fnc'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone'],
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''"
        ),
        'payone_aid' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(6) NOT NULL default ''"
        ),
        'payone_portalid' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>7, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(7) NOT NULL default ''"
        ),
        'payone_key' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'worldpay_instId' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>6, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(6) NOT NULL default '0'",
        ),
        'worldpay_callbackPW' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'worldpay_signatureFields' => array
        (
            'exclude'               => true,
            'default'               => 'instId:cartId:amount:currency',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'worldpay_md5secret' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'worldpay_description' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'quickpay_merchantId' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'rgpx'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) NULL",
        ),
        'quickpay_agreementId' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>10, 'rgpx'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(10) NULL",
        ),
        'quickpay_apiKey' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'quickpay_privateKey' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>64, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'quickpay_paymentMethods' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('decodeEntities'=>true, 'tl_class'=>'clr long'),
            'sql'                   => "text NULL",
        ),
        'opp_entity_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'rgpx'=>'alnum', 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'opp_auth' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => ['user', 'token'],
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['opp_auth'],
            'eval'                  => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'opp_token' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'useRawRequestData'=>true, 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'opp_user_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'rgpx'=>'alnum', 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'opp_password' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'rgxp'=>'alnum', 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'opp_brands' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'options'               => array_keys(\Isotope\Model\Payment\OpenPaymentPlatform::$paymentBrands),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_payment']['opp_brands'],
            'eval'                  => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "blob NULL",
            'save_callback' => [
                function($value) {
                    $brands = \Contao\StringUtil::deserialize($value);

                    if (!empty($brands) && \is_array($brands)) {
                        if (!\Isotope\Model\Payment\OpenPaymentPlatform::supportsPaymentBrands($brands)) {
                            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['oppIncompatible']);
                        }

                        if (\strlen(implode(' ', $brands)) > 32) {
                            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['oppTooMany']);
                        }
                    }

                    return $value;
                }
            ]
        ),
        'mpay24_merchant' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'minlength'=>5, 'maxlength'=>5, 'rgpx'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(5) NOT NULL default ''",
        ),
        'mpay24_password' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'swissbilling_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'swissbilling_pwd' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'swissbilling_prescreening' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default '0'",
            'eval'                  => array('tl_class' => 'w50'),
        ),
        'swissbilling_b2b' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default '0'",
            'eval'                  => array('tl_class' => 'w50'),
        ),
        'requireCCV' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'guests' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'protected' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'groups' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('multiple'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'debug' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => ['tl_class' => 'clr w50'],
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'logging' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => ['tl_class' => 'w50'],
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'enabled' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => ['tl_class' => 'w50'],
            'sql'                   => "char(1) NOT NULL default ''",
        ),
    )
);
