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
 * Table tl_iso_product_collection_surcharge
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_surcharge'] = array
(
    'config' => array
    (
        'sql' => array
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
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'type' => array
        (
            'sql'                 =>  "varchar(32) NOT NULL default ''",
        ),
        'source_id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'label' => array
        (
            'sql'                 =>  "varchar(255) NOT NULL default ''",
        ),
        'price' => array
        (
            'sql'                 =>  "varchar(32) NOT NULL default ''",
        ),
        'total_price' => array
        (
            'sql'                 =>  "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_free_total_price' => array
        (
            'sql'                 =>  "decimal(12,2) NOT NULL default '0.00'",
        ),
        'tax_class' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'tax_id' => array
        (
            'sql'                 =>  "varchar(32) NOT NULL default ''",
        ),
        'before_tax' => array
        (
            'sql'                 =>  "char(1) NOT NULL default ''",
        ),
        'addToTotal' => array
        (
            'sql'                 =>  "char(1) NOT NULL default ''",
        ),
        'applyRoundingIncrement' => array
        (
            'sql'                 => "char(1) NOT NULL default '1'",
        ),
        'products' => array
        (
            'sql'                 =>  "blob NULL",
        ),
    ),
);