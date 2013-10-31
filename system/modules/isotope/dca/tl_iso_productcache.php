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
    'config' => array
    (
        'sql' => array
        (
            'keys' => array
            (
                'id'=> 'primary',
                'page_id,module_id,requestcache_id,keywords,expires' => 'index',
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
        'page_id' => array
        (
            'foreignKey'            => 'tl_page.title',
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'module_id' => array
        (
            'foreignKey'            => 'tl_module.name',
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'requestcache_id' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),
        'keywords' => array
        (
            'sql'                   =>  "varchar(255) NOT NULL default ''",
        ),
        'groups' => array
        (
            'foreignKey'            => 'tl_member_group.name',
            'sql'                   =>  "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'products' => array
        (
            'foreignKey'            => 'tl_iso_product.name',
            'eval'                  => array('csv'=>','),
            'sql'                   =>  "blob NULL",
            'relation'              => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'expires' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
        ),

    ),
);