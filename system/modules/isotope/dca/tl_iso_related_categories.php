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
 * Table tl_iso_related_categories
 */
$GLOBALS['TL_DCA']['tl_iso_related_categories'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'         => 'Table',
        'enableVersioning'      => true,
        'closed'                => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
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
            'mode'              => 1,
            'fields'            => array('name'),
            'flag'              => 1,
        ),
        'label' => array
        (
            'fields'            => array('name'),
            'format'            => '%s',
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'          => 'mod=&table=',
                'class'         => 'header_back',
                'attributes'    => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['new'],
                'href'          => 'act=create',
                'class'         => 'header_new',
                'attributes'    => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffset();"',
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif'
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif'
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'               => '{name_legend},name;{redirect_legend},jumpTo',
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
            'label'             => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['name'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'               => "varchar(255) NOT NULL default ''",
        ),
        'jumpTo' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_iso_related_categories']['jumpTo'],
            'exclude'           => true,
            'inputType'         => 'pageTree',
            'foreignKey'        => 'tl_page.title',
            'eval'              => array('fieldType'=>'radio'),
            'sql'               => "int(10) unsigned NOT NULL default '0'",
            'relation'          => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
    )
);
