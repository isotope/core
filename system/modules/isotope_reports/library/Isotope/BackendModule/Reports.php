<?php
/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\BackendModule;

use Isotope\Isotope;


class Reports extends \BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_iso_reports';


	public function generate()
	{
	    $this->import('BackendUser', 'User');

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
		$arrAllowedProducts = \Isotope\Backend::getAllowedProductIds();

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
												" . ($arrAllowedProducts === true ? '' : (" AND i.product_id IN (" . (empty($arrAllowedProducts) ? '0' : implode(',', $arrAllowedProducts)) . ")")) . "
												GROUP BY config_id")
									->execute(strtotime('-24 hours'));

		while ($objOrders->next())
		{
			$arrSummary[] = array
			(
				'name'			=> $objOrders->config_name,
				'currency'		=> $objOrders->currency,
				'total_orders'	=> $objOrders->total_orders,
				'total_sales'	=> Isotope::formatPrice($objOrders->total_sales),
				'total_items'	=> $objOrders->total_items,
			);
		}

		return $arrSummary;
	}
}

