<?php

use Contao\BackendUser;
use Contao\DC_Table;
use Contao\DataContainer;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;

$GLOBALS['TL_DCA']['tl_iso_config'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' =>  [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'userRoot' => 'iso_configs',
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
            {checkout_legend},address_fields,billing_country,shipping_country,billing_countries,shipping_countries,limitMemberCountries;
            {shipping_legend},shipping_ids;
            {payment_legend},payment_ids;
            {price_legend},priceDisplay,taxCalculation,currencyFormat,priceRoundPrecision,priceRoundIncrement,currency,currencyPosition,currencySymbol;
            {converter_legend:hide},priceCalculateFactor;
            {order_legend:hide},orderPrefix,orderDigits',
    ],
    'subpalettes' => [
        'currencySymbol' => 'currencySpace',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 0],
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'translation' => ['domain' => 'isotope'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'firstname' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'lastname' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'company' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'vat_no' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'street_1' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'street_2' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'street_3' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'postal' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'clr w50'],
            'sql' => ['type' => Types::STRING, 'length' => 32, 'default' => ''],
        ],
        'city' => [
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'subdivision' => [
            'sorting' => true,
            'inputType' => 'conditionalselect',
            'eval' => ['conditionField' => 'country', 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 10, 'default' => ''],
        ],
        'country' => [
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'default' => (string) BackendUser::getInstance()->country,
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 2, 'default' => ''],
        ],
        'phone' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'email' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'email', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'bankName' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'bankAccount' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 34, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 34, 'default' => ''],
        ],
        'bankCode' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
        ],
        'taxNumber' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
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
                'actions' => [],
            ],
            'sql' => ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false],
        ],
        'billing_country' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::STRING, 'length' => 2, 'default' => ''],
        ],
        'shipping_country' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::STRING, 'length' => 2, 'default' => ''],
        ],
        'billing_countries' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'shipping_countries' => [
            'inputType' => 'select',
            'eval' => ['multiple' => true, 'size' => 8, 'csv' => ',', 'tl_class' => 'w50', 'chosen' => true],
            'sql' => ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'limitMemberCountries' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'payment_ids' => [
            'inputType' => 'checkboxWizard',
            'foreignKey' => 'tl_iso_payment.name',
            'eval' => ['multiple' => true, 'csv' => ','],
            'sql' =>  ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'shipping_ids' => [
            'inputType' => 'checkboxWizard',
            'foreignKey' => 'tl_iso_shipping.name',
            'eval' => ['multiple' => true, 'csv' => ','],
            'sql' =>  ['type' => Types::TEXT, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT, 'notnull' => false],
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'priceDisplay' => [
            'default' => 'gross',
            'inputType' => 'select',
            'enum' => PriceDisplay::class,
            'reference' => &$GLOBALS['TL_LANG']['MSC']['iso_priceDisplay'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w33', 'helpwizard' => true],
            'sql' => ['type' => Types::STRING, 'length' => 8, 'default' => 'gross'],
        ],
        'taxCalculation' => [
            'default' => 'vertical',
            'inputType' => 'select',
            'enum' => TaxCalculation::class,
            'reference' => &$GLOBALS['TL_LANG']['MSC']['iso_taxCalculation'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w33', 'helpwizard' => true],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => 'vertical'],
        ],
        'currencyFormat' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'clr w33'],
            'sql' => ['type' => Types::STRING, 'length' => 20, 'default' => ''],
        ],
        'priceRoundPrecision' => [
            'default' => '2',
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 1, 'rgpx' => 'digit', 'tl_class' => 'w33'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 2],
        ],
        'priceRoundIncrement' => [
            'inputType' => 'select',
            'options' => ['0.01', '0.05'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w33'],
            'sql' => ['type' => Types::STRING, 'length' => 4, 'default' => ''],
        ],
        'currency' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w33'],
            'sql' => ['type' => Types::STRING, 'length' => 3, 'default' => ''],
        ],
        'currencySymbol' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w33'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'currencySpace' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w33'],
            'sql' =>  ['type' => 'boolean', 'default' => false],
        ],
        'currencyPosition' => [
            'inputType' => 'radio',
            'default' => 'left',
            'options' => ['left', 'right'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_config'],
            'eval' => ['tl_class' => 'w33'],
            'sql' => ['type' => Types::STRING, 'length' => 5, 'default' => ''],
        ],
        'priceCalculateFactor' => [
            'default' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 16, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
        ],
        'orderPrefix' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'orderDigits' => [
            'default' => 4,
            'inputType' => 'select',
            'options' => range(1, 9),
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'default' => 4],
        ],
    ],
];
