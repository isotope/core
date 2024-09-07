<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

 use Contao\ArrayUtil;

/**
 * Backend modules
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['isotope'], 2, array
(
    'reports' => array
    (
        'callback'                      => 'Isotope\BackendModule\Reports',
        'icon'                          => 'system/modules/isotope_reports/assets/icon.png',
        'stylesheet'                    => \Haste\Util\Debug::uncompressedFile('system/modules/isotope_reports/assets/reports.min.css'),
        'modules' => array
        (
            'sales' => array
            (
                'sales_total' => array
                (
                    'callback'          => 'Isotope\Report\SalesTotal',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales_total'],
                    'icon'              => 'system/modules/isotope_reports/assets/sales_total.png',
                    'panels' => array
                    (
                        array('getSelectStopPanel', 'getSelectStartPanel', 'getSelectPeriodPanel', 'getVisitorsPanel'),
                        array('getSortingPanel', 'getFilterByConfigPanel', 'getStatusPanel', 'getDateFieldPanel')
                    )
                ),
                'sales_product' => array
                (
                    'callback'          => 'Isotope\Report\SalesProduct',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales_product'],
                    'icon'              => 'system/modules/isotope_reports/assets/sales_product.png',
                    'panels' => array
                    (
                        array('getSelectFromPanel', 'getSelectColumnsPanel', 'getSelectPeriodPanel', 'getSelectVariantsPanel'),
                        array('getSortingPanel', 'getStatusPanel', 'getDateFieldPanel')
                    )
                ),
            ),
            'member' => array
            (
                'members_total' => array
                (
                    'callback'          => 'Isotope\Report\MembersTotal',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_total'],
                    'icon'              => 'system/modules/isotope_reports/assets/members_total.png',
                    'panels' => array
                    (
                        array('getSelectStopPanel', 'getSelectStartPanel'),
                        array('getSortingPanel', 'getFilterByConfigPanel', 'getStatusPanel', 'getDateFieldPanel')
                    )
                ),
                'members_registration' => array
                (
                    'callback'          => 'Isotope\Report\MembersRegistration',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_registration'],
                    'icon'              => 'system/modules/isotope_reports/assets/members_registration.png',
                    'panels' => array
                    (
                        array('getSelectStopPanel', 'getSelectStartPanel', 'getSelectPeriodPanel'),
                    )
                ),
                'members_guests' => array
                (
                    'callback'          => 'Isotope\Report\MembersGuests',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_guests'],
                    'icon'              => 'system/modules/isotope_reports/assets/members_guests.png',
                    'panels' => array
                    (
                        array('getSelectStopPanel', 'getSelectStartPanel', 'getSelectPeriodPanel'),
                        array('getSortingPanel', 'getFilterByConfigPanel', 'getStatusPanel', 'getDateFieldPanel')
                    )
                ),
            ),
            'custom' => array(),
        ),
    ),
));


/**
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_reports';
