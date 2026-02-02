<?php

use Isotope\CompatibilityHelper;

/*
 * Add a child table to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['config']['ctable'][] = 'tl_iso_address';

/*
 * Add a global operation to tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['list']['operations']['address_book'] = [
    'href' => 'table=tl_iso_address',
    'icon' => 'images/cards-address.png',
];

/*
 * Force the "country" field to be mandatory
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['country']['eval']['mandatory'] = true;
