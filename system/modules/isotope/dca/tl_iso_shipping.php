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
 * Load tl_iso_product data container and language files
 */
$this->loadDataContainer('tl_iso_product');
\System::loadLanguageFile('tl_iso_product');


/**
 * Table tl_iso_shipping
 */
$GLOBALS['TL_DCA']['tl_iso_shipping'] = array
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
            array('Isotope\Backend\Shipping\Callback', 'checkPermission'),
            array('Isotope\Backend\Shipping\Callback', 'hideLabelAndNotes'),
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
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
                'button_callback'   => array('Isotope\Backend\Shipping\Callback', 'copyShippingModule'),
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Shipping\Callback', 'deleteShippingModule'),
            ),
            'toggle' => array
            (
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'   => array('Isotope\Backend\Shipping\Callback', 'toggleIcon')
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
        '__selector__'              => array('type', 'flatCalculation', 'protected'),
        'default'                   => '{title_legend},name,label,type',
        'flat'                      => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'flatperWeight'             => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation,flatWeight;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'product_price'             => '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},tax_class,productCalculation;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'group'                     => '{title_legend},name,label,type,inherit;{note_legend:hide},note;{config_legend},group_methods;{price_legend},group_calculation,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
        'dhl_business'              => '{title_legend},name,label,type;{note_legend:hide},note;{api_legend},dhl_user,dhl_signature,dhl_epk,dhl_product,dhl_app,dhl_token,dhl_shipping;{price_legend},price,tax_class,flatCalculation,shipping_weight;{config_legend},countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids,address_type;{expert_legend:hide},guests,protected;{enabled_legend},enabled,debug,logging',
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
            'default'               => 'flat',
            'options_callback'      => function() {
                return \Isotope\Model\Shipping::getModelTypeOptions();
            },
            'reference'             => &$GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'],
            'eval'                  => array('helpwizard'=>true, 'submitOnChange'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'inherit' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'note' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('rte'=>'tinyMCE', 'decodeEntities'=>true),
            'sql'                   => "text NULL",
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
        'subdivisions' => array
        (
            'exclude'               => true,
            'sorting'               => true,
            'inputType'             => 'conditionalselect',
            'options_callback'      => array('Isotope\Backend', 'getSubdivisions'),
            'eval'                  => array('multiple'=>true, 'size'=>8, 'conditionField'=>'countries', 'tl_class'=>'w50 w50h'),
            'sql'                   => "longblob NULL",
        ),
        'postalCodes' => array
        (
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:40px', 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'minimum_total' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>13, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'maximum_total' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>13, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'minimum_weight' => array
        (
            'exclude'               => true,
            'default'               => array('unit'=>'kg'),
            'inputType'             => 'timePeriod',
            'options'               => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'             => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'maximum_weight' => array
        (
            'exclude'               => true,
            'default'               => array('unit'=>'kg'),
            'inputType'             => 'timePeriod',
            'options'               => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'             => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'quantity_mode' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => [
                \Isotope\Model\Shipping::QUANTITY_MODE_ITEMS,
                \Isotope\Model\Shipping::QUANTITY_MODE_PRODUCTS,
            ],
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['quantity_mode'],
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
        'product_types' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\ProductType::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'size'=>8, 'chosen'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'product_types_condition' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('onlyAvailable', 'allAvailable', 'oneAvailable', 'calculation'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
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
        'address_type' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => ['custom', 'billing'],
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['address_type'],
            'eval'                  => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'                   => "varchar(8) NOT NULL default ''",
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
        'flatCalculation' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('perProduct', 'perItem', 'perWeight'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval'                  => array('submitOnChange'=>true, 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_iso_shipping']['flat'], 'tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'flatWeight' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'default'               => array('unit'=>'kg'),
            'options'               => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'             => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50', 'helpwizard'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'shipping_weight' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'default'               => array('unit'=>'kg'),
            'options'               => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'             => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50', 'helpwizard'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'productCalculation' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array(
                \Isotope\Model\Shipping\ProductPrice::PRICE_HIGHEST_ITEM,
                \Isotope\Model\Shipping\ProductPrice::PRICE_LOWEST_ITEM,
                \Isotope\Model\Shipping\ProductPrice::PRICE_SUM_ITEMS,
                \Isotope\Model\Shipping\ProductPrice::PRICE_HIGHEST_PRODUCT,
                \Isotope\Model\Shipping\ProductPrice::PRICE_LOWEST_PRODUCT,
                \Isotope\Model\Shipping\ProductPrice::PRICE_SUM_PRODUCTS,
            ),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['productCalculationOptions'],
            'eval'                  => array('helpwizard'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'group_methods' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkboxWizard',
            'options_callback'      => function($dc) {
                $objShipping = \Isotope\Model\Shipping::findBy(array($dc->table.'.id!=?'), $dc->id);
                return null === $objShipping ? array() : $objShipping->fetchEach('name');
            },
            'eval'                  => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "blob NULL",
        ),
        'group_calculation' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => [
                \Isotope\Model\Shipping\Group::CALCULATE_FIRST,
                \Isotope\Model\Shipping\Group::CALCULATE_LOWEST,
                \Isotope\Model\Shipping\Group::CALCULATE_HIGHEST,
                \Isotope\Model\Shipping\Group::CALCULATE_SUM,
            ],
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping'],
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'dhl_user' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>16, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NULL",
        ),
        'dhl_signature' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NULL",
        ),
        'dhl_epk' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NULL",
        ),
        'dhl_product' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => array('V01PAK', 'V53WPAK', 'V54EPAK', 'V06PAK', 'V06TG', 'V86PARCEL', 'V82PARCEL', 'V87PARCEL'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_shipping']['dhl_product'],
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(8) NULL",
        ),
        'dhl_app' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NULL",
        ),
        'dhl_token' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'decodeEntities'=>true, 'hideInput'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(32) NULL",
        ),
        'dhl_shipping' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'options'               => array('days', 'weeks', 'months', 'years'),
            'reference'             => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
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
            'sql'                   => "char(1) NOT NULL default ''"
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
