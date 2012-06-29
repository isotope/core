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
 * Class ModuleIsotopeProductReader
 * Front end module Isotope "product reader".
 */
class ModuleIsotopeProductReader extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productreader';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT READER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Return if no product has been specified
		if ($this->Input->get('product') == '')
		{
			return '';
		}

		return parent::generate();
	}


	/**
	 * Generate AJAX scripts
	 * @return string
	 */
	public function generateAjax()
	{
		$objProduct = IsotopeFrontend::getProduct($this->Input->get('product'), IsotopeFrontend::getReaderPageId(), false);

		if ($objProduct)
		{
			return $objProduct->generateAjax($this);
		}

		return '';
	}


	/**
	 * Generate module
	 * @return void
	 */
	protected function compile()
	{
		$objProduct = IsotopeFrontend::getProductByAlias($this->Input->get('product'), IsotopeFrontend::getReaderPageId());

		if (!$objProduct)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['invalidProductInformation'];
			return;
		}

		$this->Template->product = $objProduct->generate((strlen($this->iso_reader_layout) ? $this->iso_reader_layout : $objProduct->reader_template), $this);
		$this->Template->product_id = ($objProduct->cssID[0] != '') ? ' id="' . $objProduct->cssID[0] . '"' : '';
		$this->Template->product_class = trim('product ' . $objProduct->cssID[1]);
		$this->Template->referer = 'javascript:history.go(-1)';
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

		global $objPage;

		$objPage->pageTitle = strip_insert_tags($objProduct->name);
		$objPage->description = $this->prepareMetaDescription($objProduct->description_meta);

		$GLOBALS['TL_KEYWORDS'] .= (strlen($GLOBALS['TL_KEYWORDS']) ? ', ' : '') . $objProduct->keywords_meta;
	}
}

