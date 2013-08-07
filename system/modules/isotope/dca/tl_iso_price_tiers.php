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
 * Table tl_iso_price_tiers
 */
$GLOBALS['TL_DCA']['tl_iso_price_tiers'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'             => 'Table',
        //'ptable'                    => '',
    ),

    // List
    'list' => array
    (
    ),

    // Palettes
    'palettes' => array
    (
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
        'tstamp' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'min' => array
        (
            'sql'                 =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'price' => array
        (
            'sql'                 =>  "decimal(12,2) NOT NULL default '0.00'",
        ),

    )
);
