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
 * Add a child table to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['config']['ctable'][] = \Isotope\Model\Address::getTable();
$GLOBALS['TL_DCA']['tl_member']['config']['ondelete_callback'][] = array('\Isotope\Backend\Member\Callback', 'deleteMemberCart');


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
