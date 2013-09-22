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
 * Table tl_iso_product_collection_item
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_item'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        'closed'                    => true,
        'notEditable'               => true,
        'ptable'                    => 'tl_iso_product_collection',
        'ctable'                    => array('tl_iso_product_collection_download'),
        'sql'                       => array
        (
            'keys' => array
            (
                'id'                => 'primary',
                'pid'               => 'index',
            )
        ),
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'foreignKey'            => 'tl_iso_product_collection.document_number',
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'tstamp' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'product_id' => array
        (
            'foreignKey'            => 'tl_iso_products.name',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'type' => array
        (
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'sku' => array
        (
            'sql'                   => "varchar(128) NOT NULL default ''",
        ),
        'name' => array
        (
            'sql'                   => "varchar(255) NOT NULL default ''",
        ),
        'options' => array
        (
           'sql'                    => "blob NULL",
        ),
        'quantity' => array
        (
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
        ),
        'price' => array
        (
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_free_price' => array
        (
            'sql'                   => "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_id' => array
        (
            // Not the ID of a tax class or rate, this is the CSV list of applicable taxes (incremental numeric)
            'sql'                   => "varchar(32) NOT NULL default ''",
        ),
        'jumpTo' => array
        (
            'foreignKey'            => 'tl_page.title',
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),

    )
);
