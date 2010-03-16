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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleProductReader extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_productreader';
	
	protected $strFormId = 'iso_product_reader';
	

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			
			$objTemplate->wildcard = '### ISOTOPE PRODUCT READER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Return if no product has been specified
		if (!strlen($this->Input->get('product')))
		{
			return '';
		}

		if (!strlen($this->iso_reader_layout))
		{
			$this->iso_reader_layout = 'iso_reader_default';
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
		global $objPage;
	
		$arrCleanUrl = explode('?', $this->Environment->request);
	
		$objProduct = $this->getProductByAlias($this->Input->get('product'));
			
		if (!$objProduct)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['invalidProductInformation'];
			return;
		}
		
		if(!$this->iso_disableFilterAjax)
		{
			$arrAttributes = $this->getInheritedAttributes($objProduct);
			
			$arrAjaxParams[] = 'id='. $this->id;
	
			$strAjaxParams = implode("&", $arrAjaxParams);	//build the ajax params
			
			$objScriptTemplate = new FrontendTemplate('js_products');
			$objScriptTemplate->ajaxParams = $strAjaxParams;	
			$objScriptTemplate->mId = $this->id;
			$objScriptTemplate->productJson = json_encode($arrAttributes);
			
			$GLOBALS['TL_MOOTOOLS'][] = $objScriptTemplate->parse();
		}
		
		// Buttons
		$arrButtons = array
		(
			'add_to_cart'		=> array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeCart', 'addProduct')),
		);
		
		if (isset($GLOBALS['TL_HOOKS']['isoReaderButtons']) && is_array($GLOBALS['TL_HOOKS']['isoReaderButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoReaderButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}
		}
						
		$arrTemplateData = array
		(
			'buttons'		=> $arrButtons,
			'quantityLabel'	=> $GLOBALS['TL_LANG']['MSC']['quantity'],
			'useQuantity'	=> $this->iso_use_quantity,
		);		
		
		
		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->formId = $this->strFormId;



		$this->Template->product = $this->generateProduct($objProduct, $this->iso_reader_layout, $arrTemplateData, $this->strFormId, $intParentProductId);	
				
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit) // && $this->Input->post('product_id') == $objProduct->id)
		{			

			foreach( $arrButtons as $button => $data )
			{
				if (strlen($this->Input->post($button)))
				{
					if (is_array($data['callback']) && count($data['callback']) == 2)
					{
					
						$this->import($data['callback'][0]);
						$this->{$data['callback'][0]}->{$data['callback'][1]}($objProduct, $this);
					}
					break;
				}
			}
			
			$this->reload();
		}

		$objPage->title .= ' - ' . $objProduct->name;
		$objPage->description .= $this->cleanForMeta($objProduct->description, 200);
	}		
	
	private function cleanForMeta($strText, $limit)
	{
		$string = strip_tags($strText);
		
		$break="."; 
		
		$pad=".";
		
		
		// return with no change if string is shorter than $limit  
		if(strlen($string) <= $limit) return $string; 
		
		// is $break present between $limit and the end of the string?  
		if(false !== ($breakpoint = strpos($string, $break, $limit))) 
		{ 
			if($breakpoint < strlen($string) - 1) 
			{ 
				$string = substr($string, 0, $breakpoint) . $pad; 
			} 
		} 
		
		return $string; 
		
	}
	
	
	/** 
	 * TODO - Switch to JSON to allow flexibility to grab and return structured data for use in various elements on the product reader page in a single call
	 */
	public function generateAjax()
	{		
		if(!$this->Input->get('variant'))
		{
			$objProduct = $this->getProductByAlias($this->Input->get('product'));
			$arrAttributes = $objProduct->getAttributes();
		}
		else
		{
			$objProduct = $this->getProduct((integer)$this->Input->get('variant'));
			
			$arrAttributes = $this->getInheritedAttributes($objProduct);	
		}
				
		echo json_encode($arrAttributes);

	}	

	public function getInheritedAttributes($objProduct)
	{
		$arrAttributes = $objProduct->getAttributes();
		
		if($objProduct->pid)
		{
			$objParentProduct = $this->getProduct($objProduct->pid);
			
			$arrParentAttributes = $objParentProduct->getAttributes();
			
			//unset($arrParentAttributes['images']);	//clear the image array
		}
			
		$arrAttributes = $objProduct->getAttributes();
			
		unset($arrAttributes['variants_wizard']);		
		
		foreach($arrAttributes as $k=>$v)
		{
			if(!$v)
			{				
				$arrAttributes[$k] = $arrParentAttributes[$k];				
			}
			
			switch($k)
			{
				case $this->Isotope->Store->priceField:
					$arrAttributes[$k] = $this->Isotope->formatPriceWithCurrency($v);
					break;
				
			}
			
		}	
		
		return $arrAttributes;	
		
	}

	public function getImages($intProductId)
	{
		$objImages = $this->Database->prepare("SELECT images FROM tl_product_data WHERE id=?")
								   ->limit(1)
								   ->execute($intProductId);
		
		if(!$objImages->numRows)
		{
			return array();
		}
		
		return deserialize($objImages->images);
	
	}

}

