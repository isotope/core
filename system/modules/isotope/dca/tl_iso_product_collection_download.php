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
        'pid' => array
        (
            'foreignKey'    => 'tl_iso_product_collection_item.name',
            'relation'      => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'download_id' => array
        (
            'foreignKey'    => 'tl_iso_downloads.type',
            'relation'      => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
    )
);
