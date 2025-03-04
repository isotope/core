<?php

use Contao\DC_Table;
use Contao\StringUtil;
use Isotope\Backend;
use Isotope\Backend\Payment\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;
use Isotope\Model\Payment;
use Isotope\Model\Payment\OpenPaymentPlatform;
use Isotope\Model\Payment\PSP;
use Isotope\Model\ProductType;
use Isotope\Model\TaxClass;

/*
 * Table tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
            [Callback::class, 'loadShippingModules'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['name'],
            'flag' => 1,
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'button_callback' => [Callback::class, 'copyPaymentModule'],
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [Callback::class, 'deletePaymentModule'],
            ],
            'toggle' => [
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [Callback::class, 'toggleIcon'],
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'opp_auth', 'protected'],
        'default' => '{type_legend},name,label,type',
        'cash' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled',
        'concardis' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'paybyway' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paybyway_merchant_id,paybyway_private_key;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'paypal' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paypal_account;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'paypal_plus' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paypal_client,paypal_secret;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'paypal_checkout' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},paypal_client,paypal_secret;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'postfinance' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template,psp_payment_method;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'viveum' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},psp_pspid,psp_http_method,psp_hash_method,psp_hash_in,psp_hash_out,psp_dynamic_template,psp_payment_method;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'datatrans' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},datatrans_id,datatrans_sign,datatrans_hash_method,datatrans_hash_convert;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'innopay' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},vads_site_id,vads_certificate;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'sparkasse' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},sparkasse_paymentmethod,trans_type,sparkasse_sslmerchant,sparkasse_sslpassword,sparkasse_merchantref;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'sofortueberweisung' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend:hide},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},trans_type,sofortueberweisung_user_id,sofortueberweisung_project_id,sofortueberweisung_project_password;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,logging',
        'saferpay' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},saferpay_accountid,trans_type,saferpay_username,saferpay_password,saferpay_description,saferpay_vtconfig,saferpay_paymentmethods;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'billpay_saferpay' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},saferpay_accountid,trans_type,saferpay_description,saferpay_vtconfig,saferpay_paymentmethods;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'expercash' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},expercash_popupId,expercash_profile,expercash_popupKey,expercash_paymentMethod;{price_legend:hide},price,tax_class;{template_legend},expercash_css;{expert_legend:hide},protected;{enabled_legend},enabled,logging',
        'epay' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},trans_type,epay_windowstate,epay_merchantnumber,epay_secretkey;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,logging',
        'payone' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},trans_type,payone_clearingtype,payone_aid,payone_portalid,payone_key;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'worldpay' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},worldpay_instId,worldpay_callbackPW,worldpay_signatureFields,worldpay_md5secret,worldpay_description;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'quickpay' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},quickpay_merchantId,quickpay_agreementId,quickpay_apiKey,quickpay_privateKey,quickpay_paymentMethods;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'opp' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},opp_entity_id,opp_auth,opp_user_id,opp_password,opp_brands;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'opptoken' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},opp_entity_id,opp_auth,opp_token,opp_brands;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'mpay24' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},mpay24_merchant,mpay24_password;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
        'swissbilling' => '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types,product_types_condition,config_ids;{gateway_legend},swissbilling_id,swissbilling_pwd,swissbilling_prescreening,swissbilling_b2b;{price_legend:hide},price,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled,debug,logging',
    ],

    // Subpalettes
    'subpalettes' => [
        'protected' => 'groups',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'default' => 'cash',
            'options_callback' => static fn () => Payment::getModelTypeOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_payment'],
            'eval' => ['includeBlankOption' => true, 'helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'note' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE'],
            'sql' => 'text NULL',
        ],
        'new_order_status' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => OrderStatus::getTable().'.name',
            'options_callback' => [Backend::class, 'getOrderStatus'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'price' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'rgxp' => 'surcharge', 'nullIfEmpty' => true, 'tl_class' => 'w50'],
            'sql' => 'varchar(16) NULL',
        ],
        'tax_class' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => TaxClass::getTable().'.name',
            'options_callback' => [TaxClass::class, 'getOptionsWithSplit'],
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        // @deprecated Deprecated since 2.2, to be removed in 3.0. Create your own field instead.
        'allowed_cc_types' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'options_callback' => [Callback::class, 'getAllowedCCTypes'],
            'eval' => ['multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'trans_type' => [
            'exclude' => true,
            'default' => 'capture',
            'inputType' => 'select',
            'options' => ['capture', 'auth'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'helpwizard' => true],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'minimum_total' => [
            'exclude' => true,
            'inputType' => 'text',
            'default' => 0,
            'eval' => ['maxlength' => 255, 'rgxp' => 'digit', 'tl_class' => 'clr w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'maximum_total' => [
            'exclude' => true,
            'inputType' => 'text',
            'default' => 0,
            'eval' => ['maxlength' => 255, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'quantity_mode' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => [
                Payment::QUANTITY_MODE_ITEMS,
                Payment::QUANTITY_MODE_PRODUCTS,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['quantity_mode'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'minimum_quantity' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'clr w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'maximum_quantity' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'countries' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'shipping_modules' => [
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'product_types' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'clr w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'product_types_condition' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['onlyAvailable', 'notAvailable', 'allAvailable', 'oneAvailable'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'config_ids' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => Config::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'clr w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'paybyway_merchant_id' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'paybyway_private_key' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'paypal_account' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'rgxp' => 'email', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'paypal_client' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'paypal_secret' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'psp_pspid' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'psp_http_method' => [
            'exclude' => true,
            'inputType' => 'select',
            'default' => 'POST',
            'options' => ['POST', 'GET'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(4) NOT NULL default ''",
        ],
        'psp_hash_method' => [
            'exclude' => true,
            'default' => 'sha1',
            'inputType' => 'select',
            'options' => ['sha1', 'sha256', 'sha512'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['psp_hash_method'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'psp_hash_in' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'hideInput' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''", // Max is 512 bit hash = 128 hex digits
        ],
        'psp_hash_out' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'hideInput' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''", // Max is 512 bit hash = 128 hex digits
        ],
        'psp_dynamic_template' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 128, 'rgxp' => 'url', 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'psp_payment_method' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static function ($dc) {
                /** @var Payment $payment */
                $payment = Payment::findById($dc->id);

                if ($payment instanceof PSP) {
                    return $payment->getPaymentMethods();
                }

                return [];
            },
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'clr'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'datatrans_id' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'datatrans_sign' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 128, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'datatrans_hash_convert' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default '0'",
            'eval' => ['tl_class' => 'w50 m12'],
        ],
        'datatrans_hash_method' => [
            'exclude' => true,
            'default' => 'md5',
            'inputType' => 'select',
            'options' => ['md5', 'sha256'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_hash_method'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "char(6) NOT NULL default 'md5'",
        ],
        'vads_site_id' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 8, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'vads_certificate' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'sparkasse_paymentmethod' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['creditcard', 'maestro', 'directdebit'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'sparkasse_sslmerchant' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'sparkasse_sslpassword' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'hideInput' => true, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'sparkasse_merchantref' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'clr long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'sofortueberweisung_user_id' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'sofortueberweisung_project_id' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'sofortueberweisung_project_password' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'saferpay_accountid' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'saferpay_username' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'saferpay_password' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'minlength' => 16, 'maxlength' => 32, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'saferpay_description' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'saferpay_vtconfig' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'saferpay_paymentmethods' => [
            'inputType' => 'select',
            'options_callback' => static function ($dc) {
                if (!$dc->activeRecord->saferpay_username) {
                    return [
                        1 => 'MasterCard',
                        2 => 'Visa',
                        3 => 'American Express',
                        4 => 'Diners Club',
                        5 => 'JCB',
                        6 => 'Saferpay Testkarte',
                        7 => 'Laser Card',
                        8 => 'Bonus Card',
                        9 => 'PostFinance E-Finance',
                        10 => 'PostFinance Card',
                        11 => 'Maestro International',
                        12 => 'MyOne',
                        13 => 'Lastschrift',
                        14 => 'Rechnung',
                        15 => 'Sofortüberweisung',
                        16 => 'PayPal',
                        17 => 'giropay',
                        18 => 'iDEAL',
                        19 => 'ClickandBuy',
                        20 => 'Homebanking AT (eps)',
                        21 => 'Mpass',
                        22 => 'ePrzelewy',
                    ];
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
            'eval' => ['multiple' => true, 'size' => 5, 'chosen' => true, 'csv' => ',', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'expercash_popupId' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 10, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'expercash_profile' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 3, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(3) NOT NULL default '0'",
        ],
        'expercash_popupKey' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'expercash_paymentMethod' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['automatic_payment_method', 'elv_buy', 'elv_authorize', 'cc_buy', 'cc_authorize', 'giropay', 'sofortueberweisung'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'expercash_css' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'css', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],
        'epay_windowstate' => [
            'exclude' => true,
            'default' => '3',
            'inputType' => 'select',
            'options' => ['3', '4'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['epay_windowstate_options'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'epay_merchantnumber' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'epay_secretkey' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'payone_clearingtype' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['elv', 'cc', 'dc', 'vor', 'rec', 'sb', 'wlt', 'fnc'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['payone'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(3) NOT NULL default ''",
        ],
        'payone_aid' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 6, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'payone_portalid' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 7, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(7) NOT NULL default ''",
        ],
        'payone_key' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'worldpay_instId' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 6, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(6) NOT NULL default '0'",
        ],
        'worldpay_callbackPW' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'worldpay_signatureFields' => [
            'exclude' => true,
            'default' => 'instId:cartId:amount:currency',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'worldpay_md5secret' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'worldpay_description' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr long'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'quickpay_merchantId' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 10, 'rgpx' => 'digit', 'tl_class' => 'w50'],
            'sql' => 'int(10) NULL',
        ],
        'quickpay_agreementId' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 10, 'rgpx' => 'digit', 'tl_class' => 'w50'],
            'sql' => 'int(10) NULL',
        ],
        'quickpay_apiKey' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'quickpay_privateKey' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 64, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'quickpay_paymentMethods' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'clr long'],
            'sql' => 'text NULL',
        ],
        'opp_entity_id' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'rgpx' => 'alnum', 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'opp_auth' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['user', 'token'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['opp_auth'],
            'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'opp_token' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'useRawRequestData' => true, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'opp_user_id' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'rgpx' => 'alnum', 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'opp_password' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'rgxp' => 'alnum', 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'opp_brands' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'options' => array_keys(OpenPaymentPlatform::$paymentBrands),
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_payment']['opp_brands'],
            'eval' => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => 'blob NULL',
            'save_callback' => [
                static function ($value) {
                    $brands = StringUtil::deserialize($value);

                    if (!empty($brands) && is_array($brands)) {
                        if (!OpenPaymentPlatform::supportsPaymentBrands($brands)) {
                            throw new RuntimeException($GLOBALS['TL_LANG']['ERR']['oppIncompatible']);
                        }

                        if (strlen(implode(' ', $brands)) > 32) {
                            throw new RuntimeException($GLOBALS['TL_LANG']['ERR']['oppTooMany']);
                        }
                    }

                    return $value;
                },
            ],
        ],
        'mpay24_merchant' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'minlength' => 5, 'maxlength' => 5, 'rgpx' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'mpay24_password' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'swissbilling_id' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'swissbilling_pwd' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 32, 'decodeEntities' => true, 'hideInput' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'swissbilling_prescreening' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default '0'",
            'eval' => ['tl_class' => 'w50'],
        ],
        'swissbilling_b2b' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default '0'",
            'eval' => ['tl_class' => 'w50'],
        ],
        'requireCCV' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'protected' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'debug' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'logging' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enabled' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
