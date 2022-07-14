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
 * Table tl_iso_product_price
 */
$GLOBALS['TL_DCA']['tl_iso_product_price'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'ptable'                    => 'tl_iso_product',
        'ctable'                    => array('tl_iso_product_pricetier'),
        'onload_callback' => array
        (
            array('Isotope\Backend\ProductPrice\Callback', 'initializeDCA'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'                                                => 'primary',
                'pid'                                               => 'index',
                'config_id,member_group,start,stop,pid'             => 'index',
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 4,
            'fields'                => array('id'),
            'flag'                  => 1,
            'panelLayout'           => 'filter;search,limit',
            'headerFields'          => array('id', 'name', 'alias', 'sku'),
            'disableGrouping'       => true,
            'child_record_callback' => array('Isotope\Backend\ProductPrice\Callback', 'listRows')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
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
                'icon'              => 'copy.svg'
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.svg',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.svg'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{price_legend},price_tiers,tax_class;{limit_legend},config_id,member_group,start,stop',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'foreignKey'            => 'tl_iso_product.name',
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'tstamp' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'price_tiers' => array
        (
            'exclude'               => true,
            'inputType'             => 'multiColumnWizard',
            'eval' => array
            (
                'doNotSaveEmpty'    => true,
                'tl_class'          => 'clr',
                'disableSorting'    => true,
                'columnFields' => array
                (
                    'min' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tier_columns']['min'],
                        'inputType' => 'text',
                        'eval'      => array('mandatory'=>true, 'rgxp'=>'digit'),
                    ),
                    'price' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tier_columns']['price'],
                        'inputType' => 'text',
                        'eval'      => array('mandatory'=>true, 'rgxp'=>'digit'),
                    ),
                ),
            ),
            'load_callback' => array
            (
                array('Isotope\Backend\ProductPrice\Callback', 'loadTiers'),
            ),
            'save_callback' => array
            (
                array('Isotope\Backend\ProductPrice\Callback', 'saveTiers'),
            ),
        ),
        'tax_class' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\TaxClass::getTable().'.name',
            'eval'                  => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['MSC']['taxFree'], 'tl_class'=>'clr'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'config_id' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\Config::getTable().'.name',
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'member_group' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Contao\MemberGroupModel::getTable().'.name',
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'start' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
        'stop' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                   => "varchar(10) NOT NULL default ''",
        ),
    )
);
