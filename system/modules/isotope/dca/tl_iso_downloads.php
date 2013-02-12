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
        'dataContainer'                    => 'Table',
        'enableVersioning'                => true,
        'ptable'                        => 'tl_iso_products',
        'onload_callback' => array
        (
            array('Isotope\tl_iso_downloads', 'prepareSRC'),
        ),
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                        => 4,
            'fields'                    => array('sorting'),
            'flag'                        => 1,
            'panelLayout'                => 'filter;search,limit',
            'headerFields'                => array('name', 'alias', 'sku'),
            'child_record_callback'        => array('Isotope\tl_iso_downloads', 'listRows'),
            'disableGrouping'            => true,
        ),
        'label' => array
        (
            'fields'                    => array('title', 'singleSRC'),
            'format'                    => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'                    => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                    => 'act=select',
                'class'                    => 'header_edit_all',
                'attributes'            => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads']['edit'],
                'href'                    => 'act=edit',
                'icon'                    => 'edit.gif'
            ),
            'copy' => array
            (
                'label'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads']['copy'],
                'href'                    => 'act=paste&amp;mode=copy',
                'icon'                    => 'copy.gif',
                'attributes'            => 'onclick="Backend.getScrollOffset();"'
            ),
            'cut' => array
            (
                'label'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads']['cut'],
                'href'                    => 'act=paste&amp;mode=cut',
                'icon'                    => 'cut.gif',
                'attributes'            => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'label'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads']['delete'],
                'href'                    => 'act=delete',
                'icon'                    => 'delete.gif',
                'attributes'            => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads']['show'],
                'href'                    => 'act=show',
                'icon'                    => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                    => array('type'),
        'default'                        => '{file_legend},type,',
        'file'                            => '{file_legend},type,singleSRC;{name_legend},title,description;{limit_legend},downloads_allowed,expires',
        'folder'                        => '{file_legend},type,singleSRC;{limit_legend},downloads_allowed,expires',
    ),

    // Fields
    'fields' => array
    (
        'pid' => array
        (
            'foreignKey'                => 'tl_iso_product.name',
            'relation'                  => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'type' => array
        (
            'label'                        => &$GLOBALS['TL_LANG']['tl_iso_downloads']['type'],
            'exclude'                    => true,
            'inputType'                    => 'select',
            'options'                    => array('file', 'folder'),
            'reference'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads'],
            'eval'                        => array('mandatory'=>true, 'submitOnChange'=>true),
        ),
        'singleSRC' => array
        (
            'label'                        => &$GLOBALS['TL_LANG']['tl_iso_downloads']['singleSRC'],
            'exclude'                    => true,
            'inputType'                    => 'fileTree',
            'eval'                        => array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['allowedDownload']),
        ),
        'title' => array
        (
            'label'                        => &$GLOBALS['TL_LANG']['tl_iso_downloads']['title'],
            'exclude'                    => true,
            'inputType'                    => 'text',
            'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
        ),
        'description' => array
        (
            'label'                        => &$GLOBALS['TL_LANG']['tl_iso_downloads']['description'],
            'exclude'                    => true,
            'inputType'                    => 'textarea',
            'eval'                        => array('rte'=>'tinyMCE'),
        ),
        'downloads_allowed' => array
        (
            'label'                        => &$GLOBALS['TL_LANG']['tl_iso_downloads']['downloads_allowed'],
            'exclude'                    => true,
            'inputType'                    => 'text',
            'eval'                        => array('mandatory'=>true, 'maxlength'=>5, 'rgxp'=>'digit', 'tl_class'=>'w50'),
        ),
        'expires' => array
        (
            'label'                        => &$GLOBALS['TL_LANG']['tl_iso_downloads']['expires'],
            'exclude'                    => true,
            'inputType'                    => 'timePeriod',
            'options'                    => array('minutes', 'hours', 'days', 'weeks', 'months', 'years'),
            'reference'                    => &$GLOBALS['TL_LANG']['tl_iso_downloads'],
            'eval'                        => array('rgxp'=>'digit', 'tl_class'=>'w50'),
        ),
    )
);
