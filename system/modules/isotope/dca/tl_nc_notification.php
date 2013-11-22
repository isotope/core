<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_nc_notification']['palettes']['iso_order_status_change'] = '{title_legend},title,type;{config_legend},iso_collectionTpl,iso_orderCollectionBy,iso_gallery,iso_document';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_collectionTpl'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_nc_notification']['iso_collectionTpl'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => function(\DataContainer $dc) {
        return \Isotope\Backend::getTemplates('iso_collection_');
    },
    'eval'                  => array('mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                   => "varchar(64) NOT NULL default ''",
);


$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_orderCollectionBy'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_nc_notification']['iso_orderCollectionBy'],
    'exclude'               => true,
    'default'               => 'asc_id',
    'inputType'             => 'select',
    'options'               => $GLOBALS['TL_LANG']['MSC']['iso_orderCollectionBy'],
    'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                   => "varchar(16) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_gallery'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_nc_notification']['iso_gallery'],
    'exclude'               => true,
    'inputType'             => 'select',
    'foreignKey'            => \Isotope\Model\Gallery::getTable().'.name',
    'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                   => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_nc_notification']['fields']['iso_document'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_nc_notification']['iso_document'],
    'exclude'               => true,
    'inputType'             => 'select',
    'foreignKey'            => 'tl_iso_document.name',
    'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                   => "int(10) unsigned NOT NULL default '0'",
);
