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
 * Table tl_iso_document
 */
$GLOBALS['TL_DCA']['tl_iso_document'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'enableVersioning'          => true,
        'closed'                    => true,
        'onload_callback' => array
        (
            array('Isotope\Backend', 'initializeSetupModule')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                  => 1,
            'fields'                => array('name'),
            'flag'                  => 1,
            'panelLayout'           => 'filter;search,limit',
        ),
        'label' => array
        (
            'fields'                => array('name'),
            'format'                => '%s',
        ),
        'global_operations' => array
        (
            'back' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'              => 'mod=&table=',
                'class'             => 'header_back',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
            'new' => array
            (
                'label'             => &$GLOBALS['TL_LANG']['tl_iso_document']['new'],
                'href'              => 'act=create',
                'class'             => 'header_new',
                'attributes'        => 'onclick="Backend.getScrollOffset();"',
            ),
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
                'href'              => 'act=edit',
                'icon'              => 'edit.gif',
            ),
            'copy' => array
            (
                'href'              => 'act=copy',
                'icon'              => 'copy.gif'
            ),
            'delete' => array
            (
                'href'              => 'act=delete',
                'icon'              => 'delete.gif',
                'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array
            (
                'href'              => 'act=show',
                'icon'              => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'              => array('type'),
        'default'                   => '{type_legend},name,type',
        'standard'                  => '{type_legend},name,type;{config_legend},documentTitle,fileTitle;{template_legend},documentTpl,gallery,collectionTpl,orderCollectionBy',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL auto_increment"
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
        'type' => array
        (
            'exclude'               => true,
            'filter'                => true,
            'inputType'             => 'select',
            'default'               => 'standard',
            'options_callback'      => function() {
                return \Isotope\Model\Document::getModelTypeOptions();
            },
            'eval'                  => array('submitOnChange'=>true, 'tl_class'=>'w50', 'includeBlankOption'=>true),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'documentTitle' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'fileTitle' => array
        (
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => array('mandatory'=>true, 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                   => "varchar(255) NOT NULL default ''"
        ),
        'documentTpl'  => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'options_callback'      => function(\DataContainer $dc) {
                return \Isotope\Backend::getTemplates('iso_document_');
            },
            'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50', 'mandatory'=>true),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'collectionTpl'  => array
        (
            'exclude'               => true,
            'default'               => 'iso_collection_invoice',
            'inputType'             => 'select',
            'options_callback'      => function(\DataContainer $dc) {
                return \Isotope\Backend::getTemplates('iso_collection_');
            },
            'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50', 'mandatory'=>true),
            'sql'                   => "varchar(64) NOT NULL default ''",
        ),
        'orderCollectionBy' => array
        (
            'exclude'               => true,
            'default'               => 'asc_id',
            'inputType'             => 'select',
            'options'               => &$GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
            'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                   => "varchar(16) NOT NULL default ''",
        ),
        'gallery' => array
        (
            'exclude'               => true,
            'inputType'             => 'select',
            'foreignKey'            => \Isotope\Model\Gallery::getTable().'.name',
            'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
    ),
);
