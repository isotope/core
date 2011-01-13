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
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope']['epay_relay'] = 'ModuleEpayRelay';


/**
 * Payment modules
 */
$GLOBALS['ISO_PAY']['epay_form']			= 'PaymentEPayForm';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage'][] = array('EpayRelay', 'overwriteBase');


/**
 * Intercept redirects and add ePay relay script
 */
function epay_relay($buffer) 
{
	if ($GLOBALS['EPAY_RELAY'] === true)
	{
		$arrHeaders = headers_list();
		
		foreach( $arrHeaders as $strHeader )
		{
			if (strpos($strHeader, 'Location: ') !== false)
			{
				header(str_replace('Location: ', 'Location: https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/', $strHeader) . '?HTTP_COOKIE='.$_SERVER['HTTP_COOKIE']);
				exit;
			}
		}
	}
	
	return $buffer;
}

ob_start("epay_relay");

