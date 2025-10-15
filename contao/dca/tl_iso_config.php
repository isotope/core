<?php

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\DC_Table;
use Isotope\Backend;
use Isotope\Backend\Config\Callback;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\EventListener\DataContainer\Config\AddressFieldsListener;
use Isotope\CoreBundle\Model\OrderStatus;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\Model\Config;

$GLOBALS['TL_DCA']['tl_iso_config'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
        ],
        'label' => [
            'fields' => ['name', 'label'],
            'format' => '%s <span style="color:#999;margin-left:3px">(%s)</span>',
        ],
    ],
    'palettes' => [
        '__selector__' => ['currencySymbol'],
        'default' => '
            {name_legend},name,label;
            {address_legend:hide},firstname,lastname,company,vat_no,street_1,street_2,street_3,postal,city,country,subdivision,email,phone;
            {bank_legend:hide},bankName,bankAccount,bankCode,taxNumber;
            {checkout_legend},address_fields,billing_country,shipping_country,billing_countries,shipping_countries,limitMemberCountries,vatNoValidators;
            {pricing_legend},priceDisplay,currencyFormat,priceRoundPrecision,priceRoundIncrement;
            {currency_legend},currency,currencyPosition,currencySymbol;
            {converter_legend:hide},priceCalculateFactor;
            {order_legend:hide},orderPrefix,orderDigits,orderstatus_new,orderstatus_error,orderDetailsModule;
            {config_legend},templateGroup,cartMinSubtotal',
    ],
    'subpalettes' => [
        'currencySymbol' => 'currencySpace',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'firstname' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'lastname' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'company' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'vat_no' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_1' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_2' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'street_3' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'postal' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'clr w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'city' => [
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'subdivision' => [
            'sorting' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['conditionField' => 'country', 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'country' => [
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'default' => (string) BackendUser::getInstance()->country,
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'phone' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'email' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'email', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'bankName' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'bankAccount' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 34, 'tl_class' => 'w50'],
            'sql' => "varchar(34) NOT NULL default ''",
        ],
        'bankCode' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'taxNumber' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'address_fields' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'name' => [
                    'label' => ['&nbsp;'],
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
            'eval' => [
                'tl_class' => 'clr',
                'dragAndDrop' => true,
                'actions' => [],
            ],
            'sql' => 'blob NULL',
        ],
        'billing_country' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'shipping_country' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'billing_countries' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'text NULL',
        ],
        'shipping_countries' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'text NULL',
        ],
        'limitMemberCountries' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'vatNoValidators' => [
            'inputType' => 'checkboxWizard',
            'options' => \array_keys($GLOBALS['ISO_VAT'] ?? []),
            'reference' => $GLOBALS['TL_LANG']['ISO_VAT'] ?? [],
            'eval' => ['multiple' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        'priceDisplay' => [
            'default' => 'gross',
            'inputType' => 'select',
            'enum' => PriceDisplay::class,
            'reference' => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'currencyFormat' => [
            'inputType' => 'select',
            'options' => \array_keys($GLOBALS['ISO_NUM']),
            'eval' => ['includeBlankOption' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'priceRoundPrecision' => [
            'default' => '2',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 1, 'rgpx' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '2'",
        ],
        'priceRoundIncrement' => [
            'inputType' => 'select',
            'options' => ['0.01', '0.05'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(4) NOT NULL default ''",
        ],
        'cartMinSubtotal' => [
            'default' => '0.00',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 13, 'rgpx' => 'price', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'currency' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(3) NOT NULL default ''",
        ],
        'currencySymbol' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'currencySpace' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'currencyPosition' => [
            'inputType' => 'radio',
            'default' => 'left',
            'options' => ['left', 'right'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'priceCalculateFactor' => [
            'default' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'orderPrefix' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'orderDigits' => [
            'default' => 4,
            'inputType' => 'select',
            'options' => \range(1, 9),
            'eval' => ['tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '4'",
        ],
        'orderstatus_new' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => OrderStatus::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'orderstatus_error' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => OrderStatus::getTable().'.name',
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'orderDetailsModule' => [
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getOrderDetailsModules'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'table' => 'tl_module'],
        ],
        'templateGroup' => [
            'inputType' => 'select',
            'options_callback' => [Callback::class, 'getTemplateFolders'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
