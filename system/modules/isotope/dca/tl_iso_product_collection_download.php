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
 */


/**
 * Table tl_iso_product_collection_download
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_download'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'closed'            => true,
        'notEditable'       => true,
        'ptable'            => 'tl_iso_product_collection_item',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'foreignKey'    => 'tl_iso_product_collection_item.name',
            'relation'      => array('type'=>'belongsTo', 'load'=>'lazy'),
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'download_id' => array
        (
            'foreignKey'    => 'tl_iso_downloads.type',
            'relation'      => array('type'=>'hasOne', 'load'=>'lazy'),
        ),

        'download_id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0',",
        ),
        'downloads_remaining' => array
        (
            'sql'                 =>  "varchar(255) NOT NULL default ''",
        ),
        'expires' => array
        (
            'sql'                 =>  "varchar(10) NOT NULL default ''",
        ),
    )
);
