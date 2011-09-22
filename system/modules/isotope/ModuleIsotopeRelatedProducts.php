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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class ModuleIsotopeRelatedProducts extends ModuleIsotopeProductList
{

	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: RELATED PRODUCTS ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		if (!strlen($this->Input->get('product')))
			return '';

		$this->iso_related_categories = deserialize($this->iso_related_categories);

		if (!is_array($this->iso_related_categories) || !count($this->iso_related_categories))
		{
			return '';
		}

		return parent::generate();
	}


	protected function findProducts()
	{
		$strAlias = $this->Input->get('product');

		$arrIds = array(0);
		$arrJumpTo = array();

		$objCategories = $this->Database->prepare("SELECT *, (SELECT jumpTo FROM tl_iso_related_categories WHERE id=category) AS jumpTo FROM tl_iso_related_products WHERE pid IN (SELECT id FROM tl_iso_products WHERE " . (is_numeric($strAlias) ? 'id' : 'alias') . "=?" . ($this->iso_list_where != '' ? ' AND '.$this->iso_list_where : '') . ") AND category IN (" . implode(',', $this->iso_related_categories) . ") ORDER BY id=" . implode(' DESC, id=', $this->iso_related_categories) . " DESC")->execute($strAlias);

		while( $objCategories->next() )
		{
			$ids = deserialize($objCategories->products);

			if (is_array($ids) && count($ids))
			{
				$arrIds = array_unique(array_merge($arrIds, $ids));

				if ($objCategories->jumpTo)
				{
					$arrJumpTo = array_fill_keys($ids, $objCategories->jumpTo) + $arrJumpTo;
				}
			}
		}

		return IsotopeFrontend::getProducts($arrIds, $this->iso_reader_jumpTo);
	}
}

