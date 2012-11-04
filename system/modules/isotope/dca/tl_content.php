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
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_list_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_list_layout'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'		  => array('tl_content_isotope', 'getListTemplates'),
	'eval'					  => array('includeBlankOption'=>true),
);


/**
 * Class tl_content_isotope
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_content_isotope extends Backend
{

	/**
	 * Get the filters and return them as array
	 * @param DataContainer
	 * @return array
	 */
	public function getFilters(DataContainer $dc)
	{
		$objAttributeSet = $this->Database->prepare("SELECT iso_attribute_set FROM tl_content WHERE id=?")
										  ->limit(1)
										  ->execute($dc->id);

		if ($objAttributeSet->numRows < 1)
		{
			return '';
		}

		$intAttributeSet = $objAttributeSet->iso_attribute_set;

		$objFilters = $this->Database->prepare("SELECT id, name FROM tl_iso_attributes WHERE fe_filter=? AND pid=?")
									 ->execute(1, (int) $intAttributeSet);

		if ($objFilters->numRows < 1)
		{
			return array();
		}

		$arrFilters = $objFilters->fetchAllAssoc();

		foreach ($arrFilters as $filter)
		{
			$arrFilterList[$filter['id']] = $filter['name'];
		}

		return $arrFilterList;
	}


	/**
	 * Return all list templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getListTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		// Get the page ID
		$objArticle = $this->Database->prepare("SELECT pid FROM tl_article WHERE id=?")
									 ->limit(1)
									 ->execute($intPid);

		// Inherit the page settings
		$objPage = $this->getPageDetails($objArticle->pid);

		// Get the theme ID
		$objLayout = $this->Database->prepare("SELECT pid FROM tl_layout WHERE id=?")
									->limit(1)
									->execute($objPage->layout);

		// Return all templates
		return $this->getTemplateGroup('iso_list_', $objLayout->pid);
	}
}

