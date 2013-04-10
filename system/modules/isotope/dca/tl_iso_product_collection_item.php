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
        'dataContainer'     => 'Table',
        'closed'            => true,
        'notEditable'       => true,
        'ptable'            => 'tl_iso_product_collection',
        'ctable'            => array('tl_iso_product_collection_download'),
    ),

    // Fields
    'fields' => array
    (
        'pid' => array
        (
            'foreignKey'    => 'tl_iso_product_collection.order_id',
            'relation'      => array('type'=>'belongsTo', 'load'=>'lazy'),
        ),
        'product_id' => array
        (
            'foreignKey'    => 'tl_iso_products.name',
            'relation'      => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
    )
);
