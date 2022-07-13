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
 * Table tl_iso_download
 */
$GLOBALS['TL_DCA']['tl_iso_download'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'ptable'                    => 'tl_iso_product',
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
            'flag'                  => 1,
            'panelLayout'           => 'filter;search,limit',
            'headerFields'          => array(),
            'header_callback'       => array('Isotope\Backend\Download\Callback', 'headerFields'),
            'child_record_callback' => array('Isotope\Backend\Download\Callback', 'listRows'),
            'disableGrouping'       => true,
        ),
        'label' => array
        (
            'fields'                => array('title', 'singleSRC'),
            'format'                => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'href'              => 'act=paste&amp;mode=copy',
                'icon'              => 'copy.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'cut' => array
            (
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\Backend\Download\Callback', 'deleteButton'),
            ),
            'toggle' => array
            (
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback'   => array('Isotope\Backend\Download\Callback', 'toggleIcon')
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
        'default'                   => '{file_legend},singleSRC;{limit_legend},downloads_allowed,expires;{publish_legend},published',
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
        'sorting' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'singleSRC' => array
        (
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'eval'                  => array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['allowedDownload']),
            'sql'                   => "binary(16) NULL",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_files', 'field'=>'uuid'),
        ),
        'downloads_allowed' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>5, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(5) unsigned NOT NULL default '0'",
        ),
        'expires' => array
        (
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'options'               => array('minutes', 'hours', 'days', 'weeks', 'months', 'years'),
            'reference'             => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'published' => array
        (
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
    )
);
