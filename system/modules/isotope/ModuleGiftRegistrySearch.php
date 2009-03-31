<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    IsotopeBase 
 * @license    Commercial 
 * @filesource
 */


/**
 * Class ModuleGiftRegistrySearch
 *
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
 */
class ModuleGiftRegistrySearch extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_registry_formsearch';


	/**
	 * Make sure the UFO plugin is available
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### GIFT REGISTRY SEARCH ###';

			return $objTemplate->parse();
		}

		// Set last page visited
		if ($this->redirectBack)
		{
			$_SESSION['LAST_PAGE_VISITED'] = $this->getReferer();
		}

		// Form Submit
		if ($this->Input->post('FORM_SUBMIT') == 'tl_registry_search')
		{
			// Check whether last name is set
			if (!$this->Input->post('lastname'))
			{
				$_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['registry']['emptyField'];
				$this->reload();
			}

			$this->import('FrontendUser', 'User');
			$strRedirect = $this->Environment->request;
			
			$lastname = $this->Input->post('lastname');
			
			if($this->Input->post('date'))
			{
			$date = $this->Input->post('date');
			$arrDate = explode('/',$date);
			$dateTime = mktime(0, 0, 0, $arrDate[0], $arrDate[1], $arrDate[2]);
			}
			else
			{
			$dateTime = '';
			}
			
			$strParams = "/lastname/" . $lastname . "/date/" . $dateTime; 

			// Redirect to jumpTo page
			if (strlen($this->jumpTo))
			{
				$objNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
											  ->limit(1)
											  ->execute($this->jumpTo);

				if ($objNextPage->numRows)
				{
					$strRedirect = $this->generateFrontendUrl($objNextPage->fetchAssoc(), $strParams);
				}
			}

			$this->redirect($strRedirect);
			//$this->reload();
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		
		$this->strTemplate = 'iso_registry_formsearch';
		$this->Template = new FrontendTemplate($this->strTemplate);

		$this->Template->message = '';

		// Show login form
		if (count($_SESSION['TL_ERROR']))
		{
			$_SESSION['LOGIN_ERROR'] = $_SESSION['TL_ERROR'][0];
			$_SESSION['TL_ERROR'] = array();
		}

		if (strlen($_SESSION['LOGIN_ERROR']))
		{
			$this->Template->message = $_SESSION['LOGIN_ERROR'];
			$_SESSION['LOGIN_ERROR'] = '';
		}

		$this->Template->lastname = $GLOBALS['TL_LANG']['MSC']['registry']['lastname'];
		$this->Template->datestr = $GLOBALS['TL_LANG']['MSC']['registry']['datestr'];
		$this->Template->action = $this->Environment->request;
		$this->Template->submitlabel = $GLOBALS['TL_LANG']['MSC']['registry']['registrySearch'];
		$this->Template->lastnamevalue = specialchars($this->Input->post('lastname'));
		$this->Template->datevalue = $this->Input->post('date');
	}
	
}

?>