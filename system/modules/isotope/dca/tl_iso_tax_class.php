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
 * Table tl_iso_tax_class
 */
$GLOBALS['TL_DCA']['tl_iso_tax_class'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'            => 'Table',
        'enableVersioning'         => true,
        'backlink'                 => 'do=iso_setup',
        'onload_callback' => array
        (
            array('Isotope\Backend\TaxClass\Callback', 'checkPermission'),
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
            'panelLayout'           => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                => array('name', 'fallback'),
            'format'                => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>'
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
                'icon'              => 'edit.svg'
            ),
            'copy' => array
            (
                'href'              => 'act=copy',
                'icon'              => 'copy.svg',
                'button_callback'   => array('Isotope\Backend\TaxClass\Callback', 'copyTaxClass'),
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.svg',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\TaxClass\Callback', 'deleteTaxClass'),
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{name_legend},name,fallback;{rate_legend},includes,label,rates,applyRoundingIncrement,notNegative',
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
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'fallback' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('fallback'=>true, 'tl_class'=>'w50 m12'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'includes' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\TaxRate::getTable().'.name',
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'eager'),
        ),
        'label' => array
        (
            'exclude'               => true,
            'search'                => true,
            'inputType'             => 'text',
            'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'rates' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkboxWizard',
            'foreignKey'            => \Isotope\Model\TaxRate::getTable().'.name',
            'eval'                  => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                   => "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'applyRoundingIncrement' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
        'notNegative' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50'),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
    )
);
