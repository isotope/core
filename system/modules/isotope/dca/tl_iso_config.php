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
 * Table tl_iso_config
 */
$GLOBALS['TL_DCA']['tl_iso_config'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'backlink'                  => 'do=iso_setup',
        'onload_callback' => array
        (
            array('Isotope\Backend\Config\Callback', 'checkPermission'),
            array('Isotope\Backend\OrderStatus\Callback', 'addDefault'),
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend', 'truncateProductCache'),
            array('Isotope\Backend\Config\Callback', 'convertCurrencies'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
            )
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
        ),
        'label' => array
        (
            'fields'                => array('name', 'fallback'),
            'format'                => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback'        => array('Isotope\Backend\Config\Callback', 'addIcon')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'              => 'act=edit',
                'icon'              => 'edit.svg',
            ),
            'copy' => array
            (
                'href'              => 'act=copy',
                'icon'              => 'copy.svg',
                'button_callback'   => array('Isotope\Backend\Config\Callback', 'copyConfig'),
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.svg',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Config\Callback', 'deleteConfig'),
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.svg',
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('currencySymbol', 'currencyAutomator', 'ga_enable'),
        'default'                   => '
            {name_legend},name,label,fallback;
            {address_legend:hide},firstname,lastname,company,vat_no,street_1,street_2,street_3,postal,city,country,subdivision,email,phone;
            {bank_legend:hide},bankName,bankAccount,bankCode,taxNumber;
            {checkout_legend},address_fields,billing_country,shipping_country,billing_countries,shipping_countries,limitMemberCountries,vatNoValidators;
            {pricing_legend},priceDisplay,currencyFormat,priceRoundPrecision,priceRoundIncrement;
            {currency_legend},currency,currencyPosition,currencySymbol;
            {converter_legend:hide},priceCalculateFactor,priceCalculateMode,currencyAutomator;
            {order_legend:hide},orderPrefix,orderDigits,orderstatus_new,orderstatus_error,orderDetailsModule;
            {config_legend},templateGroup,cartMinSubtotal;
            {products_legend},newProductPeriod;
            {analytics_legend},ga_enable',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'currencySymbol'            => 'currencySpace',
        'currencyAutomator'         => 'currencyOrigin,currencyProvider',
        'ga_enable'                 => 'ga_account,ga_member',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'fallback' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50 m12'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'firstname' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'lastname' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'company' => array
        (
            'exclude'               => true,
            'search'                => true,
            'sorting'               => true,
            'flag'                  => 1,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'vat_no' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_1' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_2' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'street_3' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'postal' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>32, 'tl_class'=>'clr w50'),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'city' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'search'                => true,
            'sorting'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'subdivision' => array
        (
            'exclude'               => true,
            'sorting'               => true,
            'inputType'             => 'conditionalselect',
            'options_callback'      => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                  => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'country' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'sorting'               => true,
            'inputType'             => 'select',
            'default'               => (string) \Contao\BackendUser::getInstance()->country,
            'options_callback'      => function() {
                return \Contao\System::getCountries();
            },
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(2) NOT NULL default ''",
        ),
        'phone' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'email' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'bankName' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'bankAccount' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>34, 'tl_class'=>'w50'),
            'sql'                   => "varchar(34) NOT NULL default ''",
        ),
        'bankCode' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>16, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'taxNumber' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'address_fields' => array
        (
            'exclude'               => true,
            'inputType'             => 'multiColumnWizard',
            'eval' => array
            (
                'tl_class'          => 'clr',
                'dragAndDrop'       => true,
                'buttons'           => array('new'=>false, 'copy'=>false, 'delete'=>false),
                'columnFields'      => array
                (
                    'name' => array
                    (
                        'label'                 => ['&nbsp;'],
                        'input_field_callback'  => array('Isotope\Backend\Config\AddressFieldsWizard', 'getNextName'),
                        'eval'                  => array('tl_class'=>'mcwUpdateFields'),
                    ),
                    'billing' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['billing'],
                        'inputType'             => 'select',
                        'options'               => array('disabled', 'enabled', 'mandatory'),
                        'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields'],
                        'eval'                  => array('style'=>'width:140px'),
                    ),
                    'shipping' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['shipping'],
                        'inputType'             => 'select',
                        'options'               => array('disabled', 'enabled', 'mandatory'),
                        'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields'],
                        'eval'                  => array('style'=>'width:140px'),
                    ),
                ),
            ),
            'load_callback' => array
            (
                array('Isotope\Backend\Config\AddressFieldsWizard', 'load'),
            ),
            'save_callback' => array
            (
                array('Isotope\Backend\Config\AddressFieldsWizard', 'save'),
            ),
            'sql'                   => "blob NULL",
        ),
        'billing_country' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Contao\System::getCountries();
            },
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(2) NOT NULL default ''",
        ),
        'shipping_country' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Contao\System::getCountries();
            },
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "varchar(2) NOT NULL default ''",
        ),
        'billing_countries' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Contao\System::getCountries();
            },
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL"
        ),
        'shipping_countries' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \Contao\System::getCountries();
            },
            'eval'                  => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "blob NULL",
        ),
        'limitMemberCountries' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'vatNoValidators' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkboxWizard',
            'options'               => array_keys($GLOBALS['ISO_VAT'] ?? []),
            'reference'             => $GLOBALS['TL_LANG']['ISO_VAT'] ?? [],
            'eval'                  => array('multiple'=>true, 'tl_class'=>'clr'),
            'sql'                   => "blob NULL",
        ),
        'priceDisplay' => array
        (
            'exclude'               => true,
            'default'               => 'gross',
            'inputType'             => 'select',
            'options'               => array(\Isotope\Model\Config::PRICE_DISPLAY_NET, \Isotope\Model\Config::PRICE_DISPLAY_GROSS, \Isotope\Model\Config::PRICE_DISPLAY_FIXED, \Isotope\Model\Config::PRICE_DISPLAY_LEGACY),
            'reference'             => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'helpwizard'=>true),
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'currencyFormat' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array_keys($GLOBALS['ISO_NUM']),
            'eval'                  => array('includeBlankOption'=>true, 'decodeEntities'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(20) NOT NULL default ''",
        ),
        'priceRoundPrecision' => array
        (
            'exclude'               => true,
            'default'               => '2',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>1, 'rgpx'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(1) unsigned NOT NULL default '2'",
        ),
        'priceRoundIncrement' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('0.01', '0.05'),
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(4) NOT NULL default ''",
        ),
        'cartMinSubtotal' => array
        (
            'exclude'               => true,
            'default'               => '0.00',
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>13, 'rgpx'=>'price', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'currency' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => &$GLOBALS['TL_LANG']['CUR'],
            'eval'                  => array('includeBlankOption'=>true, 'mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''",
        ),
        'currencySymbol' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'currencySpace' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'currencyPosition' => array
        (
            'exclude'               => true,
            'inputType'             => 'radio',
            'default'               => 'left',
            'options'               => array('left', 'right'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(5) NOT NULL default ''",
        ),
        'priceCalculateFactor' => array
        (
            'exclude'               => true,
            'default'               => 1,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'priceCalculateMode' => array
        (
            'exclude'               => true,
            'default'               => 'mul',
            'inputType'             => 'radio',
            'options'               => array('mul', 'div'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(3) NOT NULL default ''",
        ),
        'currencyAutomator' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr w50'),
            'save_callback'         => array(
                array('Isotope\Backend\Config\Callback', 'checkNeedToConvertCurrencies')
            ),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'currencyOrigin' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => &$GLOBALS['TL_LANG']['CUR'],
            'eval'                  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'clr w50'),
            'save_callback'         => array(
                array('Isotope\Backend\Config\Callback', 'checkNeedToConvertCurrencies')
            ),
            'sql'                   => "varchar(3) NOT NULL default ''",
        ),
        'currencyProvider' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('ecb_int', 'admin_ch'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'save_callback'         => array(
                array('Isotope\Backend\Config\Callback', 'checkNeedToConvertCurrencies')
            ),
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'orderPrefix' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'orderDigits' => array
        (
            'exclude'               => true,
            'default'               => 4,
            'inputType'             => 'select',
            'options'               => range(1, 9),
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "int(1) unsigned NOT NULL default '4'",
        ),
        'orderstatus_new' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\OrderStatus::getTable().'.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'orderstatus_error' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\OrderStatus::getTable().'.name',
            'options_callback'      => array('\Isotope\Backend', 'getOrderStatus'),
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'orderDetailsModule' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\Backend\Config\Callback', 'getOrderDetailsModules'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'table'=>'tl_module'),
        ),
        'templateGroup' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => array('Isotope\Backend\Config\Callback', 'getTemplateFolders'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'newProductPeriod' => array
        (
            'exclude'               => true,
            'default'               => array('unit'=>'days', 'value'=>''),
            'inputType'             => 'timePeriod',
            'options'               => array('minutes', 'hours', 'days', 'weeks', 'months', 'years'),
            'reference'             => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval'                  => array('rgxp'=>'digit', 'maxlength'=>5, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'ga_enable' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'doNotCopy'=>true, 'tl_class'=>'clr'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'ga_account' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>64, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'ga_member' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
    )
);
