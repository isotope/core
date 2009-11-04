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


/**
 * Class ProductOptionsWizard
 *
 * Provide methods to handle product options.  Based upon the TYPOlight option wizard by Leo Feyer
 */
class ProductOptionsWizard extends Widget
{
	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';

	/**
	 * Contents
	 * @var array
	 */
	protected $arrContents = array();

	
	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'value':
				$this->varValue = deserialize($varValue);
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}



	/**
	 * Trim values
	 * @param mixed
	 * @return mixed
	 */
/*	protected function validator($varInput)
	{
		if (is_array($varInput))
		{
			return parent::validator($varInput);
		}

		return parent::validator(trim($varInput));
	}
*/

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
	
		
			$strOptionSetValue = $this->Input->post('option_set_mode');
							
			switch($strOptionSetValue)
			{
				case 'new_option_set':
					$strOptionSetName = $this->Input->post('option_set_name');
					$arrValues = $this->Input->post('values');
					if(!sizeof($arrValues))
					{
						return $varValue;
					}else{
						foreach($arrValues as $key=>$attribute)
						{
							$arrAttributes[$key] = explode(',', trim($attribute));
						}
						
						
					}
					break;
				case 'existing_option_set':
					$strOptionSetId = $this->Input->post('option_sets');
					
					$arrSubProducts = $this->loadSubproducts($strOptionSetId);
					
					break;		
			}
			
			var_dump($arrAttributes);
	
		
		$this->import('Database');
		
		$objProductTypePid = $this->Database->prepare("SELECT pt.id AS ptId FROM tl_product_data pd, tl_product_types pt WHERE pt.id=pd.type AND pd.id=?")
											->limit(1)
											->execute($this->Input->get('id'));
		
		$intProductTypePid = $objProductTypePid->ptId;
		
		$arrOptions = $this->getProductOptionSets($intProductTypePid);
	
		$arrData = array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_product_data']['optionSetSelect'],
			'inputType'		=> 'select',
			'options'		=> $arrOptions,
		);
		
		$arrCreationWidgets['existing_option_set'] = $this->generateWidget($arrData, 'option_sets', $this->Input->post('option_sets'));
		
		$arrData = array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_product_data']['optionSetTitle'],
			'inputType'		=> 'text'
		);
		
		$arrCreationWidgets['new_option_set'] = $this->generateWidget($arrData, 'option_set_name', $this->Input->post('option_set_name'));
		
		$arrData = array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_product_data']['useOrCreateOptionSet'],
			'inputType'		=> 'radio',
			'options'		=> $arrCreationWidgets,
		);
		
		$strRadioGroup = $this->generateWidget($arrData, 'option_set_mode', $this->Input->post('option_set_mode'));
		
		
		//Load an existing product attribute set.  
		
		$strProductType = $this->getProductType($this->Input->get('id'));
		
		$arrEditableAttributes = $this->getEditableAttributes($strProductType);
		
		//Render the select box
		//$strSelectBoxWidget = $this->renderSelectBox('option_sets', $GLOBALS['TL_LANG']['MSC']['optionSetSelect'], $arrOptions, $this->Input->post('option_sets'));
								
			
		//Render text box, title for new product option set
		$strTxtOptionSetName = $this->renderTextBox('option_set_name', $GLOBALS['TL_LANG']['MSC']['optionSetTitle']);
					
		//$strProductVariants = $this->renderProductVariants($intProductOptionSetId);
		
		//$arrVariants = $this->getSubProductData($this->Input->get('id'));
		
		//$strSelectAllCheckbox = $this->renderSelectAllCheckbox();
		
		$return .= '<div class="tl_productoptionwizard_create">';
		/*$return .= $strSelectBoxWidget . '<br /><br />';
		$return .= $strTxtOptionSetName . '<br /><br />';
		*/
		$return .= $strRadioGroup;
		$return .= '<br /><br />';

		$return .= '<strong>Attribute Values</strong><br /><br />';
		
		foreach($arrEditableAttributes as $attribute)
		{
			$return .= $this->renderTextBox('values[' . $attribute['field_name'] . ']', $attribute['name']) . '<br />';
		}
				
		$return .= '</div>';
		$return .= '<br /><br />';
		$return .= '<div><input type="submit" name="generate_subproducts" id="ctrl_generate_subproducts" value="' . $GLOBALS['TL_LANG']['MSC']['generateSubproducts'] . '" /></div>';
		$return .= '<br /><br />';	
		// Add label and return wizard
		$return .= '<table cellspacing="0" cellpadding="5" id="ctrl_'.$this->strId.'" class="tl_productoptionwizard_variants" summary="Module wizard">
		  <thead>
		  <tr>
		    <th>' . $strSelectAllCheckbox . '</th>
		    <th>'.$GLOBALS['TL_LANG'][$this->strTable]['values'].'</th>
		    <th>'.$GLOBALS['TL_LANG'][$this->strTable]['sku'].'</th>
		    <th>'.$GLOBALS['TL_LANG'][$this->strTable]['quantity'].'</th>
			<th>'.$GLOBALS['TL_LANG'][$this->strTable]['price_change'].'</th>
		    <th>'.$GLOBALS['TL_LANG'][$this->strTable]['weight_change'].'</th>
		  </tr>
		  </thead>
		  <tbody>';
		
		/* $arrVariants has
		
			'attribute_values','sku' (text), 'quantity' (text), 'price_change' (inputUnit), 'weight' (text), 
		*/
		
		if(sizeof($arrVariants))
		{
			foreach($arrVariants as $variant)
			{
				$return .= '<tr>';
				
				foreach($variant as $k=>$v)
				{
					
					$return .= '<td>' . '&nbsp;' . '</td>';
				}
				
				$return .= '</tr>';
			
			}
		}
		else
		{
			$return .= '<tr><td colspan="6"><em>' . $GLOBALS['TL_LANG']['MSC']['noVariants'] . '</em></td></tr>';
		}		  
		
		
		$return .= '</tbody></table>';
			
		return $return;	
	}
	
	
	protected function generateWidget($arrData, $strInputName, $varValue)
	{
		$strField = 'options_wizard';
		$strTable = 'tl_product_data';
		
		$intId = $this->Input->get('id');

		$arrWidget = $this->prepareForWidget($arrData, $strInputName, $varValue, $strField, $strTable);

		$objWidget = new $GLOBALS['BE_FFL'][$arrData['inputType']]($arrWidget);

		//$objWidget->xlabel = $GLOBALS['TL_LANG']['tl_product_data']['options_wizard'];
		$objWidget->currentRecord = $intId;
		
		// Validate field
		if ($this->Input->post('FORM_SUBMIT') == $strTable)
		{
			$paletteFields = array();
			$key = ($this->Input->get('act') == 'editAll') ? 'FORM_FIELDS_' . $intId : 'FORM_FIELDS';

			$objWidget->validate();

			if ($objWidget->hasErrors())
			{
				$this->noReload = true;
			}

			elseif ($objWidget->submitInput())
			{
				$varValue = $objWidget->value;

				// Sort array by key (fix for JavaScript wizards)
				if (is_array($varValue))
				{
					ksort($varValue);
					$varValue = serialize($varValue);
				}
				
				/*
				// Encrypt the value
				if ($arrData['eval']['encrypt'])
				{
					$varValue = $this->Encryption->encrypt($varValue);
				}

				// Save the current value
				try
				{
					$this->save($varValue);
				}

				catch (Exception $e)
				{
					$this->noReload = true;
					$objWidget->addError($e->getMessage());
					$this->blnCreateNewRecord = false;
				}*/
				
				
			}
		}
		
		return $objWidget->parse();
	
	}
	
	/*protected function loadSubProducts()
	{
		$strOptionSetValue = $this->Input->post('option_set_mode');
						
		switch($strOptionSetValue)
		{
			case 'new_option_set':
				$strOptionSetName = $this->Input->post('option_set_name');
				$arrValues = $this->Input->post('values');
				if(!sizeof($arrValues))
				{
					return $varValue;
				}else{
					foreach($arrValues as $key=>$attribute)
					{
						$arrAttributes[$key] = explode(',', trim($attribute));
					}
					
					
				}
				break;
			case 'existing_option_set':
				$strOptionSetId = $this->Input->post('option_sets');
				
				$arrSubProducts = $this->loadSubproducts($strOptionSetId);
				
				break;		
		}

		var_dump($arrAttributes);

	}*/
	
	protected function getProductType($intProductId)
	{
		$objProductType = $this->Database->prepare("SELECT type FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->execute($intProductId);
		
		if($objProductType->numRows < 1)
		{
			throw new Exception('no product type return for this product!');	//TODO: Add to language array
		}
		
		return $objProductType->type;
		
	}
	
	protected function getEditableAttributes($strProductType)
	{
		//Get attribute collection for given product type.
		$objEligibleAttributes = $this->Database->prepare("SELECT attributes FROM tl_product_types WHERE id=?")
												->limit(1)
												->execute($strProductType);
		
		
		if($objEligibleAttributes->numRows < 1)
		{
		
			return array();
		}										
		
		$arrEligibleAttributes = deserialize($objEligibleAttributes->attributes);
		
		$strFieldIds = join(',', $arrEligibleAttributes);
			
		$objAttributes = $this->Database->prepare("SELECT field_name, name FROM tl_product_attributes WHERE is_customer_defined=? AND id IN(" . $strFieldIds . ")")
										->execute(1);
		
		if($objAttributes->numRows < 1)
		{
			return array();
		}	
		
		$arrAttributes = $objAttributes->fetchAllAssoc();
		
		return $arrAttributes;
		
	}
	
	protected function renderInputUnit()
	{
		return sprintf('<input type="text" name="%s[value]" id="ctrl_%s" class="tl_text_unit%s" value="%s" onfocus="Backend.getScrollOffset();" /> <select name="%s[unit]" class="tl_select_unit" onfocus="Backend.getScrollOffset();">%s</select>',
						$strName,
						$strName,
						'',
						specialchars($arrValues['value']),
						$strName,
						implode('', $arrUnits));
	
	}
	
	protected function renderSelectAllCheckbox()
	{
		return '<span class="fixed"><input type="checkbox" id="check_all_variants" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this, \'ctrl_variants\')" /> <label for="check_all_variants" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label></span>';
	}
	

	protected function renderTextBox($strName, $strTitle)
	{
		return sprintf('<label for="%s">%s</label> <input autocomplete="off" type="text" name="%s" id="ctrl_%s" class="tl_text%s" value="%s"%s />', 
						$strName,
						$strTitle,
						$strName,
						$strName,
						'',
						'',
						'');
	
	}
	
	public function getSubProductData($intProductId)
	{
		//Load existing subproduct values
		$objSubProducts = $this->Database->prepare("SELECT * FROM tl_product_data WHERE pid=?")
										 ->execute($intProductId);
		
		if($objSubProducts->numRows < 1)
		{
			return '<em>' . $GLOBALS['TL_LANG']['ERR']['noSubProducts'] . '</em>';
		}
		
		//Add new values
		foreach($arrValues as $value)
		{
			//attribute values, sku, price change / unit, weight change, quantity
					
		
		}
	

	}

	protected function getProductOptionSets($intProductTypePid)
	{
		$objSets = $this->Database->prepare("SELECT id, title FROM tl_product_option_sets WHERE pid=?")
								  ->execute($intProductTypePid);
		
		if($objSets->numRows < 1)
		{
			return array();	
		}
		
		$arrSets = $objSets->fetchAllAssoc();
				
		foreach($arrSets as $row)
		{
			$arrReturn[$row['id']] = $row['title'];
		}
		
		return $arrReturn;
	
	}

	protected function renderSelectBox($strName, $strTitle, $arrOptions = array(), $varValue = null)
	{
		$strClass = 'tl_select';
		
		// Add empty option (XHTML) if there are none
		if (!count($arrOptions))
		{
			$arrOptions = array(array('value'=>'', 'label'=>'-'));
		}

		foreach ($arrOptions as $strKey=>$arrOption)
		{
			if (isset($arrOption['value']))
			{
				$arrOptions[] = sprintf('<option value="%s"%s>%s</option>',
										 specialchars($arrOption['value']),
										 ((is_array($varValue) && in_array($arrOption['value'] , $varValue) || $varValue == $arrOption['value']) ? ' selected="selected"' : ''),
										 $arrOption['label']);

				continue;
			}

			$arrOptgroups = array();

			foreach ($arrOption as $arrOptgroup)
			{
				$arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
										   specialchars($arrOptgroup['value']),
										   ((is_array($varValue) && in_array($arrOptgroup['value'] , $varValue) || $varValue == $arrOptgroup['value']) ? ' selected="selected"' : ''),
										   $arrOptgroup['label']);
			}

			$arrOptions[] = sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', specialchars($strKey), implode('', $arrOptgroups));
		}

		return sprintf('<label for="%s">%s</label> <select name="%s" id="ctrl_%s" class="%s%s" onfocus="Backend.getScrollOffset();">%s</select>',
						$strName,
						$strTitle,
						$strName,
						$strName,
						$strClass,
						(strlen($strClass) ? ' ' . $strClass : ''),
						implode('', $arrOptions)
					);
	}
	
	
	/*		if(strlen($this->varValue))
		{
			$arrValues = split(',',$this->varValue);
			
			$arrTrimmedValues = array_map(array($this,'trimElement'), $arrValues);
						
			$strSubProducts = $this->getSubProductsInterface($arrTrimmedValues);
		
		}			
		
		return sprintf($strAjax.'<input autocomplete="off" type="text" name="%s" id="ctrl_%s" class="tl_text%s" value="%s"%s />%s<br /><div style="display: none;" class="autocompleter-loading"></div><br /><br />%s',
						$this->strName,
						$this->strId,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						specialchars($this->varValue),
						$this->getAttributes(),
						$this->wizard,
						$strSubProducts);


		// Return if field size is missing
		if (!$this->size)
		{
			return '';
		}

		if (!is_array($this->varValue))
		{
			$this->varValue = array($this->varValue);
		}

		$arrFields = array();

		
		return sprintf('<div id="ctrl_%s"%s>%s</div>%s',
						$this->strId,
						(strlen($this->strClass) ? ' class="' . $this->strClass . '"' : ''),
						implode(' ', $arrFields),
						$this->wizard);
	}

	public function trimElement($v)
	{
		return trim($v);
	
	}
*/
}

