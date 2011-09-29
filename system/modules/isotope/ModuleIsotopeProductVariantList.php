<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
			$objTemplate = new BackendTemplate('be_wildcard');
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
	 *
	 * @param	array|null
	 * @return	array
	 */
	protected function findProducts($arrCacheIds=null)
	{
		$arrIds = $this->findCategoryProducts($this->iso_category_scope, $this->iso_list_where);

		if (is_array($arrCacheIds))
		{
			$arrIds = array_intersect($arrIds, $arrCacheIds);
		}

		list($arrFilters, $arrSorting, $strWhere, $arrValues) = $this->getFiltersAndSorting();

		$objProductData = $this->Database->prepare(IsotopeProduct::getSelectStatement() . " WHERE p1.published='1' AND p1.language='' AND (p1.pid IN (" . implode(',', $arrIds) . "))$strWhere ORDER BY sorting")
										 ->execute($arrValues);

		return IsotopeFrontend::getProducts($objProductData, $this->iso_reader_jumpTo, true, $arrFilters, $arrSorting);
	}
}

