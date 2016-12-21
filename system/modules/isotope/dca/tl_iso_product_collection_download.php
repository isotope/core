<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_product_collection_download
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_download'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'         => 'Table',
        'closed'                => true,
        'notEditable'           => true,
        'ptable'                => 'tl_iso_product_collection_item',
        'sql'                   => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index',
            )
        ),
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'foreignKey'        => 'tl_iso_product_collection_item.name',
            'sql'               => "int(10) unsigned NOT NULL default '0'",
            'relation'          => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'",
        ),
        'download_id' => array
        (
            'foreignKey'        => \Isotope\Model\Download::getTable().'.id',
            'sql'               => "int(10) unsigned NOT NULL default '0'",
            'relation'          => array('type'=>'hasOne', 'load'=>'eager'),
        ),
        'downloads_remaining' => array
        (
            'sql'               => "varchar(10) NOT NULL default ''",
        ),
        'expires' => array
        (
            'sql'               => "varchar(10) NOT NULL default ''",
        ),
    )
);
