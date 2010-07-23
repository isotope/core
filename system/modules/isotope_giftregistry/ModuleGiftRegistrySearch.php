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
 
 
class ModuleGiftRegistrySearch extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_registry_search';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### GIFT REGISTRY SEARCH ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

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
		if ((!$_GET['lastname'] || !$_GET['date']) && $this->Input->post('FORM_SUBMIT') == 'tl_registry_search')
		{
			$_GET['lastname'] = $this->Input->post('lastname');
			$_GET['date'] = $this->Input->post('date');
		}

		$objFormTemplate = new FrontendTemplate('iso_registry_formsearch');

		$objFormTemplate->queryType = $this->queryType;
		$objFormTemplate->keyword = specialchars($strKeywords);
		$objFormTemplate->lastnameLabel = $GLOBALS['TL_LANG']['MSC']['lastNameLabel'];
		$objFormTemplate->dateLabel = $GLOBALS['TL_LANG']['MSC']['dateLabel'];
		$objFormTemplate->searchRegistry = specialchars($GLOBALS['TL_LANG']['MSC']['searchregistryLabel']);
		$objFormTemplate->matchAll = specialchars($GLOBALS['TL_LANG']['MSC']['matchAll']);
		$objFormTemplate->matchAny = specialchars($GLOBALS['TL_LANG']['MSC']['matchAny']);
		$objFormTemplate->id = ($GLOBALS['TL_CONFIG']['disableAlias'] && $this->Input->get('id')) ? $this->Input->get('id') : false;
		$objFormTemplate->action = ampersand($this->Environment->request);

		$this->Template->form = $objFormTemplate->parse();
		$this->Template->results = '';
		
		$strLastname = $_GET['lastname'];
		
		$dateQuery = '';
		$intDate = 0;
		if(strlen($_GET['date']))
		{
			$strDate = html_entity_decode($_GET['date']);
			$intDate = strtotime($strDate);
			$dateFuture = strtotime('+1month', $intDate);
			$datePast = strtotime('-1month', $intDate);
			$dateQuery = " AND ( date >  '" . $datePast . "' AND date <  '" . $dateFuture . "' )";
		}

		// Execute search if there are keywords
		if ((strlen($strLastname) || $intDate>0) && $strLastname != '*')
		{
			$arrResult = null;
			
			$nameQuery = 'AND (m.lastname LIKE "%' . $strLastname . '%" OR r.name LIKE "%' . $strLastname . '%" OR r.second_party_name LIKE "%' . $strLastname . '%")';

			// Get result
			if (is_null($arrResult))
			{
				try
				{
					$objSearch = $this->Database->prepare("SELECT r.id, r.name, r.date, r.event_type, r.second_party_name FROM tl_iso_registry r, tl_member m WHERE r.pid = m.id {$nameQuery}{$dateQuery}")->execute();
					$arrResult = $objSearch->fetchAllAssoc();
				}
				catch (Exception $e)
				{
					$this->log('Registry search failed: ' . $e->getMessage(), 'ModuleGiftRegistrySearch compile()', TL_ERROR);
					$arrResult = array();
				}

			}
			
			$count = count($arrResult);

			// No results
			if ($count < 1)
			{
				$this->Template->header = sprintf($GLOBALS['TL_LANG']['MSC']['sEmpty'], $strLastname);

				return;
			}

			$from = 1;
			$to = $count;
			
			$this->loadLanguageFile('tl_iso_registry');

			// Get results
			for ($i=($from-1); $i<$to && $i<$count; $i++)
			{
				$objTemplate = new FrontendTemplate((strlen($this->searchTpl) ? $this->searchTpl : 'search_registry_default'));

				$objTemplate->id = $arrResult[$i]['id'];
				$objTemplate->name = $arrResult[$i]['name'];
				$objTemplate->date = $arrResult[$i]['date'];
				$objTemplate->second_party_name = $arrResult[$i]['second_party_name'];
				$objTemplate->event_type = $GLOBALS['TL_LANG']['tl_iso_registry'][$arrResult[$i]['event_type']];
				$objTemplate->href = $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->jumpTo)->fetchAssoc(), '/rid/' . $arrResult[$i]['id']);
				$objTemplate->class = (($i == ($from - 1)) ? 'first ' : '') . (($i == ($to - 1) || $i == ($count - 1)) ? 'last ' : '') . (($i % 2 == 0) ? 'even' : 'odd');

				$this->Template->results .= $objTemplate->parse();
			}
			
			$strResults = (strlen($strLastname)) ? $GLOBALS['TL_LANG']['MSC']['lastNameLabel'] . ' ' . $strLastname . ' ' : '';
			$strResults .= (strlen($strDate)) ? $GLOBALS['TL_LANG']['MSC']['dateLabel'] . ' ' . $strDate . ' ' : '';
			
			$this->Template->header = vsprintf($GLOBALS['TL_LANG']['MSC']['rResults'], array($from, $to, $count, $strResults));
		}
	}
}

?>