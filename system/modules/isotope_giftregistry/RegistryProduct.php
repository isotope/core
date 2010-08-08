<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
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
 * @copyright  Intelligent Spark 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class RegistryProduct extends IsotopeProduct
{
		
	/**
	 * Set a property
	 */
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			default:
				parent::__set($strKey, $varValue);
		}
	}
	
	/**
	 * Generate a product template - RegistryProduct does not need to validate widgets since all options are pre-selected
	 */
	public function generate($strTemplate, &$objModule)
	{		
		$objTemplate = new FrontendTemplate($strTemplate);
		
		$arrProductOptions = array();
		$arrAjaxOptions = array();
		$arrAttributes = $this->getAttributes();
		
		foreach( $arrAttributes as $attribute => $varValue )
		{
				$objTemplate->$attribute = $this->generateAttribute($attribute, $varValue);
        }
        
        
        // Buttons
		$arrButtons = array();
		if (isset($GLOBALS['TL_HOOKS']['isoButtons']) && is_array($GLOBALS['TL_HOOKS']['isoButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}
			
			$arrButtons = array_intersect_key($arrButtons, array_flip(deserialize($objModule->iso_buttons, true)));
		}
				
		if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.$this->id)
		{			
			foreach( $arrButtons as $button => $data )
			{
				if (strlen($this->Input->post($button)))
				{
					if (is_array($data['callback']) && count($data['callback']) == 2)
					{
						$this->import($data['callback'][0]);
						$this->{$data['callback'][0]}->{$data['callback'][1]}($this, $objModule);
					}
					break;
				}
			}
		}
		
		
		$objTemplate->buttons = $arrButtons;
		$objTemplate->quantityLabel = $GLOBALS['TL_LANG']['MSC']['quantity'];
		$objTemplate->useQuantity = $objModule->iso_use_quantity;
			

		$objTemplate->raw = $this->arrData;
		$objTemplate->href_reader = $this->href_reader;
		
		$objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
		
		$objTemplate->options = $arrProductOptions;	
		$objTemplate->hasOptions = count($arrProductOptions) ? true : false;
		
		$objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$objTemplate->formId = 'iso_product_'.$this->id;
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->formSubmit = 'iso_product_'.$this->id;
		
		// HOOK for altering product data before output
		if (isset($GLOBALS['TL_HOOKS']['iso_generateProduct']) && is_array($GLOBALS['TL_HOOKS']['iso_generateProduct']))
		{
			  foreach ($GLOBALS['TL_HOOKS']['iso_generateProduct'] as $callback)
			  {
				$this->import($callback[0]);
				$objTemplate = $this->$callback[0]->$callback[1]($objTemplate, $this);
			  }
		}

		return $objTemplate->parse();
	}



}

