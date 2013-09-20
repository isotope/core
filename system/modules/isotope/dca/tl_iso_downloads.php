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
 * Table tl_iso_downloads
 */
$GLOBALS['TL_DCA']['tl_iso_downloads'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'ptable'                    => 'tl_iso_products',
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
            'headerFields'          => array('name', 'alias', 'sku'),
            'child_record_callback' => array('Isotope\tl_iso_downloads', 'listRows'),
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_downloads']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_downloads']['copy'],
                'href'              => 'act=paste&amp;mode=copy',
                'icon'              => 'copy.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'cut' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_downloads']['cut'],
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_downloads']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback'   => array('Isotope\tl_iso_downloads', 'deleteButton'),
            ),
            'toggle' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_downloads']['toggle'],
                'icon'              => 'visible.gif',
                'attributes'        => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback'   => array('Isotope\tl_iso_downloads', 'toggleIcon')
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_downloads']['show'],
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
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_downloads']['singleSRC'],
            'exclude'               => true,
            'inputType'             => 'fileTree',
            'foreignKey'            => 'tl_files.path',
            'eval'                  => array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['allowedDownload']),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'eager'),
        ),
        'downloads_allowed' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_downloads']['downloads_allowed'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>5, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "int(5) unsigned NOT NULL default '0'",
        ),
        'expires' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_downloads']['expires'],
            'exclude'               => true,
            'inputType'             => 'timePeriod',
            'options'               => array('minutes', 'hours', 'days', 'weeks', 'months', 'years'),
            'reference'             => &$GLOBALS['TL_LANG']['MSC']['timePeriod'],
            'eval'                  => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'published' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_downloads']['published'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('doNotCopy'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        ),
    )
);
