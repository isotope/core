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

namespace Isotope;


/**
 * Class ModuleIsotopeProductVariantList
 *
 * Front end module Isotope "product variant list".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class ModuleIsotopeProductVariantList extends ModuleIsotopeProductList
{

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT VARIANT LIST ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Fill the object's arrProducts array
	 * @param array|null
	 * @return array
	 */
	protected function findProducts($arrCacheIds=null)
	{
		$time = time();
		$arrCategories = $this->findCategories($this->iso_category_scope);

		list($arrFilters, $arrSorting, $strWhere, $arrValues) = $this->getFiltersAndSorting();

		$objProductData = $this->Database->prepare(IsotopeProduct::getSelectStatement() . "
													WHERE p1.language=''"
													. (BE_USER_LOGGED_IN === true ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)")
													. "AND (p1.id IN (SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrCategories) . "))
														OR p1.pid IN (SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrCategories) . ")))"
													. (is_array($arrCacheIds) ? ("AND (p1.id IN (" . implode(',', $arrCacheIds) . ") OR p1.pid IN (" . implode(',', $arrCacheIds) . "))") : '')
													. ($this->iso_list_where == '' ? '' : " AND {$this->iso_list_where}")
													. "$strWhere GROUP BY p1.id ORDER BY c.sorting")
										 ->execute($arrValues);

		return IsotopeFrontend::getProducts($objProductData, IsotopeFrontend::getReaderPageId(null, $this->iso_reader_jumpTo), true, $arrFilters, $arrSorting);
	}
}

