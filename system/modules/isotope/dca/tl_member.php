<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
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
    'href'                => 'table='.\Isotope\Model\Address::getTable(),
    'icon'                => 'system/modules/isotope/assets/images/cards-address.png',
);


/**
 * Force the "country" field to be mandatory
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['country']['eval']['mandatory'] = true;
