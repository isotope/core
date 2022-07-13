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
 * Table tl_iso_product_category
 */
$GLOBALS['TL_DCA']['tl_iso_product_category'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'TablePageId',
        'ptable'                    => 'tl_page',
        'closed'                    => true,
        'notEditable'               => true,
        'notCopyable'               => true,
        'notDeletable'              => true,
        'onload_callback' => array
        (
            function() {
                if (\Contao\Input::get('act') == '' && \Contao\BackendUser::getInstance()->hasAccess('modules', 'themes')) {
                    \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_category']['hint']);
                }
            }
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
                'page_id' => 'index',
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
            'child_record_callback' => array('Isotope\Backend\ProductCategory\Callback', 'listRows')
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
    ),

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
