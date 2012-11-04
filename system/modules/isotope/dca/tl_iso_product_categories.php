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


/**
 * Table tl_iso_product_categories
 */
$GLOBALS['TL_DCA']['tl_iso_product_categories'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'TablePageId',
		'ptable'						=> 'tl_page',
		'closed'						=> true,
		'notEditable'					=> true,
		'onload_callback' => array
		(

			array('tl_iso_product_categories', 'updateFilterData'),
		),
		'oncut_callback' => array
		(
			array('IsotopeBackend', 'truncateProductCache'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('sorting'),
			'panelLayout'				=> 'limit',
			'headerFields'				=> array('title', 'type'),
			'child_record_callback'		=> array('tl_iso_product_categories', 'listRows')
		),
		'global_operations' => array
		(
			'view' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['fePreview'],
				'class'					=> 'header_preview',
				'button_callback'		=> array('tl_iso_product_categories', 'getPageViewButton'),
			),
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_product_categories']['cut'],
				'href'					=> 'act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		)
	),

	'fields' => array() // Fields array must not be empty or we get a foreach error
);


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
		if ($this->Input->get('act') == '')
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
		$objPage = $this->getPageDetails($this->Input->get('id'));

		if (is_object($objPage))
		{
			$href  = ($this->Environment->ssl ? 'https://' : 'http://') . ($objPage->dns == '' ? $this->Environment->host : $objPage->dns) . (TL_PATH == '' ? '' : TL_PATH) . '/';
			$href .= $this->generateFrontendUrl($objPage->row());

			return ' &#160; :: &#160; <a href="'.$href.'" target="_blank" class="header_preview" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
		}

		return '';
	}
}

