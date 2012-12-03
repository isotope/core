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

namespace Isotope\Module;

use \Module as Contao_Module;


/**
 * Class ModuleIsotope
 *
 * Parent class for Isotope modules.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class Module extends Contao_Module
{

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;

	/**
	 * Disable caching of the frontend page if this module is in use.
	 * Usefule to enable in a child classes.
	 * @var bool
	 */
	protected $blnDisableCache = false;


	/**
	 * Load libraries and scripts
	 * @param object
	 * @param string
	 * @return void
	 */
	public function __construct($objModule, $strColumn='main')
	{
		parent::__construct($objModule, $strColumn);

		if (TL_MODE == 'FE')
		{
			$this->import('Isotope\Isotope', 'Isotope');

			if (FE_USER_LOGGED_IN === true)
			{
				$this->import('FrontendUser', 'User');
			}

			// Load Isotope javascript and css
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/assets/isotope.min.js';
			$GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/isotope.min.css';

			// Disable caching for pages with certain modules (eg. Cart)
			if ($this->blnDisableCache)
			{
				global $objPage;
				$objPage->cache = 0;
			}
		}
	}


	/**
	 * Include messages if enabled
	 * @return string
	 */
	public function generate()
	{
		$strBuffer = parent::generate();

		// Prepend any messages to the module output
		if ($this->iso_includeMessages)
		{
			$strBuffer = \Isotope\Frontend::getIsotopeMessages() . $strBuffer;
		}

		return $strBuffer;
	}


	/**
	 * The ids of all pages we take care of. This is what should later be used eg. for filter data.
	 * @param string
	 * @return array
	 */
	protected function findCategories($strCategoryScope)
	{
		if ($this->defineRoot && $this->rootPage > 0)
		{
			$objPage = $this->getPageDetails($this->rootPage);
		}
		else
		{
			global $objPage;
		}

		switch ($strCategoryScope)
		{
			case 'global':
				$arrCategories = $this->getChildRecords($objPage->rootId, 'tl_page');
				$arrCategories[] = $objPage->rootId;
				break;

			case 'current_and_first_child':
				$arrCategories = $this->Database->execute("SELECT id FROM tl_page WHERE pid={$objPage->id}")->fetchEach('id');
				$arrCategories[] = $objPage->id;
				break;

			case 'current_and_all_children':
				$arrCategories = $this->getChildRecords($objPage->id, 'tl_page');
				$arrCategories[] = $objPage->id;
				break;

			case 'parent':
				$arrCategories = array($objPage->pid);
				break;

			case 'product':
				$objProduct = \Isotope\Frontend::getProductByAlias(\Input::get('product'));

				if ($objProduct !== null)
				{
					$arrCategories = $objProduct->categories;
				}
				else
				{
					return array(0);
				}
				break;

			case 'article':
				$arrCategories = array($GLOBALS['ISO_CONFIG']['current_article']['pid'] > 0 ? $GLOBALS['ISO_CONFIG']['current_article']['pid'] : $objPage->id);
				break;

			case 'current_category':
			default:
				$arrCategories = array($objPage->id);
				break;
		}

		return empty($arrCategories) ? array(0) : $arrCategories;
	}


	/**
	 * Generate the URL from existing $_GET parameters.
	 * Use \Input::setGet('var', null) to remove a parameter from the final URL.
	 * @return string
	 */
	protected function generateRequestUrl()
	{
		if (!strlen($this->Environment->request))
		{
			return '';
		}

		$strRequest = preg_replace('/\?.*$/i', '', $this->Environment->request);
		$strRequest = preg_replace('/' . preg_quote($GLOBALS['TL_CONFIG']['urlSuffix'], '/') . '$/i', '', $strRequest);
		$arrFragments = explode('/', $strRequest);

		// Skip index.php
		if (strtolower($arrFragments[0]) == 'index.php')
		{
			array_shift($arrFragments);
		}

		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl']))
		{
			foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback)
			{
				$this->import($callback[0]);
				$arrFragments = $this->$callback[0]->$callback[1]($arrFragments);
			}
		}

		$strParams = '';
		$arrGet = array();

		// Add fragments to URL params
		for ($i=1, $count=count($arrFragments); $i<$count; $i+=2)
		{
			if (isset($_GET[$arrFragments[$i]]))
			{
				$key = urldecode($arrFragments[$i]);
				\Input::setGet($key, null);
				$strParams .= '/' . $key . '/' . urldecode($arrFragments[$i+1]);
			}
		}

		// Add get parameters to URL
		if (is_array($_GET) && !empty($_GET))
		{
			foreach ($_GET as $key => $value)
			{
				// Ignore the language parameter
				if ($key == 'language' && $GLOBALS['TL_CONFIG']['addLanguageToUrl'])
				{
					continue;
				}

				$arrGet[] = $key . '=' . $value;
			}
		}

		global $objPage;
		return $this->generateFrontendUrl($objPage->row(), $strParams) . (!empty($arrGet) ? ('?'.implode('&', $arrGet)) : '');
	}
}

