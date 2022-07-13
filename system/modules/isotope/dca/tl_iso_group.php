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
 * Table tl_iso_group
 */
$GLOBALS['TL_DCA']['tl_iso_group'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'label'                     => &$GLOBALS['TL_LANG']['tl_iso_group']['label'],
        'backlink'                  => \Contao\Input::get('popup') ? null : 'do=iso_products',
        'enableVersioning'          => true,
        'onload_callback' => array
        (
            array('Isotope\Backend\Group\Callback', 'checkPermission'),
        ),
        'ondelete_callback' => array
        (
            array('Isotope\Backend\Group\Callback', 'deleteGroup'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 5,
            'fields'                => array('sorting'),
            'flag'                  => 1,
            'icon'                  => 'system/modules/isotope/assets/images/folders.png',
        ),
        'label' => array
        (
            'fields'                => array('name'),
            'format'                => '%s',
            'label_callback'        => array('Isotope\Backend\Group\Callback', 'addIcon')
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
                'href'              => 'table=tl_iso_group&amp;act=edit',
                'icon'              => 'edit.gif',
                'button_callback'   => array('Isotope\Backend\Group\Callback', 'editButton'),
            ),
            'copy' => array
            (
                'href'              => 'table=tl_iso_group&amp;act=paste&amp;mode=copy',
                'icon'              => 'copy.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset()"',
                'button_callback'   => array('Isotope\Backend\Group\Callback', 'copyButton'),
            ),
            'copyChilds' => array
            (
                'href'              => 'table=tl_iso_group&amp;act=paste&amp;mode=copy&amp;childs=1',
                'icon'              => 'copychilds.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset()"',
                'button_callback'   => array('Isotope\Backend\Group\Callback', 'copyButton'),
            ),
            'cut' => array
            (
                'href'              => 'table=tl_iso_group&amp;act=paste&amp;mode=cut',
                'icon'              => 'cut.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'href'              => 'table=tl_iso_group&amp;act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Group\Callback', 'deleteButton'),
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
        'default'                   => '{group_legend},name,product_type;',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'foreignKey'            => 'tl_iso_group.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'sorting' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'"
        ),
        'name' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'product_type' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\ProductType::getTable().'.name',
            'options_callback'      => array('Isotope\Backend\ProductType\Callback', 'getOptions'),
            'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'eager'),
        )
    )
);
