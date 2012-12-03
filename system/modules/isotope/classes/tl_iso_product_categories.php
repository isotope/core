<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_iso_product_categories
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_product_categories extends \Backend
{

	/**
	 * List the products
	 * @param array
	 * @return string
	 */
	public function listRows($row)
	{
		$this->loadDataContainer('tl_iso_products');
		$this->loadLanguageFile('tl_iso_products');

		$objProduct = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id=?")->limit(1)->execute($row['pid']);

		$this->import('tl_iso_products');
		return $this->tl_iso_products->getRowLabel($objProduct->row());
	}


	/**
	 * Repair associations between products and categories.
	 * We only need tl_iso_products.pages to filter for categories in the backend.
	 * @param DataContainer
	 * @return void
	 */
	public function updateFilterData(\DataContainer $dc)
	{
		if (\Input::get('act') == '')
		{
			$arrCategories = $this->Database->execute("SELECT page_id FROM tl_iso_product_categories WHERE pid={$dc->id}");
			$this->Database->query("UPDATE tl_iso_products SET pages='" . serialize($arrCategories) . "' WHERE id={$dc->id}");
		}
	}


	/**
	 * Return the page view button
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param array
	 * @return string
	 */
	public function getPageViewButton($href, $label, $title, $class, $attributes, $table, $root)
	{
		$objPage = $this->getPageDetails(\Input::get('id'));

		if (is_object($objPage))
		{
			$href  = ($this->Environment->ssl ? 'https://' : 'http://') . ($objPage->dns == '' ? $this->Environment->host : $objPage->dns) . (TL_PATH == '' ? '' : TL_PATH) . '/';
			$href .= $this->generateFrontendUrl($objPage->row());

			return ' &#160; :: &#160; <a href="'.$href.'" target="_blank" class="header_preview" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
		}

		return '';
	}
}

