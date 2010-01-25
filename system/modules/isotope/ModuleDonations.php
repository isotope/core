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


class ModuleDonations extends Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_donations';

	protected $strFormId = 'iso_donations';
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
		
		$objWidget = $this->generateDonationWidget();
	
		$objWidget->storeValues = true;
		$objWidget->tableless = false;		


		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId)
		{
			$objWidget->validate();
			$varValue = $objWidget->value;
				
		
			// Do not submit if there are errors
			if ($objWidget->hasErrors() || !$objWidget->value)
			{				
				$this->doNotSubmit = true;
				/*$objTemplate = new FrontendTemplate('mod_message');
				$objTemplate->class = 'payment_method';
				$objTemplate->hl = 'h2';
				$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['payment_method'];
				$objTemplate->type = 'error';
				$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];
				return $objTemplate->parse();*/
			}
			elseif ($objWidget->submitInput())
			{
				$this->import('IsotopeCart','Cart');
				// Set new value
								
				$objProduct = $this->getProduct($this->iso_donationProduct);
				
				//Manually cobble together a product
				$objProduct->price = $this->Input->post('donation_amount');
				$objProduct->reader_jumpTo_Override = $this->Environment->request;
				$this->Cart->addProduct($objProduct);
				
				$this->reload();
			}

		
		}
					
		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->formId = $this->strFormId;
		$this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['addDonation']);
		$this->Template->fields = $objWidget->parse();

	}
	
	


	private function generateDonationWidget()
	{
		$arrData = array
		(
			'label'			=> &$GLOBALS['TL_LANG']['MSC']['labelDonations'],
			'inputType'		=> 'text',
			'eval'			=> array('mandatory'=>true,'rgxp'=>'digit')
		);
		
		$objWidget = new FormTextField($this->prepareForWidget($arrData, 'donation_amount', ''));
	
		return $objWidget;
	}

		

	/**
	 * Get attribute data and do something with it based on the properties of the attribute.
	 *
	 * @param string
	 * @param integer
	 * @return array
	 *
	 */
	protected function getProductAttributeData($strFieldName)
	{		
		
		$objAttributeData = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE field_name=?")
										   ->limit(1)
										   ->execute($strFieldName);

		if($objAttributeData->numRows < 1)
		{			
			return array();
		}
		
		return $objAttributeData->fetchAssoc();
	}
	
	/**
	 * Shortcut for a single product by ID
	 */
	protected function getProduct($intId)
	{
		$objProduct = new IsotopeProduct();
		
		if (!$objProduct->findBy('id', $intId))
			return NULL;
					
		return $objProduct;
	}
	


}