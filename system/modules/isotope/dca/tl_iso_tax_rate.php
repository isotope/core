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
 * Table tl_iso_tax_rate
 */
$GLOBALS['TL_DCA']['tl_iso_tax_rate'] = array
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
            array('Isotope\Backend\TaxRate\Callback', 'checkPermission'),
            array('Isotope\Backend\TaxRate\Callback', 'addCurrencyRate'),
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
            'flag'                  => 1,
            'fields'                => array('name'),
            'panelLayout'           => 'filter;search,limit',
        ),
        'label' => array
        (
            'fields'                => array('name'),
            'format'                => '%s',
            'label_callback'        => array('Isotope\Backend\TaxRate\Callback', 'listRow'),
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new'],
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy'],
                'href'              => 'act=copy',
                'icon'              => 'copy.gif',
                'button_callback'   => array('Isotope\Backend\TaxRate\Callback', 'copyTaxRate'),
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\TaxRate\Callback', 'deleteTaxRate'),
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('protected'),
        'default'                   => '{name_legend},name,label;{rate_legend},rate;{location_legend},address,countries,subdivisions,postalCodes;{condition_legend},amount;{config_legend:hide},config,exemptOnValidVAT,stop,guests,protected',
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
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'foreignKey'            => \Isotope\Model\TaxClass::getTable().'.name',
            'relation'              => array('type'=>'belongsTo', 'load'=>'lazy')
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'name' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name'],
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'address' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'checkbox',
            'default'               => array('shipping'),
            'options'               => array('billing', 'shipping'),
            'reference'             => &$GLOBALS['TL_LANG']['tl_iso_tax_rate'],
            'eval'                  => array('mandatory'=>true, 'multiple'=>true),
            'sql'                   => "blob NULL",
        ),
        'countries' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['countries'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'options_callback'      => function() {
                return \System::getCountries();
            },
            'eval'                  => array('multiple'=>true, 'size'=>10, 'csv'=>',', 'tl_class'=>'w50 w50h', 'chosen'=>true),
            'sql'                   => "text NULL",
        ),
        'subdivisions' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['subdivisions'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'conditionalselect',
            'options_callback'      => array('\Isotope\Backend', 'getSubdivisions'),
            'eval'                  => array('conditionField'=>'countries', 'multiple'=>true, 'size'=>10, 'csv'=>',', 'tl_class'=>'w50 w50h'),
            'sql'                   => "text NULL",
        ),
        'postalCodes' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postalCodes'],
            'exclude'               => true,
            'inputType'             => 'textarea',
            'eval'                  => array('style'=>'height:40px', 'tl_class'=>'clr'),
            'sql'                   => "text NULL",
        ),
        'rate' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate'],
            'exclude'               => true,
            'inputType'             => 'inputUnit',
            'options'               => array('%'=>'%'),
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'price'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'amount' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('multiple'=>true, 'size'=>2, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'config' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'],
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\Config::getTable().'.name',
            'eval'                  => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy')
        ),
        'exemptOnValidVAT' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['exemptOnValidVAT'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'stop' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'guests' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['guests'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'clr'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'protected' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['protected'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'groups' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['groups'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'foreignKey'            => 'tl_member_group.name',
            'eval'                  => array('multiple'=>true),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
    )
);
