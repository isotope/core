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
 * Table tl_iso_baseprice
 */
$GLOBALS['TL_DCA']['tl_iso_baseprice'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'         => 'Table',
        'enableVersioning'      => true,
        'backlink'                  => 'do=iso_setup',
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
            'panelLayout'       => 'search,limit',
        ),
        'label' => array
        (
            'fields'            => array('name'),
            'format'            => '%s',
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'          => 'act=edit',
                'icon'          => 'edit.svg'
            ),
            'copy' => array
            (
                'href'          => 'act=copy',
                'icon'          => 'copy.svg'
            ),
            'delete' => array
            (
                'href'          => 'act=delete',
                'icon'          => 'delete.svg',
                'attributes'    => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'href'          => 'act=show',
                'icon'          => 'show.svg'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'               => '{name_legend},name,amount,label',
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
            'exclude'           => true,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'               => "varchar(255) NOT NULL default ''",
        ),
        'amount' => array
        (
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>true, 'rgxp'=>'digit', 'maxlength'=>32, 'tl_class'=>'w50'),
            'sql'               => "varchar(32) NOT NULL default ''",
        ),
        'label' => array
        (
            'exclude'           => true,
            'search'            => true,
            'default'           => '%s',
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
            'sql'               => "varchar(255) NOT NULL default ''",
        ),
    )
);
