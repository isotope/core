<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_label
 */
$GLOBALS['TL_DCA']['tl_iso_label'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'closed'                      => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'        => 'primary',
                'language'  => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                      => 1,
            'fields'                    => array('language', 'label'),
            'flag'                      => 1,
            'panelLayout'               => 'filter,search,limit',
        ),
        'label' => array
        (
            'fields'                    => array('language', 'label', 'replacement'),
            'showColumns'               => true,
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'                  => 'mod=&table=',
                'class'                 => 'header_back',
                'attributes'            => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_label']['new'],
                'href'                  => 'act=create',
                'class'                 => 'header_new',
                'attributes'            => 'onclick="Backend.getScrollOffset();"',
            ),
            'all' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                  => 'act=select',
                'class'                 => 'header_edit_all',
                'attributes'            => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_label']['edit'],
                'href'                  => 'act=edit',
                'icon'                  => 'edit.gif'
            ),
            'copy' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_label']['copy'],
                'href'                  => 'act=copy',
                'icon'                  => 'copy.gif'
            ),
            'delete' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_label']['delete'],
                'href'                  => 'act=delete',
                'icon'                  => 'delete.gif',
                'attributes'            => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_label']['show'],
                'href'                  => 'act=show',
                'icon'                  => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                       => '{label_legend},language,label,replacement'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                       => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                       => "int(10) unsigned NOT NULL default '0'"
        ),
        'language' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_iso_label']['language'],
            'filter'                    => true,
            'inputType'                 => 'select',
            'options_callback'          => function() {
                return \System::getLanguages();
            },
            'default'                   => \BackendUser::getInstance()->language,
            'eval'                      => array('mandatory'=>true, 'tl_class'=>'clr'),
            'sql'                       => "varchar(5) NOT NULL default ''"
        ),
        'label' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_iso_label']['label'],
            'search'                    => true,
            'inputType'                 => 'text',
            'eval'                      => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long'),
            'sql'                       => "varchar(255) NOT NULL default ''"
        ),
        'replacement' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_iso_label']['replacement'],
            'search'                    => true,
            'inputType'                 => 'text',
            'eval'                      => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long'),
            'sql'                       => "varchar(255) NOT NULL default ''"
        )
    )
);
