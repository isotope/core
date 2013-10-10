<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Table tl_iso_product_categories
 */
$GLOBALS['TL_DCA']['tl_iso_product_categories'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'TablePageId',
        'ptable'                    => 'tl_page',
        'closed'                    => true,
        'notEditable'               => true,
        'onload_callback' => array
        (

            array('Isotope\tl_iso_product_categories', 'updateFilterData'),
        ),
        'oncut_callback' => array
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
            'fields'                => array('sorting'),
            'panelLayout'           => 'limit',
            'headerFields'          => array('title', 'type'),
            'child_record_callback' => array('Isotope\tl_iso_product_categories', 'listRows')
        ),
        'global_operations' => array
        (
            'view' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['fePreview'],
                'class'             => 'header_preview',
                'button_callback'   => array('Isotope\tl_iso_product_categories', 'getPageViewButton'),
            ),
            'all' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'              => 'act=select',
                'class'             => 'header_edit_all',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations' => array
        (
            'cut' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_product_categories']['cut'],
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
        )
    ),

    'fields'                        => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment",
        ),

        'pid' => array
        (
            'foreignKey'            => 'tl_iso_products.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),

        'sorting' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),

        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),

        'page_id' => array
        (
            'foreignKey'            => 'tl_page.title',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
    )
);
