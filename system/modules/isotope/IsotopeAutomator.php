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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeAutomator
 *
 * Provide methods to run Isotope automated jobs.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class IsotopeAutomator extends Controller
{

	/**
	 * Remove carts that have not been accessed for a given number of days (depending on store config)
	 */
	public function deleteOldCarts()
	{
		$this->import('Database');

		$time = time() - $GLOBALS['TL_CONFIG']['iso_cartTimeout'];
		$objCarts = $this->Database->execute("SELECT id FROM tl_iso_cart WHERE tstamp<$time");

		if ($objCarts->numRows)
		{
			$arrIds = array();
			$objCart = new IsotopeCart();

			foreach ($objCarts->fetchEach('id') as $id)
			{
				if ($objCart->findBy('id', $id))
				{
					$objOrder = new IsotopeOrder();

					if ($objOrder->findBy('cart_id', $objCart->id))
					{
						if ($objOrder->status == 0)
						{
							$objOrder->delete();
						}
					}

					$objCart->delete();
					$arrIds[] = $id;
				}
			}

			if (!empty($arrIds))
			{
				$this->log('Purged ' . count($arrIds) . ' old guest carts', __METHOD__, TL_CRON);
			}
		}
	}


	/**
	 * Update the store configs with latest currency conversion data
	 * @return void
	 */
	public function convertCurrencies()
	{
		$this->import('Database');

		$objConfigs = $this->Database->execute("SELECT * FROM tl_iso_config WHERE currencyAutomator='1'");

		while ($objConfigs->next())
		{
			switch ($objConfigs->currencyProvider)
			{
				case 'ecb.int':
					$fltCourse = ($objConfigs->currency == 'EUR') ? 1 : 0;
					$fltCourseOrigin = ($objConfigs->currencyOrigin == 'EUR') ? 1 : 0;

					$objRequest = new Request();
					$objRequest->send('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

					if ($objRequest->hasError())
					{
						$this->log('Error retrieving data from European Central Bank (ecb.int): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);
						return;
					}

					$objXml = new SimpleXMLElement($objRequest->response);

					foreach ($objXml->Cube->Cube->Cube as $rate)
					{
						if (!$fltCourse && $currency['code'] == strtolower($objConfigs->currency))
						{
							$fltCourse = (float) $currency->kurs;
						}

						if (!$fltCourseOrigin && $currency['code'] == strtolower($objConfigs->currencyOrigin))
						{
							$fltCourseOrigin = (float) $currency->kurs;
						}
					}

					// Log if one of the currencies is not available
					if (!$fltCourse || !$fltCourseOrigin)
					{
						$this->log('Could not find currency to convert in European Central Bank (ecb.int).', __METHOD__, TL_ERROR);
						return;
					}

					$fltFactor = $fltCourse / $fltCourseOrigin;
					$this->Database->prepare("UPDATE tl_iso_config SET priceCalculateFactor=? WHERE id=?")->execute($fltFactor, $objConfigs->id);
					break;

				case 'admin.ch':
					$fltCourse = ($objConfigs->currency == 'CHF') ? 1 : 0;
					$fltCourseOrigin = ($objConfigs->currencyOrigin == 'CHF') ? 1 : 0;

					$objRequest = new Request();
					$objRequest->send('http://www.afd.admin.ch/publicdb/newdb/mwst_kurse/wechselkurse.php');

					if ($objRequest->hasError())
					{
						$this->log('Error retrieving data from Swiss Federal Department of Finance (admin.ch): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);
						return;
					}

					$objXml = new SimpleXMLElement($objRequest->response);

					foreach ($objXml->devise as $currency)
					{
						if (!$fltCourse && $currency['code'] == strtolower($objConfigs->currency))
						{
							$fltCourse = (float) $currency->kurs;
						}

						if (!$fltCourseOrigin && $currency['code'] == strtolower($objConfigs->currencyOrigin))
						{
							$fltCourseOrigin = (float) $currency->kurs;
						}
					}

					// Log if one of the currencies is not available
					if (!$fltCourse || !$fltCourseOrigin)
					{
						$this->log('Could not find currency to convert in Swiss Federal Department of Finance (admin.ch).', __METHOD__, TL_ERROR);
						return;
					}

					$fltFactor = $fltCourse / $fltCourseOrigin;
					$this->Database->prepare("UPDATE tl_iso_config SET priceCalculateFactor=? WHERE id=?")->execute($fltFactor, $objConfigs->id);
					break;

				default:
					// !HOOK: other currency providers
					// function myCurrencyConverter($strProvider, $strSourceCurrency, $strTargetCurrency, $arrConfig)
					if (isset($GLOBALS['ISO_HOOKS']['convertCurrency']) && is_array($GLOBALS['ISO_HOOKS']['convertCurrency']))
					{
						foreach ($GLOBALS['ISO_HOOKS']['convertCurrency'] as $callback)
						{
							$this->import($callback[0]);
							$this->$callback[0]->$callback[1]($objConfigs->currencyProvider, $objConfigs->currencyOrigin, $objConfigs->currency, $objConfigs-row());
						}
					}
			}
		}
	}
}

