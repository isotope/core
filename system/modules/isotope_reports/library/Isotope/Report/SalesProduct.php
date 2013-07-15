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

namespace Isotope\Report;

use \Isotope\Isotope;


class SalesProduct extends Sales
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
		$arrSession = \Session::getInstance()->get('iso_reports');
		$strPeriod = (string) $arrSession[$this->name]['period'];
		$intColumns = (int) $arrSession[$this->name]['columns'];
		$blnVariants = (bool) $arrSession[$this->name]['variants'];
		$intStatus = (int) $arrSession[$this->name]['iso_status'];

		if ($arrSession[$this->name]['from'] == '')
		{
			$intStart = strtotime('-' . ($intColumns-1) . ' ' . $strPeriod);
		}
		else
		{
			$intStart = (int) $arrSession[$this->name]['from'];
		}

		list($publicDate, $privateDate, $sqlDate) = $this->getPeriodConfiguration($strPeriod);

		$arrData = array('rows'=>array());
		$arrData['header'] = $this->getHeader($strPeriod, $publicDate, $intStart, $intColumns);

		$dateFrom = date($privateDate, $intStart);
		$dateTo = date($privateDate, strtotime('+ ' . ($intColumns-1) . ' ' . $strPeriod, $intStart));
		$groupVariants = $blnVariants ? 'p1.id' : 'IF(p1.pid=0, p1.id, p1.pid)';
		$arrAllowedProducts = \Isotope\Backend::getAllowedProductIds();

		$objProducts = $this->Database->query("
			SELECT
				IFNULL($groupVariants, i.product_id) AS product_id,
				IFNULL(p1.name, i.product_name) AS variant_name,
				IFNULL(p2.name, i.product_name) AS product_name,
				p1.sku AS product_sku,
				p2.sku AS variant_sku,
				i.product_options,
				SUM(i.product_quantity) AS quantity,
				t.attributes,
				t.variants,
				t.variant_attributes,
				SUM(i.tax_free_price * i.product_quantity) AS total,
				DATE_FORMAT(FROM_UNIXTIME(o.{$this->strDateField}), '$sqlDate') AS dateGroup
			FROM tl_iso_order_items i
			LEFT JOIN tl_iso_orders o ON i.pid=o.id
			LEFT JOIN tl_iso_orderstatus os ON os.id=o.status
			LEFT OUTER JOIN tl_iso_products p1 ON i.product_id=p1.id
			LEFT OUTER JOIN tl_iso_products p2 ON p1.pid=p2.id
			LEFT OUTER JOIN tl_iso_producttypes t ON p1.type=t.id
			WHERE 1
				" . ($intStatus > 0 ? " AND o.status=".$intStatus : '') . "
				" . ($arrAllowedProducts === true ? '' : (" AND p1.id IN (" . (empty($arrAllowedProducts) ? '0' : implode(',', $arrAllowedProducts)) . ")")) . "
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
				$arrOptions['name'] = sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $arrOptions['name'], ($objProducts->variants ? $objProducts->variant_sku : $objProducts->product_sku));
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

					$arrOptions[] = '<span class="variant">' . $strName . ': ' . $strValue . '</span>';
				}
			}

			$arrOptions['name'] = '<span class="product">' . $arrOptions['name'] . '</span>';

			$arrRaw[$objProducts->product_id]['name'] = implode('<br>', $arrOptions);
			$arrRaw[$objProducts->product_id][$objProducts->dateGroup] = (float) $arrRaw[$objProducts->product_id][$objProducts->dateGroup] + (float) $objProducts->total;
			$arrRaw[$objProducts->product_id][$objProducts->dateGroup.'_quantity'] = (int) $arrRaw[$objProducts->product_id][$objProducts->dateGroup.'_quantity'] + (int) $objProducts->quantity;
			$arrRaw[$objProducts->product_id]['total'] = (float) $arrRaw[$objProducts->product_id]['total'] + (float) $objProducts->total;
			$arrRaw[$objProducts->product_id]['quantity'] = (int) $arrRaw[$objProducts->product_id]['quantity'] + (int) $objProducts->quantity;
		}

		// Prepare columns
		$arrColumns = array();
		for ($i=0; $i<$intColumns; $i++)
		{
			$arrColumns[] = date($privateDate, $intStart);
			$intStart = strtotime('+1 ' . $strPeriod, $intStart);
		}

		$arrFooter = array();

		// Sort the data
		usort($arrRaw, array($this, ($arrSession[$this->name]['tl_sort'] == 'product_name' ? 'sortProductsByName' : 'sortProductsByTotal')));

		// Generate data
		foreach ($arrRaw as $arrProduct)
		{
			$arrRow = array(array
			(
				'value'      => $arrProduct['name'],
				'attributes' => ' style="white-space:nowrap"',
				'class'      => ($blnVariants ? '' : 'fix-height')
			));

			$arrFooter[0] = array
			(
				'value'      => $GLOBALS['ISO_LANG']['REPORT']['sums'],
				'class'      => 'fix-height'
			);

			foreach ($arrColumns as $i=>$column)
			{
				$arrRow[$i+1] = array
				(
					'value'			=> Isotope::formatPriceWithCurrency($arrProduct[$column]) . (($arrProduct[$column.'_quantity'] !== null) ? '<br><span class="variant">' . Isotope::formatItemsString($arrProduct[$column.'_quantity']) . '</span>' : ''),
					'attributes'	=> ' style="text-align:right;white-space:nowrap"',
				);

				$arrFooter[$i+1] = array
				(
					'total'         => $arrFooter[$i+1]['total'] + $arrProduct[$column],
					'quantity'      => $arrFooter[$i+1]['quantity'] + $arrProduct[$column.'_quantity'],
					'attributes'	=> ' style="text-align:right;white-space:nowrap"',
				);
			}

			$arrRow[$i+2] = array
			(
				'value'			=> Isotope::formatPriceWithCurrency($arrProduct['total']) . (($arrProduct['quantity'] !== null) ? '<br><span class="variant">' . Isotope::formatItemsString($arrProduct['quantity']) . '</span>' : ''),
				'attributes'	=> ' style="text-align:right;white-space:nowrap"',
			);

			$arrFooter[$i+2] = array
			(
				'total'         => $arrFooter[$i+2]['total'] + $arrProduct['total'],
				'quantity'      => $arrFooter[$i+2]['quantity'] + $arrProduct['quantity'],
				'attributes'	=> ' style="text-align:right;white-space:nowrap"',
			);

			$arrData['rows'][] = array
			(
				'columns' => $arrRow,
			);
		}

		for ($i=1; $i<count($arrFooter); $i++)
		{
			$arrFooter[$i]['value'] = Isotope::formatPriceWithCurrency($arrFooter[$i]['total']) . '<br><span class="variant">' . Isotope::formatItemsString($arrFooter[$i]['quantity']) . '</span>';
			unset($arrFooter[$i]['total'], $arrFooter[$i]['quantity']);
		}

		$arrData['footer'] = $arrFooter;
		$this->Template->data = $arrData;
	}


	protected function getSelectVariantsPanel()
	{
		$arrSession = \Session::getInstance()->get('iso_reports');

		return array
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
			'attributes'	=> ' onchange="this.form.submit()"'
		);
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
		$arrSession = \Session::getInstance()->get('iso_reports');

		if ($arrSession[$this->name]['tl_sort'] == '')
		{
			$arrSession[$this->name]['tl_sort'] = 'total';
		}

		\Session::getInstance()->set('iso_reports', $arrSession);

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

