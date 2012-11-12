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


class IsotopeReportSalesProduct extends IsotopeReportSales
{

	public function generate()
	{
		$this->initializeDefaultValues();

		$this->loadLanguageFile('tl_iso_products');
		$this->loadDataContainer('tl_iso_products');

		return parent::generate();
	}


	protected function compile()
	{
		$arrSession = $this->Session->get('iso_reports');
		$strPeriod = (string) $arrSession[$this->name]['period'];
		$intStart = (int) $arrSession[$this->name]['from'];
		$intColumns = (int) $arrSession[$this->name]['columns'];
		$blnVariants = (bool) $arrSession[$this->name]['variants'];

		list($publicDate, $privateDate, $sqlDate) = $this->getPeriodConfiguration($strPeriod);

		$arrData = array('rows'=>array());
		$arrData['header'] = $this->getHeader($strPeriod, $publicDate, $intStart, $intColumns);

		$dateFrom = date($privateDate, $intStart);
		$dateTo = date($privateDate, strtotime('+ ' . ($intColumns-1) . ' ' . $strPeriod, $intStart));
		$groupVariants = $blnVariants ? 'p1.id' : 'IF(p1.pid=0, p1.id, p1.pid)';

		$objProducts = $this->Database->query("
			SELECT
				IFNULL($groupVariants, i.product_id) AS product_id,
				IFNULL(p1.name, i.product_name) AS variant_name,
				IFNULL(p2.name, i.product_name) AS product_name,
				p1.sku AS product_sku,
				p2.sku AS variant_sku,
				i.product_options,
				t.attributes,
				t.variants,
				t.variant_attributes,
				SUM(i.price*i.product_quantity) AS total,
				DATE_FORMAT(FROM_UNIXTIME(o.date), '$sqlDate') AS dateGroup
			FROM tl_iso_order_items i
			LEFT JOIN tl_iso_orders o ON i.pid=o.id
			LEFT OUTER JOIN tl_iso_products p1 ON i.product_id=p1.id
			LEFT OUTER JOIN tl_iso_products p2 ON p1.pid=p2.id
			LEFT OUTER JOIN tl_iso_producttypes t ON p1.type=t.id
			GROUP BY dateGroup, product_id
			HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo");

		$arrRaw = array();

		// Prepare product data
		while ($objProducts->next())
		{
			$arrAttributes = deserialize($objProducts->attributes, true);
			$arrVariantAttributes = deserialize($objProducts->variant_attributes, true);
			$arrOptions = array('name'=>$objProducts->variant_name);

			// Use product title if name is not a variant attribute
			if ($objProducts->variants && !$arrVariantAttributes['name']['enabled'])
			{
				$arrOptions['name'] = $objProducts->product_name;
			}

			if ($arrAttributes['sku']['enabled'])
			{
				$arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], ($objProduct->variants ? $objProducts->variant_sku : $objProducts->product_sku));
			}

			if ($blnVariants && $objProducts->variants)
			{
				if ($arrVariantAttributes['sku']['enabled'])
				{
					$arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], $objProducts->product_sku);
				}

				foreach (deserialize($objProducts->product_options, true) as $strName => $strValue)
				{
					if (isset($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strName]))
					{
						$strValue = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strName]['options'][$strValue] ? $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strName]['options'][$strValue] : $strValue;
						$strName = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strName]['label'][0] ? $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strName]['label'][0] : $strName;
					}

					$arrOptions[] = $strName . ': ' . $strValue;
				}
			}

			$strName = '<p class="tl_help tl_tip">' . implode('<br>', $arrOptions) . '</p>';

			$arrRaw[$objProducts->product_id]['name'] = $strName;
			$arrRaw[$objProducts->product_id][$objProducts->dateGroup] = $objProducts->total;
			$arrRaw[$objProducts->product_id]['total'] = (float) $arrRaw[$objProducts->product_id]['total'] + (float) $objProducts->total;
		}

		// Prepare columns
		$arrColumns = array();
		for ($i=0; $i<$intColumns; $i++)
		{
			$arrColumns[] = date($privateDate, $intStart);
			$intStart = strtotime('+1 ' . $strPeriod, $intStart);
		}

		// Sort the data
		usort($arrRaw, array($this, ($arrSession[$this->name]['tl_sort'] == 'product_name' ? 'sortProductsByName' : 'sortProductsByTotal')));

		// Generate data
		foreach ($arrRaw as $arrProduct)
		{
			$arrRow = array(array('value'=>$arrProduct['name']));

			foreach ($arrColumns as $column)
			{
				$arrRow[] = array
				(
					'value'			=> $this->Isotope->formatPrice($arrProduct[$column]),
					'attributes'	=> ' style="text-align:right"',
				);
			}

			$arrRow[] = array
			(
				'value'			=> $this->Isotope->formatPrice($arrProduct['total']),
				'attributes'	=> ' style="text-align:right"',
			);

			$arrData['rows'][] = array
			(
				'columns' => $arrRow,
			);
		}

		$this->Template->data = $arrData;
	}


	protected function getPanels()
	{
		$arrSession = $this->Session->get('iso_reports');
		$arrPanels = parent::getPanels();

		$arrPanels[0][] = array
		(
			'name'			=> 'variants',
			'label'			=> 'Varianten:',
			'type'			=> 'radio',
			'value'			=> (string) $arrSession[$this->name]['variants'],
			'class'			=> 'tl_variants',
			'options'		=> array
			(
				'1'			=> &$GLOBALS['TL_LANG']['MSC']['yes'],
				''			=> &$GLOBALS['TL_LANG']['MSC']['no'],
			),
			'attributes'	=> ' onchange="this.form.submit()"',
		);

		return $arrPanels;
	}


	protected function initializeDefaultValues()
	{
		$this->arrSearchOptions = array
		(
			'product_name' => 'Produktname',
		);

		$this->arrSortingOptions = array
		(
			'product_name'	=> 'Produktname',
			'total'			=> 'Gesamtumsatz',
		);

		// Set default session data
		$arrSession = $this->Session->get('iso_reports');

		if ($arrSession[$this->name]['tl_sort'] == '')
		{
			$arrSession[$this->name]['tl_sort'] = 'total';
		}

		$this->Session->set('iso_reports', $arrSession);

		parent::initializeDefaultValues();
	}


	protected function getHeader($strPeriod, $strFormat, $intStart, $intColumns)
	{
		$arrHeader = array();
		$arrHeader[] = array('value'=>'Produkt');

		for ($i=0; $i<$intColumns; $i++)
		{
			$arrHeader[] = array
			(
				'value'			=> $this->parseDate($strFormat, $intStart),
				'attributes'	=> ' style="text-align:right"',
			);

			$intStart = strtotime('+ 1 ' . $strPeriod, $intStart);
		}

		$arrHeader[] = array
		(
			'value'			=> 'Total',
			'attributes'	=> ' style="text-align:right"',
		);

		return $arrHeader;
	}


	protected function sortProductsByName($a, $b)
	{
		return strcasecmp($a['name'], $b['name']);
	}


	protected function sortProductsByTotal($a, $b)
	{
		return ($a['total'] == $b['total'] ? 0 : ($a['total'] < $b['total'] ? 1 : -1));
	}
}

