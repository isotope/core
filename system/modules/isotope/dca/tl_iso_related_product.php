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
 * Table tl_iso_related_product
 */
$GLOBALS['TL_DCA']['tl_iso_related_product'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'ptable'                    => 'tl_iso_product',
        'onload_callback'           => array
        (
            array('Isotope\Backend\RelatedProduct\Callback', 'initDCA')
        ),
        'onsubmit_callback' => array
        (
            array('Isotope\Backend', 'truncateProductCache'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index',
            )
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 4,
            'fields'                => array('category'),
            'flag'                  => 1,
            'panelLayout'           => 'filter,limit',
            'headerFields'          => array('type', 'name', 'alias', 'sku'),
            'disableGrouping'       => true,
            'child_record_callback' => array('Isotope\Backend\RelatedProduct\Callback', 'listRows')
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
        'default'                   => '{category_legend},category;{products_legend},products',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'foreignKey'            => 'tl_iso_product.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'category' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\RelatedCategory::getTable().'.name',
            'eval'                  => array('mandatory'=>true, 'includeBlankOption'=>true),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('table' => \Isotope\Model\RelatedCategory::getTable(), 'type'=>'hasOne', 'load'=>'lazy'),
        ),
        'products' => array
        (
            'exclude'               => true,
            'inputType'             => 'picker',
            'eval'                  => array('mandatory'=>true, 'multiple' => true, 'csv' => ',', 'tl_class'=>'clr', 'orderField' => 'productsOrder'),
            'sql'                   => "blob NULL",
            'relation'              => array('table' => \Isotope\Model\Product::getTable(), 'type' => 'hasMany', 'load' => 'lazy'),
        ),
        'productsOrder' => array
        (
            'sql'                   => "blob NULL",
            'eval'                  => array('doNotShow' => true),
        ),
    )
);
