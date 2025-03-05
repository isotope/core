<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Isotope\Backend;
use Isotope\Backend\Shipping\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\Config;
use Isotope\Model\ProductType;
use Isotope\Model\Shipping;
use Isotope\Model\Shipping\Group;
use Isotope\Model\Shipping\ProductPrice;
use Isotope\Model\TaxClass;

/*
 * Load tl_iso_product data container and language files
 */
$this->loadDataContainer('tl_iso_product');
System::loadLanguageFile('tl_iso_product');

$GLOBALS['TL_DCA']['tl_iso_shipping'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=iso_setup',
        'onload_callback' => [
            [Callback::class, 'checkPermission'],
            [Callback::class, 'hideLabelAndNotes'],
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
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'type'],
            'format' => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ],
        'operations' => [
            'edit',
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'button_callback' => [Callback::class, 'copyShippingModule'],
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [Callback::class, 'deleteShippingModule'],
            ],
            'toggle',
            'show',
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'flatCalculation', 'protected'],
        'default' => '{title_legend},name,label,type',
        'flat' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},protected;{enabled_legend},enabled',
        'flatperWeight' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation,flatWeight;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},protected;{enabled_legend},enabled',
        'product_price' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class,productCalculation;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},protected;{enabled_legend},enabled',
        'group' => '{title_legend},name,label,type,inherit;{note_legend:hide},note;{config_legend},group_methods;{price_legend},group_calculation,tax_class;{expert_legend:hide},protected;{enabled_legend},enabled',
    ],
    'subpalettes' => [
        'protected' => 'groups',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'default' => 'flat',
            'options_callback' => static fn () => Shipping::getModelTypeOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'inherit' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'note' => [
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'decodeEntities' => true],
            'sql' => 'text NULL',
        ],
        'countries' => [
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'subdivisions' => [
            'sorting' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['multiple' => true, 'size' => 8, 'conditionField' => 'countries', 'tl_class' => 'w50 w50h'],
            'sql' => 'longblob NULL',
        ],
        'postalCodes' => [
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:40px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'minimum_total' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 13, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'maximum_total' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 13, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'minimum_weight' => [
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'maximum_weight' => [
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'quantity_mode' => [
            'inputType' => 'select',
            'options' => [
                Shipping::QUANTITY_MODE_ITEMS,
                Shipping::QUANTITY_MODE_PRODUCTS,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['quantity_mode'],
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'minimum_quantity' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'clr w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'maximum_quantity' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'product_types' => [
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'chosen' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'product_types_condition' => [
            'inputType' => 'select',
            'options' => ['onlyAvailable', 'allAvailable', 'oneAvailable', 'calculation'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'config_ids' => [
            'inputType' => 'select',
            'foreignKey' => Config::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'clr w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'address_type' => [
            'inputType' => 'select',
            'options' => ['custom', 'billing'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['address_type'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
        ],
        'price' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'rgxp' => 'surcharge', 'nullIfEmpty' => true, 'tl_class' => 'w50'],
            'sql' => 'varchar(16) NULL',
        ],
        'tax_class' => [
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => TaxClass::getTable().'.name',
            'options_callback' => [TaxClass::class, 'getOptionsWithSplit'],
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class' => 'w50'],
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'flatCalculation' => [
            'inputType' => 'select',
            'options' => ['perProduct', 'perItem', 'perWeight'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['flat'], 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'flatWeight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_weight' => [
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'productCalculation' => [
            'inputType' => 'select',
            'options' => [
                ProductPrice::PRICE_HIGHEST_ITEM,
                ProductPrice::PRICE_LOWEST_ITEM,
                ProductPrice::PRICE_SUM_ITEMS,
                ProductPrice::PRICE_HIGHEST_PRODUCT,
                ProductPrice::PRICE_LOWEST_PRODUCT,
                ProductPrice::PRICE_SUM_PRODUCTS,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['productCalculationOptions'],
            'eval' => ['helpwizard' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'group_methods' => [
            'inputType' => 'checkboxWizard',
            'options_callback' => static function ($dc) {
                $objShipping = Shipping::findBy([$dc->table.'.id!=?'], $dc->id);

                return null === $objShipping ? [] : $objShipping->fetchEach('name');
            },
            'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => 'blob NULL',
        ],
        'group_calculation' => [
            'inputType' => 'select',
            'options' => [
                Group::CALCULATE_FIRST,
                Group::CALCULATE_LOWEST,
                Group::CALCULATE_HIGHEST,
                Group::CALCULATE_SUM,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'protected' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'debug' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'logging' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enabled' => [
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
