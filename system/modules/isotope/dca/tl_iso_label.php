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
 * Table tl_iso_label
 */
$GLOBALS['TL_DCA']['tl_iso_label'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'backlink'                    => 'do=iso_setup',
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
            'all' => array
            (
                'href'                  => 'act=select',
                'class'                 => 'header_edit_all',
                'attributes'            => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'                  => 'act=edit',
                'icon'                  => 'edit.svg'
            ),
            'copy' => array
            (
                'href'                  => 'act=copy',
                'icon'                  => 'copy.svg'
            ),
            'delete' => array
            (
                'href'                  => 'act=delete',
                'icon'                  => 'delete.svg',
                'attributes'            => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'href'                  => 'act=show',
                'icon'                  => 'show.svg'
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
            'filter'                    => true,
            'inputType'                 => 'select',
            'options_callback'          => function() {
                return \Contao\System::getContainer()->get('contao.intl.locales')->getLocales(null, true);
            },
            'default'                   => \Contao\BackendUser::getInstance()->language,
            'eval'                      => array('mandatory'=>true, 'tl_class'=>'clr'),
            'sql'                       => "varchar(5) NOT NULL default ''"
        ),
        'label' => array
        (
            'search'                    => true,
            'inputType'                 => 'text',
            'eval'                      => array('mandatory'=>true, 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long'),
            'sql'                       => "varchar(255) NOT NULL default ''"
        ),
        'replacement' => array
        (
            'search'                    => true,
            'inputType'                 => 'text',
            'eval'                      => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'                       => "varchar(255) NOT NULL default ''"
        )
    )
);
