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
 * @author     Jan Reuteler <jan.reuteler@terminal42.ch>
 */



/**
 * Table tl_iso_requestcache
 */
$GLOBALS['TL_DCA']['tl_iso_requestcache'] = array
(

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
        'filters' => array
        (
            'sql'                 =>  "blob NULL",
        ),
        'sorting' => array
        (
            'sql'                 =>  "blob NULL",
        ),
        'limits' => array
        (
            'sql'                 =>  "blob NULL",
        ),
    ),
);