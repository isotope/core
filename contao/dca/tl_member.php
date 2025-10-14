<?php

use Isotope\Backend\Member\Callback;
use Isotope\CompatibilityHelper;
use Isotope\Model\Address;

/*
 * Add a child table to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['config']['ctable'][] = Address::getTable();

/*
 * Add a global operation to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['list']['operations']['address_book'] = [
    'href' => 'table='.Address::getTable(),
    'icon' => CompatibilityHelper::asset('images/cards-address.png'),
];

/*
 * Force the "country" field to be mandatory
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['country']['eval']['mandatory'] = true;
