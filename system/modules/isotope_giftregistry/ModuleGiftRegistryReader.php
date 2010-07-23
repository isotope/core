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


class ModuleGiftRegistryReader extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_registry_reader';

	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE CART ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		// Return if no registry has been specified
		if (!strlen($this->Input->get('rid')))
		{
			return '';
		}
		
		global $objPage;
		
		$this->iso_reader_jumpTo = $objPage->id;

		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		if(!$this->Isotope->Registry)
		{
			$this->Isotope->Registry = new IsotopeRegistry();
		}
		$this->Isotope->Registry->findBy('id',$this->Input->get('rid'));
		$this->Isotope->Registry->updateSold(); //Need to do this until we can hook into writeOrder on checkout
		$arrProducts = $this->Isotope->Registry->getProducts();
		
		if (!count($arrProducts))
		{
		   $this->Template = new FrontendTemplate('mod_message');
		   $this->Template->type = 'empty';
		   $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInRegistry'];
		   return;
		}
				
		global $objPage;
		$strUrl = $this->generateFrontendUrl($objPage->row());
		
		$arrBuffer = array();
		
		foreach( $arrProducts as $i => $objProduct )
		{
			$regInfo = $this->Isotope->Registry->getProductInfo($objProduct);
			
			$arrBuffer[] = array
			(
				'class'			=> ('product' . ($i == 0 ? ' product_first' : '')),
				'html'			=> $objProduct->generate((strlen($this->iso_list_layout) ? $this->iso_list_layout : $objProduct->list_template), $this),
				'quantity_req'	=> $regInfo[0]['product_quantity'],
				'quantity_sold'	=> $regInfo[0]['quantity_sold'],
				'options'		=> deserialize($regInfo[0]['product_options'])
			);
			
			$blnSetClear = (($i+1) % $this->columns==0 ? true : false);
		}
	
		// Add "product_last" css class
		if (count($arrBuffer))
		{
			$arrBuffer[count($arrBuffer)-1]['class'] .= ' product_last';
		}
		
		$this->loadLanguageFile('tl_iso_registry');
		$strPeople = $this->Isotope->Registry->name;
		$strPeople .= ($this->Isotope->Registry->second_party_name) ? ' & ' . $this->Isotope->Registry->second_party_name : '';
				
		$this->Template->products = $arrBuffer;
		$this->Template->registryTitle = vsprintf($GLOBALS['TL_LANG']['MSC']['registryOwnerTitle'], $strPeople) ;
		$this->Template->name = $this->Isotope->Registry->name;
		$this->Template->second_party_name = $this->Isotope->Registry->second_party_name;
		$this->Template->date = $this->Isotope->Registry->date;
		$this->Template->event_type = $GLOBALS['TL_LANG']['tl_iso_registry'][$this->Isotope->Registry->event_type];
		$this->Template->description = $this->Isotope->Registry->description;
		
	}	
	
}