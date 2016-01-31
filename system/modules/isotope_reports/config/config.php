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
 * Backend modules
 */
array_insert($GLOBALS['BE_MOD']['isotope'], 2, array
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
                        array('getSelectStopPanel', 'getSelectStartPanel', 'getSelectPeriodPanel'),
                        array('getSortingPanel', 'getFilterByConfigPanel', 'getStatusPanel')
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
                        array('getSortingPanel', 'getStatusPanel')
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
                        array('getSelectStopPanel', 'getSelectStartPanel', 'getSelectPeriodPanel'),
                        array('getSortingPanel', 'getFilterByConfigPanel', 'getStatusPanel')
                    )
                ),
                'members_registration' => array
                (
                    'callback'          => 'Isotope\Report\MembersRegistration',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_registration'],
                    'icon'              => 'system/modules/isotope_reports/assets/members_registration.png',
                    'class'             => 'disabled',
                ),
                'members_guests' => array
                (
                    'callback'          => 'Isotope\Report\MembersGuests',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_guests'],
                    'icon'              => 'system/modules/isotope_reports/assets/members_guests.png',
                    'class'             => 'disabled',
                ),
            ),
            'rules' => array
            (
                'rules_usage' => array
                (
                    'callback'          => 'Isotope\Report\RulesUsage',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['rules_usage'],
                    'icon'              => 'system/modules/isotope_reports/assets/rules_usage.png',
                    'class'             => 'disabled',
                ),
                'rules_coupons' => array
                (
                    'callback'          => 'Isotope\Report\RulesCoupons',
                    'label'             => &$GLOBALS['TL_LANG']['ISO_REPORT']['rules_coupons'],
                    'icon'              => 'system/modules/isotope_reports/assets/rules_coupons.png',
                    'class'             => 'disabled',
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

