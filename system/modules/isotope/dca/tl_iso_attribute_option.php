<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_attribute_option
 */
$GLOBALS['TL_DCA']['tl_iso_attribute_option'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Multilingual',
        'enableVersioning'          => true,
        'ptable'                    => 'tl_iso_attribute',
        'dynamicPtable'             => true,
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'ptable,pid' => 'index'
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
            'panelLayout'           => 'filter,search,limit',
            'headerFields'          => array('name', 'field_name', 'type', 'customer_defined'),
            'child_record_callback' => function() {

            }
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
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['edit'],
                'href'              => 'act=edit',
                'icon'              => 'edit.gif'
            ),
            'copy' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['copy'],
                'href'              => 'act=paste&amp;mode=copy',
                'icon'              => 'copy.gif'
            ),
            'cut' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['cut'],
                'href'              => 'act=paste&amp;mode=cut',
                'icon'              => 'cut.gif'
            ),
            'delete' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['delete'],
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_iso_attribute_option']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['show'],
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                   => '{label_legend},type,label;{publish_legend},published',
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
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'ptable' => array
        (
            'sql'                   =>  "varchar(64) NOT NULL default ''",
        ),
        'langPid' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'language' => array
        (
            'sql'                   =>  "varchar(5) NOT NULL default ''",
        ),
        'type' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['type'],
            'exclude'               => true,
            'default'               => 'option',
            'inputType'             => 'select',
            'options'               => array('blank', 'option', 'group'),
            'eval'                  => array('tl_class'=>'w50', 'doNotCopy'=>true),
            'sql'                   => "varchar(8) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['label'],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'published' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_iso_attribute_option']['published'],
            'exclude'               => true,
            'inputType'             => 'checkbox',
            'eval'                  => array('tl_class'=>'w50', 'doNotCopy'=>true),
            'sql'                   => "char(1) NOT NULL default ''",
        )
    ),
);
