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
 * Table tl_iso_requestcache
 */
$GLOBALS['TL_DCA']['tl_iso_requestcache'] = array
(

    'config' => array
    (
        'sql' => array
        (
            'keys' => array
            (
                'id'            => 'primary',
                'id,store_id'   => 'index'
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
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'store_id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'config' => array
        (
            'sql'                 =>  "blob NULL",
        ),
    ),
);