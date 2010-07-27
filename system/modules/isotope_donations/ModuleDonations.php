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


class ModuleDonations extends ModuleIsotope
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_donations';

	protected $strFormId = 'iso_donations';
	
	/**
	 * for widgets, don't submit if certain validation(s) fail
	 * @var boolean;
	 */
	protected $doNotSubmit = false;
	
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE DONATION MODULE ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}		
				
		return parent::generate();
	}
	
	/**
	 * Compile module
	 */
	protected function compile()
	{
		$arrOptions = array();
		$arrWidgets = array();
		
		$arrWidgets[] = $this->generateWishListWidget();
		$arrWidgets[] = $this->generateCommentsWidget();
		
		foreach($arrWidgets as $objWidget)
		{
			$objWidget->storeValues = true;
			$objWidget->tableless = false;		
	
	
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
			{
				$objWidget->validate();
				$varValue = $objWidget->value;
					
			
				// Do not submit if there are errors
				if ($objWidget->hasErrors())
				{				
					$this->doNotSubmit = true;
				}
			}
			
			$arrOptions[] = $objWidget->name;
			$this->Template->fields .= $objWidget->parse() . '<br /><br />';
		}
		
		
		$objWidget = $this->generateDonationWidget();
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
		{
			$objWidget->validate();
			$varValue = $objWidget->value;
				
			
			// Do not submit if there are errors
			if ($objWidget->hasErrors())
			{				
				$this->doNotSubmit = true;
			}

					
		}
		
		$this->Template->fields .= $objWidget->parse();
	
		if($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit)
		{
			$objProduct = $this->getProduct($this->iso_donationProduct);
			
			//Manually cobble together a product
			$objProduct->price = $this->Input->post('donation_amount');
			$objProduct->reader_jumpTo_Override = $this->Environment->request;
			
			$this->import('IsotopeFrontend');
			
			$this->IsotopeFrontend->addToCart($objProduct);
			$this->reload();
		}
		
		$this->Template->productOptionsList = implode(',', $arrOptions);							
		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->formId = $this->strFormId;
		$this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['addDonation']);
		

	}
	
	
	private function generateWishListWidget()
	{
		$arrAttributeData = $GLOBALS['TL_DCA']['tl_iso_products']['fields']['wish_list']['attributes'];
		
		$arrOptionList = deserialize($arrAttributeData['option_list']);

		array_unshift($arrOptionList, array('value'=>'-','label'=>'-'));
		
		$arrData = array
		(
			'label'			=> array($arrAttributeData['name'],$arrAttributeData['description']),
			'inputType'		=> 'select',
			'eval'			=> array('includeBlankOption'=>true)
		);
		
		$objWidget = new FormSelectMenu($this->prepareForWidget($arrData, 'wish_list', ''));
		
		$objWidget->options = $arrOptionList;
	
		return $objWidget;
	}

	private function generateCommentsWidget()
	{
		$arrAttributeData = $GLOBALS['TL_DCA']['tl_iso_products']['fields']['donation_comments']['attributes'];
	
		$arrData = array
		(
			'label'			=> array($arrAttributeData['name'],$arrAttributeData['description']),
			'inputType'		=> 'textarea',
			'eval'			=> array('rgxp'=>'extnd')
		);
		
		$objWidget = new FormTextArea($this->prepareForWidget($arrData, 'donation_comments', ''));
	
		return $objWidget;
	}

	private function generateDonationWidget()
	{
		$arrData = array
		(
			'label'			=> &$GLOBALS['TL_LANG']['MSC']['labelDonations'],
			'inputType'		=> 'text',
			'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit')
		);
		
		$objWidget = new FormTextField($this->prepareForWidget($arrData, 'donation_amount', ''));
		
		$objWidget->required = true;
		
		return $objWidget;
	}	
	
	
	/**
	 * Shortcut for a single product by ID
	 */
	protected function getProduct($intId)
	{
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE id=?")
										 ->limit(1)
										 ->executeUncached($intId);
									 
		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];
		
		if (!$this->classFileExists($strClass))
		{
			return null;
		}
									
		$objProduct = new $strClass($objProductData->row());
		
		return $objProduct;
	}
}