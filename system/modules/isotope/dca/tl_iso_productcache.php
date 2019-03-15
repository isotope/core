<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * @copyright  Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
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
                'id'                                                 => 'primary',
                'uniqid,requestcache_id,keywords,expires'            => 'index',
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
        'uniqid' => array
        (
            'sql'                   =>  "varchar(32) NOT NULL default ''",
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
        'requestcache_id' => array
        (
            'sql'                   =>  "int(10) unsigned NOT NULL default '0'",
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
    ),
);
