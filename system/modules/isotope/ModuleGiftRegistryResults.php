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
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleGiftRegistryResults extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_registry_results_lister';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE REGISTRY SEARCH RESULTS ###';

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		
		// Trigger the search module from a custom form
		if (!$_GET['lastname'] && !$_GET['date'] &&  $this->Input->post('FORM_SUBMIT') == 'tl_registry_search')
		{
			$_GET['lastname'] = $this->Input->post('lastname');
			$_GET['date'] = $this->Input->post('date');
			$_GET['name'] = $this->Input->post('query_type');
			$_GET['per_page'] = $this->Input->post('per_page');
		}

		$strLastname = trim($this->Input->get('lastname'));
		
		$strDate = '';
		$dateQuery = '';
		
		if($this->Input->get('date'))
		{
			$timeDate = $this->Input->get('date');
			$strDate = date('m/d/y', $timeDate);
			$timePlusOne = strtotime('+1month', $timeDate);
			$timeMinusOne = strtotime('-1month', $timeDate);
			$dateQuery = " AND ( r.date >  '" . $timeMinusOne . "' AND r.date <  '" . $timePlusOne . "' )";
		}
		
		// Overwrite default query_type.. Not sure if we are using this.
		if ($this->Input->get('query_type'))
		{
			$this->queryType = $this->Input->get('query_type');
		}
		
		//Form Jump To - Strips variables out. This should be made in to a global function
		
		$currPage = $this->Environment->request;
		
		
		// Form Submit
		if ($this->Input->post('FORM_SUBMIT') == 'tl_registry_search')
		{
			// Check whether last name and date are set
			if (!$this->Input->post('lastname'))
			{
				$_SESSION['LOGIN_ERROR'] = $GLOBALS['TL_LANG']['MSC']['registry']['emptyField'];
				$this->reload();
			}

			$this->import('FrontendUser', 'User');
			$strRedirect = $this->Environment->request;
			
			$lastname = $this->Input->post('lastname');
			$date = $this->Input->post('date');
			$arrDate = explode('/',$date);
			$dateTime = mktime(0, 0, 0, $arrDate[0], $arrDate[1], $arrDate[2]);
			
			$this->reload();
		}

		
		$objFormTemplate = new FrontendTemplate('iso_registry_formsearch');

		$this->Template->pagination = '';
		$this->Template->results = '';

		// Execute search if there is a lastname
		if (strlen($strLastname) && $strLastname != '*')
		{

			$arrRegResults = array();
			
			$queryNameLike = "%" . $strLastname;
			
			// Query for results			
			$arrRegistriesQuerystr = "SELECT r.id, r.name, r.date, m.firstname, m.lastname, c.id FROM tl_registry r, tl_member m, tl_cart c WHERE r.pid = c.id AND c.pid = m.id AND c.cart_type_id=? AND ( m.lastname LIKE ? OR r.name LIKE ?" . $dateQuery . ")";

			$objRegQuery = $this->Database->prepare($arrRegistriesQuerystr)
					   						->execute(2,$queryNameLike,$queryNameLike);
			
			
			$count = count($objRegQuery);
			
			if ($objRegQuery->numRows)
				{
					$arrRegResults = $objRegQuery->fetchAssoc();
				}
			
			//var_dump($arrRegResults);

			// No results
			if (sizeof($arrRegResults)<1)
			{
				$this->Template->header = sprintf($GLOBALS['TL_LANG']['MSC']['registry']['noresultsText'], $strLastname) . '<br /><br /><div align="center"><a href="' . ampersand($this->getReferer()) . '">Go Back</a></div>';
			
				return;
			}
			
			$from = 1;
			$to = $count;

			// Pagination
			if ($this->perPage > 0)
			{	
				$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
				$per_page = $this->Input->get('per_page') ? $this->Input->get('per_page') : $this->perPage;

				// Reset page navigator if page exceeds the lower or upper limit
				if ($page > ceil($count/$per_page) || $page < 1)
				{
					$page = 1;
				}

				$from = (($page - 1) * $per_page) + 1;
				$to = (($from + $per_page) > $count) ? $count : ($from + $per_page - 1);

				// Pagination menu
				if ($to < $count || $from > 1)
				{
					$objPagination = new Pagination($count, $per_page);
					$this->Template->pagination = $objPagination->generate("\n  ");
				}
			}
			
			// Redirect to jumpTo page
			if (strlen($this->jumpTo))
			{
				$objNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
											  ->limit(1)
											  ->execute($this->jumpTo);

				if ($objNextPage->numRows)
				{
					$strRedirect = $objNextPage->fetchAssoc();
				}
			}
			

			// Get results data
			for ($i=($from-1); $i<$to && $i<$count; $i++)
			{
				

				$objTemplate = new FrontendTemplate((strlen($this->iso_registry_results) ? $this->iso_registry_results : 'iso_registry_search_default'));
				
				$strParams = "/cartid/" . $arrRegResults['id'];
				
				$objTemplate->link = $this->generateFrontendURL($strRedirect, $strParams);
				$objTemplate->name = specialchars($arrRegResults['firstname']) . " " . specialchars($arrRegResults['lastname']);
				$objTemplate->class = (($i == ($from - 1)) ? 'first ' : '') . (($i == ($to - 1) || $i == ($count - 1)) ? 'last ' : '') . (($i % 2 == 0) ? 'even' : 'odd');
				
				$this->Template->results .= $objTemplate->parse();
			}	
			
			$this->Template->header = vsprintf($GLOBALS['TL_LANG']['MSC']['registry']['sResults'], array($count, $strLastname, $strDate));
		}
		
		$objFormTemplate->queryType = $this->queryType;
		$objFormTemplate->lastnamevalue = specialchars($strLastname);
		$objFormTemplate->datevalue = specialchars($strDate);
		$objFormTemplate->submitlabel = $GLOBALS['TL_LANG']['MSC']['registry']['registrySearch'];
		$objFormTemplate->action = ampersand($this->Environment->request);

		$this->Template->form = $objFormTemplate->parse();
		
	}
	
	
	/* USED TO STRIP CURRENT QUERY VARIABLES FORM URL */
	protected function stripQuery()
	{
	
	
	
	
	
	
	
	}
}

