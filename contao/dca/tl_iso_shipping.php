<?php

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

/*
 * Table tl_iso_shipping
 */
$GLOBALS['TL_DCA']['tl_iso_shipping'] = [
    // Config
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
                'button_callback' => [Callback::class, 'copyShippingModule'],
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if (!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [Callback::class, 'deleteShippingModule'],
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
        '__selector__' => ['type', 'flatCalculation', 'protected'],
        'default' => '{title_legend},name,label,type',
        'flat' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'flatperWeight' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation,flatWeight;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'product_price' => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class,productCalculation;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'group' => '{title_legend},name,label,type,inherit;{note_legend:hide},note;{config_legend},group_methods;{price_legend},group_calculation,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
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
            'default' => 'flat',
            'options_callback' => static fn () => Shipping::getModelTypeOptions(),
            'reference' => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'],
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'inherit' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'note' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'decodeEntities' => true],
            'sql' => 'text NULL',
        ],
        'countries' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => static fn () => CompatibilityHelper::getCountries(),
            'eval' => ['multiple' => true, 'size' => 8, 'tl_class' => 'w50 w50h', 'chosen' => true],
            'sql' => 'blob NULL',
        ],
        'subdivisions' => [
            'exclude' => true,
            'sorting' => true,
            'inputType' => 'conditionalselect',
            'options_callback' => [Backend::class, 'getSubdivisions'],
            'eval' => ['multiple' => true, 'size' => 8, 'conditionField' => 'countries', 'tl_class' => 'w50 w50h'],
            'sql' => 'longblob NULL',
        ],
        'postalCodes' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:40px', 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'minimum_total' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 13, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'maximum_total' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 13, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "decimal(12,2) NOT NULL default '0.00'",
        ],
        'minimum_weight' => [
            'exclude' => true,
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'maximum_weight' => [
            'exclude' => true,
            'default' => ['unit' => 'kg'],
            'inputType' => 'timePeriod',
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'quantity_mode' => [
            'exclude' => true,
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
        'product_types' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => ProductType::getTable().'.name',
            'eval' => ['multiple' => true, 'size' => 8, 'chosen' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'product_types_condition' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['onlyAvailable', 'allAvailable', 'oneAvailable', 'calculation'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
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
        'address_type' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['custom', 'billing'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['address_type'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default ''",
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
        'flatCalculation' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['perProduct', 'perItem', 'perWeight'],
            'reference' => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_iso_shipping']['flat'], 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'flatWeight' => [
            'exclude' => true,
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'shipping_weight' => [
            'exclude' => true,
            'inputType' => 'timePeriod',
            'default' => ['unit' => 'kg'],
            'options' => ['mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'],
            'reference' => &$GLOBALS['TL_LANG']['WGT'],
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'helpwizard' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'productCalculation' => [
            'exclude' => true,
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
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => static function ($dc) {
                $objShipping = Shipping::findBy([$dc->table.'.id!=?'], $dc->id);

                return null === $objShipping ? [] : $objShipping->fetchEach('name');
            },
            'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr w50 w50h'],
            'sql' => 'blob NULL',
        ],
        'group_calculation' => [
            'exclude' => true,
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
        'guests' => [
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
