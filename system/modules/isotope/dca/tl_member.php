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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Add a child table to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['config']['ctable'][] = \Isotope\Model\Address::getTable();


/**
 * Add a global operation to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['list']['operations']['address_book'] = array
(
    'label'               => &$GLOBALS['TL_LANG']['tl_member']['address_book'],
    'href'                => 'table='.\Isotope\Model\Address::getTable(),
    'icon'                => 'system/modules/isotope/assets/images/cards-address.png',
);


/**
 * Force the "country" field to be mandatory
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['country']['eval']['mandatory'] = true;
