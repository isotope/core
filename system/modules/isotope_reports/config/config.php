<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Backend modules
 */
array_insert($GLOBALS['BE_MOD']['isotope'], 2, array
(
	'reports' => array
	(
		'callback'		=> 'ModuleIsotopeReports',
		'icon'			=> 'system/modules/isotope_reports/assets/icon.png',
		'stylesheet'	=> 'system/modules/isotope_reports/assets/reports.css',
		'modules'		=> array
		(
			'sales' => array
			(
				'sales_total' => array
				(
					'callback'	=> 'IsotopeReportSalesTotal',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['sales_total'],
					'icon'		=> 'system/modules/isotope_reports/assets/sales.png',
					'panels'    => array
					(
						array('getSelectStopPanel', 'getSelectStartPanel', 'getSelectPeriodPanel'),
						array('getSortingPanel', 'getFilterByConfigPanel', 'getStatusPanel')
					)
				),
				'sales_product' => array
				(
					'callback'	=> 'IsotopeReportSalesProduct',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['sales_product'],
					'icon'		=> 'system/modules/isotope_reports/assets/product.png',
					'panels'    => array
					(
						array('getSelectFromPanel', 'getSelectColumnsPanel', 'getSelectPeriodPanel', 'getSelectVariantsPanel'),
						array('getSortingPanel', 'getStatusPanel')
					)
				),
			),
			'member' => array
			(
				/*
				'members_total' => array
				(
					'callback'	=> 'IsotopeReportMembersTotal',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['members_total'],
					'icon'		=> 'system/modules/isotope_reports/assets/member.png',
				),
				'members_registration' => array
				(
					'callback'	=> 'IsotopeReportMembersRegistration',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['members_registration'],
					'icon'		=> 'system/modules/isotope_reports/assets/member.png',
				),
				'members_guests' => array
				(
					'callback'	=> 'IsotopeReportMembersGuests',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['members_guests'],
					'icon'		=> 'system/modules/isotope_reports/assets/member.png',
				),
				*/
			),
			'rules' => array
			(
				/*
				'rules_usage' => array
				(
					'callback'	=> 'IsotopeReportRulesUsage',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['rules_usage'],
					'icon'		=> 'system/modules/isotope_reports/assets/generic.png',
				),
				'rules_coupons' => array
				(
					'callback'	=> 'IsotopeReportRulesCoupons',
					'label'		=> &$GLOBALS['ISO_LANG']['REPORT']['rules_coupons'],
					'icon'		=> 'system/modules/isotope_reports/assets/generic.png',
				),
				*/
			),
			'custom' => array(),
		),
	),
));


/**
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_reports';

