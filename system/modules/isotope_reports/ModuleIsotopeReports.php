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


class ModuleIsotopeReports extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_iso_reports';


	public function generate()
	{
	    $this->import('BackendUser', 'User');
	    $this->import('Isotope');

		if ($this->Input->get('report') != '')
		{
			$arrReport = $this->findReport($this->Input->get('report'));

			if ($arrReport !== false && $this->classFileExists($arrReport['callback']))
			{
				$objCallback = new $arrReport['callback']($arrReport);
				return $objCallback->generate();
			}
		}

		return parent::generate();
	}


	/**
	 * Generate a icon view of all available reports
	 */
	protected function compile()
	{
		$arrReports = array();
		$arrGroups = &$GLOBALS['BE_MOD']['isotope']['reports']['modules'];

		foreach ($arrGroups as $strGroup => $arrGroup)
		{
			$strLegend = $GLOBALS['ISO_LANG']['REPORT'][$strGroup] ? $GLOBALS['ISO_LANG']['REPORT'][$strGroup] : $strGroup;

			foreach ($arrGroup as $strName => $arrConfig)
			{
			    if (!$this->User->isAdmin && !in_array($strName, $this->User->iso_reports))
			    {
    			    continue;
			    }

				$arrReports[$strLegend][$strName] = array
				(
					'label'		=> ($arrConfig['label'][0] ? $arrConfig['label'][0] : $strName),
					'title'		=> specialchars($arrConfig['label'][1]),
					'icon'		=> $arrConfig['icon'],
					'href'		=> $this->addToUrl('report='.$strName),
				);
			}
		}

		$this->Template->reports = $arrReports;
		$this->Template->summary = $this->getDailySummary();
	}


	protected function findReport($strName)
	{
	    if (!$this->User->isAdmin && !in_array($strName, $this->User->iso_reports))
	    {
    	    return false;
	    }

		foreach ($GLOBALS['BE_MOD']['isotope']['reports']['modules'] as $strGroup => $arrReports)
		{
			if (isset($arrReports[$strName]))
			{
				$arrData = $arrReports[$strName];
				$arrData['name'] = $strName;
				$arrData['group'] = $strGroup;

				return $arrData;
			}
		}

		return false;
	}


	/**
	 * Generate a daily summary for the overview page
	 * @return array
	 */
	protected function getDailySummary()
	{
		$arrSummary = array();
		$arrAllowedProducts = IsotopeBackend::getAllowedProductIds();

		$objOrders = $this->Database->prepare("SELECT
													c.id AS config_id,
													c.name AS config_name,
													c.currency,
													COUNT(o.id) AS total_orders,
													SUM(i.tax_free_price * i.product_quantity) AS total_sales,
													SUM(i.product_quantity) AS total_items
												FROM tl_iso_orders o
												LEFT JOIN tl_iso_order_items i ON o.id=i.pid
												LEFT OUTER JOIN tl_iso_config c ON o.config_id=c.id
												WHERE o.date>?
												" . ($arrAllowedProducts === false ? '' : (" AND i.product_id IN (" . (empty($arrAllowedProducts) ? '0' : implode(',', $arrAllowedProducts)) . ")")) . "
												GROUP BY config_id")
									->execute(strtotime('-24 hours'));

		while ($objOrders->next())
		{
			$arrSummary[] = array
			(
				'name'			=> $objOrders->config_name,
				'currency'		=> $objOrders->currency,
				'total_orders'	=> $objOrders->total_orders,
				'total_sales'	=> $this->Isotope->formatPrice($objOrders->total_sales),
				'total_items'	=> $objOrders->total_items,
			);
		}

		return $arrSummary;
	}
}

