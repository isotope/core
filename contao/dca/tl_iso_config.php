<?php

use Contao\BackendUser;
use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Backend\Config\AddressFieldsWizard;
use Isotope\Backend\Config\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\Config;
use Isotope\Model\OrderStatus;

/*
 * Table tl_iso_config
 */
$GLOBALS['TL_DCA']['tl_iso_config'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
            [Backend\OrderStatus\Callback::class, 'addDefault'],
        ],
        'onsubmit_callback' => [
            [Backend::class, 'truncateProductCache'],
            [Callback::class, 'convertCurrencies'],
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
        ],
        'label' => [
            'fields' => ['name', 'fallback'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback' => [Callback::class, 'addIcon'],
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
                'button_callback' => [Callback::class, 'copyConfig'],
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [Callback::class, 'deleteConfig'],
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['currencySymbol', 'currencyAutomator'],
        'default' => '
            {name_legend},name,label,fallback;
            {address_legend:hide},firstname,lastname,company,vat_no,street_1,street_2,street_3,postal,city,country,subdivision,email,phone;
            {bank_legend:hide},bankName,bankAccount,bankCode,taxNumber;
            {checkout_legend},address_fields,billing_country,shipping_country,billing_countries,shipping_countries,limitMemberCountries,vatNoValidators;
            {pricing_legend},priceDisplay,currencyFormat,priceRoundPrecision,priceRoundIncrement;
            {currency_legend},currency,currencyPosition,currencySymbol;
            {converter_legend:hide},priceCalculateFactor,priceCalculateMode,currencyAutomator;
            {order_legend:hide},orderPrefix,orderDigits,orderstatus_new,orderstatus_error,orderDetailsModule;
            {config_legend},templateGroup,cartMinSubtotal;
            {products_legend},newProductPeriod',
    ],

    // Subpalettes
    'subpalettes' => [
        'currencySymbol' => 'currencySpace',
        'currencyAutomator' => 'currencyOrigin,currencyProvider',
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
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fallback' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'fallback' => true, 'tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'firstname' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'lastname' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'company' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'vat_no' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_1' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_2' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_3' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'postal' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'clr w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'city' => [
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'subdivision' => [
            'exclude' => true,
            'sorting' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['conditionField' => 'country', 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'country' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'default' => (string) BackendUser::getInstance()->country,
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'phone' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'email' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'email', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'bankName' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'bankAccount' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 34, 'tl_class' => 'w50'],
            'sql' => "varchar(34) NOT NULL default ''",
        ],
        'bankCode' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'taxNumber' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'address_fields' => [
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'tl_class' => 'clr',
                'dragAndDrop' => true,
                'buttons' => ['new' => false, 'copy' => false, 'delete' => false],
                'columnFields' => [
                    'name' => [
                        'label' => ['&nbsp;'],
                        'input_field_callback' => [AddressFieldsWizard::class, 'getNextName'],
                        'eval' => ['tl_class' => 'mcwUpdateFields'],
                    ],
                    'billing' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['billing'],
                        'inputType' => 'select',
                        'options' => ['disabled', 'enabled', 'mandatory'],
                        'reference' => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields'],
                        'eval' => ['style' => 'width:140px'],
                    ],
                    'shipping' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['shipping'],
                        'inputType' => 'select',
                        'options' => ['disabled', 'enabled', 'mandatory'],
                        'reference' => &$GLOBALS['TL_LANG']['tl_iso_config']['address_fields'],
                        'eval' => ['style' => 'width:140px'],
                    ],
                ],
            ],
            'load_callback' => [
                [AddressFieldsWizard::class, 'load'],
            ],
            'save_callback' => [
                [AddressFieldsWizard::class, 'save'],
            ],
            'sql' => 'blob NULL',
        ],
        'billing_country' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'shipping_country' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'billing_countries' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'shipping_countries' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'limitMemberCountries' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'vatNoValidators' => [
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options' => array_keys($GLOBALS['ISO_VAT'] ?? []),
            'reference' => $GLOBALS['TL_LANG']['ISO_VAT'] ?? [],
            'eval' => ['multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'priceDisplay' => [
            'exclude' => true,
            'default' => 'gross',
            'inputType' => 'select',
            'options' => [Config::PRICE_DISPLAY_NET, Config::PRICE_DISPLAY_GROSS, Config::PRICE_DISPLAY_FIXED, Config::PRICE_DISPLAY_LEGACY],
            'reference' => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'currencyFormat' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => array_keys($GLOBALS['ISO_NUM']),
            'eval' => ['includeBlankOption' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'priceRoundPrecision' => [
            'exclude' => true,
            'default' => '2',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 1, 'rgpx' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '2'",
        ],
        'priceRoundIncrement' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['0.01', '0.05'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(4) NOT NULL default ''",
        ],
        'cartMinSubtotal' => [
            'exclude' => true,
            'default' => '0.00',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 13, 'rgpx' => 'price', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'currency' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => &$GLOBALS['TL_LANG']['CUR'],
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(3) NOT NULL default ''",
        ],
        'currencySymbol' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'currencySpace' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'currencyPosition' => [
            'exclude' => true,
            'inputType' => 'radio',
            'default' => 'left',
            'options' => ['left', 'right'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'priceCalculateFactor' => [
            'exclude' => true,
            'default' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'priceCalculateMode' => [
            'exclude' => true,
            'default' => 'mul',
            'inputType' => 'radio',
            'options' => ['mul', 'div'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(3) NOT NULL default ''",
        ],
        'currencyAutomator' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'save_callback' => [
                [Callback::class, 'checkNeedToConvertCurrencies'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'currencyOrigin' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => &$GLOBALS['TL_LANG']['CUR'],
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'clr w50'],
            'save_callback' => [
                [Callback::class, 'checkNeedToConvertCurrencies'],
            ],
            'sql' => "varchar(3) NOT NULL default ''",
        ],
        'currencyProvider' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['ecb_int', 'admin_ch'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'save_callback' => [
                [Callback::class, 'checkNeedToConvertCurrencies'],
            ],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'orderPrefix' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'orderDigits' => [
            'exclude' => true,
            'default' => 4,
            'inputType' => 'select',
            'options' => range(1, 9),
            'eval' => ['tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '4'",
        ],
        'orderstatus_new' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => OrderStatus::getTable().'.name',
            'options_callback' => [Backend::class, 'getOrderStatus'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'orderstatus_error' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => OrderStatus::getTable().'.name',
            'options_callback' => [Backend::class, 'getOrderStatus'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'orderDetailsModule' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getOrderDetailsModules'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'table' => 'tl_module'],
        ],
        'templateGroup' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getTemplateFolders'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'newProductPeriod' => [
            'exclude' => true,
            'default' => ['unit' => 'days', 'value' => ''],
            'inputType' => 'timePeriod',
            'options' => ['minutes', 'hours', 'days', 'weeks', 'months', 'years'],
            'reference' => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval' => ['rgxp' => 'digit', 'maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
