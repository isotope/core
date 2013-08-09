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
 * Table tl_iso_productcache
 */
$GLOBALS['TL_DCA']['tl_iso_productcache'] = array
(

    // Fields
    'fields' => array
    (

        'id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL auto_increment",
        ),
        'page_id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'module_id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'requestcache_id' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'keywords' => array
        (
            'sql'                 =>  "varchar(255) NOT NULL default ''",
        ),
        'groups' => array
        (
            'sql'                 =>  "blob NULL",
        ),
        'products' => array
        (
            'sql'                 =>  "blob NULL",
        ),
        'expires' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),

    ),
);