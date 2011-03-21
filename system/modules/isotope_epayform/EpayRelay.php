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


class EpayRelay extends Frontend
{

	/**
	 * Override <base> meta tag in fe_page template
	 */
	public function overwriteBase($objPage, $objLayout, &$objPageRegular)
	{
		if ($objPage->epay_relay)
		{
			$objPageRegular->Template->base = 'https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/' . $this->Environment->base;
		}
	}


	/**
	 * Rewrite URLs for pages with relay script enabled
	 */
	public function rewriteUrls($arrRow, $strParams, $strUrl)
	{
		global $objPage;
		
		if ($arrRow['epay_relay'])
		{
			return 'https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/' . $this->Environment->base . $strUrl . '?HTTP_COOKIE='.$_SERVER['HTTP_COOKIE'];
		}
		elseif ($objPage->epay_relay)
		{
			return $this->Environment->base . $strUrl;
		}
		
		return $strUrl;
	}
}

