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
 * Class VariantsWizard
 *
 * Provide methods to handle modules of a page layout.
 */
class VariantsWizard extends Widget
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

	protected $strAttributes = 'type,pid,tstamp,sku,price,weight,stock_quantity';
	
	protected $arrEditableAttributes = array();
	
	protected $arrButtons = array();
	
	protected $valueCount = 0;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');
	}
	
	
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
			case 'attributes':
				$this->strAttributes = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}



	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$arrColumns = array();
		
		$this->import('Database');
		//$this->loadDataContainer('tl_product_data');
		$this->loadLanguageFile('tl_product_data');
		
		$blnTrackQuantity = false;
		
		$this->arrButtons = array('up','down','delete');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			switch ($this->Input->get($strCommand))
			{
				case 'up':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;
				case 'down':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;
				case 'delete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}
		}
		
		// STEP 1: Get product type data
		$arrProductTypeData = $this->getProductTypeData($this->Input->get('id'));
				
		// STEP 2: Get editable attributes
		$this->arrEditableAttributes = $this->getOptionAttributes(deserialize($arrProductTypeData['attributes']));
		
		// STEP 3: Build text boxes, one for each editable attribute
		foreach($this->arrEditableAttributes as $attribute)
		{
			$strAttributeTextBoxes .= $this->renderTextBox('values', $attribute['field_name'], $attribute['name']) . '<br />';
			
			if($this->Input->post('FORM_SUBMIT') == 'tl_product_data' && $this->Input->post($attribute['field_name']))
				$this->strAttributes .= ',' . $attribute['field_name'];
		}

		// STEP 4: Build array of value for matrices once "generate subproducts" is clicked.

		if($this->Input->post('FORM_SUBMIT') == "tl_product_data")
		{	
			
			if($this->Input->post('values'))
			{
				$arrValues = $this->Input->post('values');
				
				
				$arrSet = $this->createAttributeValues($arrValues);	//Structure the CSV input into an associative array
				
				if(count($arrSet))
				{	
					$this->createSubProducts($this->Input->get('id'), $arrSet);
				}
			}
						
			$arrEnabled = $this->Input->post('sub_published');
			$arrSku = $this->Input->post('sub_sku');
			$arrQuantity = $this->Input->post('sub_quantity');
			$arrPrice = $this->Input->post('sub_price');
			$arrWeight = $this->Input->post('sub_weight');
			
						
			$arrVariantData = array(
				'published'			=> $arrEnabled,
				'sku'				=> $arrSku,
				'stock_quantity'  	=> $arrQuantity,
				'price'				=> $arrPrice,
				'weight'			=> $arrWeight				
			);
			
			$this->saveVariantData($arrVariantData);
			
			if(count($arrEnabled))
			{
				foreach($arrEnabled as $k=>$v)
				{
					$arrInputValues[$k]['published'] = $v;
				}
			}
			
			if(count($arrSku))
			{			
				foreach($arrSku as $k=>$v)
				{
					$arrInputValues[$k]['id'] = $k;
					$arrInputValues[$k]['sku'] = $v;
				}
			}
			
			if(count($arrQuantity))
			{			
				foreach($arrQuantity as $k=>$v)
				{
					$arrInputValues[$k]['stock_quantity'] = $v;
				}
			}
			
			if(count($arrPrice))
			{			
				foreach($arrPrice as $k=>$v)
				{
					$arrInputValues[$k]['price'] = $v;
				}
			}
			
			if(count($arrWeight))
			{			
				foreach($arrWeight as $k=>$v)
				{
					$arrInputValues[$k]['weight'] = $v;
				}
			}			
			
						
			/*var_dump($arrSubProductData);
			exit;*/
			$this->saveSubProductData($arrInputValues);
		}
		
		$arrVariantData = $this->getVariantData($this->Input->get('id'));
		
				// STEP 4a: build SQL field string for subproduct data call		
		$arrSubProductData = $this->getSubProducts($this->Input->get('id'), $this->arrEditableAttributes, $arrVariantData);
		
		if(count($arrSubProductData))
		{
			$arrColumns = array_keys($arrSubProductData[0]);
		}
		
		// STEP 6: Render the "Select All" Checkbox
		$strSelectAllCheckbox = $this->renderSelectAllCheckbox();
		

		// STEP 7: Render control
			
		$return .= '<strong>' . $GLOBALS['TL_LANG']['tl_product_data']['opValueSets'] . '</strong><br /><br />';
		
		$return .= $strAttributeTextBoxes;
						
		//$return .= '<br /><br />';
		//$return .= '<div><input type="submit" name="generate_subproducts" id="ctrl_generate_subproducts" value="' . $GLOBALS['TL_LANG']['MSC']['generateSubproducts'] . '" /></div>';
		//TODO: ajax
		//$return .= '<div><input type="submit" name="generate_subproducts" id="ctrl_generate_subproducts" value="' . $GLOBALS['TL_LANG']['MSC']['generateSubproducts'] . '" onclick="IsotopeAjaxRequest.generateSubproducts(this, \''.$this->strId.'\');" /></div>';

		$return .= '<br /><br />';	
		// Add label and return wizard
		$return .= '<table width="100%" cellspacing="0" cellpadding="5" id="ctrl_'.$this->strId.'" class="tl_modulewizard" summary="Variant wizard">
		  <thead>
		  <tr>
		    <th><strong>'.$GLOBALS['TL_LANG']['tl_product_data']['enabled'].'</strong></th>'; /*' . $strSelectAllCheckbox . '</th>'*/;
		
		    foreach($arrColumns as $column)
		    {
		    	switch($column)
		    	{
		    		case 'id':
		    		case 'key':
		    		//case 'published':
		    			continue;
		    			break;		
					default:
		    			$return .= '<th><strong>'.$GLOBALS['TL_LANG']['tl_product_data'][$column][0].'</strong></th>';
						break;
		    	}
		    }
		   
		  '</tr>
		  </thead>
		  <tbody>';
		
		/*
			$arrSubProductData[] = array
			(
				'id'					=> $row['id'], 
				'key'					=> $arrAttributeValues['key'],
				'sub_published'				=> ($row['published'] ? ($blnTrackQuantity ? (($row['stock_quantity'] < 1 && !$blnAllowBackorder) ? 0 : 1) : 1) : 0),
				'values'				=> join(',', $arrAttributeValues['value']),
				'sku'					=> $row['sku'],
				'price'					=> $row['price'],
				'weight'				=> $row['weight'],
				'stock_quantity'				=> $row['stock_quantity']
			);
		*/
	
		if(count($arrSubProductData))	//Subproducts always created at this point or loaded.
		{
			foreach($arrSubProductData as $row)
			{
				$return .= '<tr>';
				
				foreach($row as $key=>$value)
				{	//id, enabled, attribute_values, sku, price, weight
					switch($key)
					{
						case 'id':
						case 'key':
							continue;
							break;
						case 'price':
							$return .= '<td align="center"><div style="float: left; padding-right:5px;">' . $value[0] . '</div> <div style="float: left; width: 20px;">$' .$value[1] . '</div></td>';
							break;
						case 'weight':
							$return .= '<td align="center"><div style="float: left; padding-right:5px;">' . $value[0] . '</div> <div style="float: left; style="width: 20px;">' . $value[1] . '</div></td>';
							break;
						default:
							$return .= '<td align="center">' . $value[0] . '</td>';
							break;
					}
				}
								
				$return .= '</tr>';
			}
		}else{
			$return .= '<tr><td colspan="6" align="center" style="width: 100%; height:20px; background-color: #eeeeee;">' . $GLOBALS['TL_LANG']['ERR']['noSubProducts'] . '</tr></td>';
		}
						  
		return $return.'</tbody></table>';  
		
	}
		
	/*
	public function generateCodes($arr) {
		if(count($arr)) {
			for($i=0; $i<count($arr[0]); $i++) {
				$tmp = $arr;
				$this->codes[$this->pos] = $arr[0][$i];
				$tarr = array_shift($tmp);
				$this->pos++;
				$this->generateCodes($tmp); 
	
			}
		} else {
			$this->arrCombos[] = $this->codes;
		}
		$pos--;
	}

*/
	
	protected function getAllPossibilities($a){
	   $out = array();
	   if (count($a) == 1) {
		  $x = array_shift($a);
		  foreach ($x as $v) $out[] = array($v);
		  return $out;
	   }
	   foreach ($a as $k => $v){
		  $b = $a;
		  unset($b[$k]);
		  $x = $this->getAllPossibilities($b);
		  foreach ($v as $v1){
			 foreach ($x As $v2) 
			 $out[] = array_merge(array($v1), $v2);
		  }
	   }
	   
	   if(count($a) > 1)
	   {
	   
	   		$arrReturn = array_slice($out, 0, count($out)/2);
	   }
	
	   //exit;
	   return $arrReturn;
	}

	protected function saveVariantData($arrData)
	{
		$strData = serialize($arrData);
		
		$this->Database->prepare("UPDATE tl_product_data SET variants_wizard=? WHERE id=?")
					   ->execute($strData, $this->Input->get('id'));
					   
	}
    
	protected function saveSubProductData($arrData)
	{
		
		/*
		$arrSubProductData['visible'];
		$arrSubProductData['sku'];
		$arrSubProductData['stock_quantity'];
		$arrSubProductData['price'];
		$arrSubProductData['weight'];
		
		$arrData(
			'key'	=> array(<id>=>value)
		)		
		*/
		
				
		foreach($arrData as $row)
		{
			foreach($row as $k=>$v)
			{
				switch($k)
				{
					case 'id':
						$intId = $row[$k];
						continue;
						break;
					case 'price':
					case 'weight':
						if($v)
						{
							$row[$k] = $this->calculateValue($v, $k);
						}
						else
						{						
							$row[$k] = $this->getStoredValue($row['id'], $k);
						}
						break;
				}
			}
			
						
			
			$this->Database->prepare("UPDATE tl_product_data SET price=?, weight=?, sku=?, published=?, stock_quantity=? WHERE id=?")->execute($row['price'], $row['weight'], $this->Input->post('sku') . $row['sku'], $row['published'], $row['stock_quantity'], $intId);
			
			
		}
					
		//NOTE SET values to nULL, not zero!		
		//var_dump($arrData);
		//exit;
	}
	
	protected function getStoredValue($intId, $strField)
	{
		$objData = $this->Database->prepare("SELECT " . $strField . " FROM tl_product_data WHERE id=?")
								  ->executeUncached($intId);
		
		if(!$objData->numRows)
		{
			return null;
		}
		
		return $objData->$strField;
	}
	
	protected function calculateValue($strValueInfo, $key, $intRoundTo = 2)
	{
		$strPattern = "/(([+-])?\d*\.?\d+)(%?)/i";
				
		$fltBaseValue = (float)$this->Input->post($key);	
									
		
		if(preg_match($strPattern, $strValueInfo, $arrMatches))
		{
			$strValue = $arrMatches[0];
						
			$blnPercentageCalc = (strpos($strValue, '%') ? true : false);
			
			$blnAddTo = ($arrMatches[2]=="+" ? true : false);
								
			$blnSubtractFrom = ($arrMatches[2]=="-" ? true : false);
						
			$fltChangeValue = (float)$arrMatches[1];
						
			if($blnPercentageCalc)
			{
				$fltNewValue = $fltBaseValue + ($fltBaseValue * ($fltChangeValue / 100));
			}
			else
			{
													
				$fltNewValue = ($blnAddTo || $blnSubtractFrom ? $fltBaseValue + $fltChangeValue : $fltChangeValue);	//straight overwrite
													
				if($fltNewValue < 0)
				{
					$fltNewValue = 0;
				}
			}	
			
			return round($fltNewValue, $intRoundTo);
			
		}
		else
		{
			
			return round($fltBaseValue, $intRoundTo);
		}
	
		
	}
	
	protected function saveChangeValue($intId, $strValue)
	{
		$this->Database->prepare("UPDATE tl_product_data SET " . $strChangeField . "=? WHERE id=?")
					   ->execute($strValue, $strId);
			
	}
	
	/**
	 * Take a raw collection of comma-separated values and transform it into an associative array
	 * @param array $arrAttributes
	 * @return array $arrSet;
	 */
	protected function createAttributeValues($arrAttributes = array())
	{
		$arrSet = array();
		$arrValues = array();
		
		if(count($arrAttributes))
		{
			
			//I need to take one or more attribute value sets, e.g. color: red, green, blue, and size: small, medium, large and create each possible combo as an attribute value set.
						
			foreach($arrAttributes as $k=>$v)
			{
				if(strlen($v))
				{
					$arrValues[] = explode(',', $v);			
				}
			}
			
			if(count($arrValues))
			{
			
				foreach($arrValues as $row)
				{
					$arrTrimmedValues = array();
					
					foreach($row as $value)
					{
						$arrTrimmedValues[] = trim($value);
					}
					
					$arrRows[] = $arrTrimmedValues;
				}

				$this->valueCount = count($arrRows);

				$arrSet = $this->getAllPossibilities($arrRows);
		
				asort($arrSet);
				
				return $arrSet;
			}
			else
			{
				return null;
			}
		}

	}

	
	/**
	 * Create new subproducts (duplicate checking to be added later, will compare using a value key like a page alias.
	 * @param array $arrSet
	 * return void
	 */
	protected function createSubProducts($intId, $arrSetFinal)
	{			
		//$intSize = (count($arrSet) / 2);

		//$arrSetFinal = array_chunk($arrSet, $intSize);	//have to split it in half for now.
		
			
		$i=0;
		
		$objProductType = $this->Database->prepare("SELECT type FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->execute($this->Input->get('id'));
										 
		if(!$objProductType->numRows)
		{
			$intProductType = 1;
		}
		else
		{
			$intProductType = $objProductType->type;
		}
		
	
		foreach($arrSetFinal as $row)
		{
						
			$arrValues = array((integer)$intProductType, (integer)$intId, time(), '', 0, 0, 0);	//starting row values		
				
			$arrRowCombinations[] = array_merge($arrValues, $row);
			
			$i++;
		}
				
		foreach($arrRowCombinations as $row)
		{
			$arrValueSets[] = join('\',\'', $row);
		}
		
		
		$strValues = join('\'),(\'', $arrValueSets);
		
		$arrAttributes = explode(',', $this->strAttributes);
		
		$i = 0;
		
				
		$strSQL = "INSERT INTO tl_product_data (" . implode(',', $arrAttributes) . ")VALUES('" . $strValues . "')";

					
		$this->Database->prepare($strSQL)->execute();
	}

	protected function getVariantData($intId)
	{
		$arrAttr = array();
		
		foreach($this->arrEditableAttributes as $row)
		{
			foreach($row as $k=>$v)
			{
				if($k=='field_name')
				{
					$arrAttr[] = $v;
				}
			}
		}
						
		$strEditableAttributes = implode(',', $arrAttr);
			
		$objData = $this->Database->prepare("SELECT id," . $this->strAttributes . ',' . $strEditableAttributes ." FROM tl_product_data WHERE pid=?")
								  ->limit(1)
								  ->execute($intId);
		
		if($objData->numRows < 1)
		{
			return null;
		}
		
		$arrData = $objData->fetchAllAssoc();

		return $arrData;
	}
	
	
	public function getSubProducts($intPid, $arrEditableAttributes = array())
	{
			
		$arrSubProductData = array();
		
		$arrAttr = array();
		
		//STEP 5: Get all subproducts from DB
		foreach($arrEditableAttributes as $row)
		{
			foreach($row as $k=>$v)
			{
				if($k=='field_name')
				{

					$arrAttr[] = $v;
				}
			}
		}
				
		$strEditableAttributes = implode(',', $arrAttr);

			$objSubProducts = $this->Database->prepare("SELECT id, " . $this->strAttributes . "," . $strEditableAttributes .", published FROM tl_product_data WHERE pid=?")
										 ->execute($intPid);
							 
		//$arrSubProducts[] = array('id'=>0, 'published'=>0, 'attribute_values'=>array(), 'sku'=>'', 'price'=>0, 'unit' => '#', 'weight' => '0', 'qty' => 0);
		
		if($objSubProducts->numRows < 1)
		{

			return array();
		}else{
					
			//Collect the values for each of the given customer defined attributes
			foreach($arrEditableAttributes as $attributeKey)
			{
				$arrAttributes[] = $attributeKey['field_name'];	//Get the currently used attribute data based on what attributes are eligible
			}
					
		}
					
	
		while($objSubProducts->next())
		{
			$arrAttributeValues = array();
			
			foreach($arrAttributes as $attribute)
			{
				$arrAttributeValues[] = $objSubProducts->$attribute;
			}

			$blnEnabled = ($objSubProducts->published ? ($blnTrackQuantity ? (($objSubProducts->stock_quantity < 1 && !$blnAllowBackorder) ? 0 : 1) : 1) : 0);
			
			$intCheckboxValue = $objSubProducts->published;
			
			//TODO: Store change values for price, weight.
			//$strPriceValue = (is_array($arrVariantData) ? $arrVariantData['price'][$row['id']] : '');
			//$strWeightValue = (is_array($arrVariantData) ? $arrVariantData['weight'][$row['id']] : '');
			
			$arrSubProductData[] = array
			(
				'id'					=> $objSubProducts->id,
				'key'					=> '',//$arrAttributeValues['key'],
				'published'				=> array($this->renderMatrixCheckBox('sub_published', $objSubProducts->id, $objSubProducts->published)),
				'values'				=> array(join(',', $arrAttributeValues)),
				'sku'					=> array($this->renderMatrixTextBox('sub_sku', $objSubProducts->id, $objSubProducts->sku, 60)),
				'price'					=> array($this->renderMatrixTextBox('sub_price', $objSubProducts->id, $strPriceValue, 60), $objSubProducts->price),
				'weight'				=> array($this->renderMatrixTextBox('sub_weight', $objSubProducts->id, $strWeightValue, 60), $objSubProducts->weight),
				'stock_quantity'		=> array($this->renderMatrixTextBox('sub_stock_quantity', $objSubProducts->id, $objSubProducts->stock_quantity)),
				'buttons'				=> $this->generateButtons('variants_wizard', $objSubProducts->id)
			);
		}
			
		return $arrSubProductData;
	}
	
	protected function generateButtons($strCommand, $strId)
	{
		foreach ($this->arrButtons as $button)
				{
					$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;id='.$strId).'" title="'.specialchars($GLOBALS['TL_LANG']['tl_product_data']['wz_'.$button]).'" onclick="Backend.moduleWizard(this, \''.$button.'\',  \'ctrl_'.$strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG']['tl_product_data']['wz_'.$button], 'class="tl_listwizard_img"').'</a> ';
				}
				
		return $return;		
	}
	

	
	protected function getProductTypeData($intId)
	{
		
		$objProductTypeData = $this->Database->prepare("SELECT pt.id, pt.attributes FROM tl_product_types pt, tl_product_data pd WHERE pt.id=pd.type AND pd.id=?")
											 ->limit(1)
											 ->execute($intId);
		
		
		$arrData = $objProductTypeData->fetchAssoc();
		
		return $arrData;
	}
	
	
	protected function getOptionAttributes($arrAttributes)
	{
		
		$objOptionAttributes = $this->Database->prepare("SELECT id, name, field_name FROM tl_product_attributes WHERE is_customer_defined='1' AND add_to_product_variants='1' AND field_name IN('" . implode("','", $arrAttributes) . "')")
											  ->execute();
				
			
		if($objOptionAttributes->numRows < 1)
		{
			return array();
		}		
		
		$arrRows = $objOptionAttributes->fetchAllAssoc();
		
		$arrData = array();
		
		foreach($arrRows as $row)
		{
			$arrData[] = $row;
		}
	
		return $arrData;
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
		return '<span class="fixed"><label for="check_all_enabled" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br /><input type="checkbox" id="check_all_opt_sub_published" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this, \'opt_sub_published\')" /></span>';
	}
	
	protected function renderMatrixCheckBox($strName, $intId, $strValue, $intLength=15)
	{
		
		return sprintf('<input type="checkbox" name="%s[%s]" id="opt_%s_%s" class="tl_checkbox" value="1" onfocus="Backend.getScrollOffset();"  style="width: %spx;" %s />',
						$strName,
						$intId,
						$strName,
						$intId,
						$intLength,
						($strValue=='1' ? 'checked' : '')					
						);
	}
	
	protected function renderMatrixTextBox($strName, $strId, $strValue, $intLength=25)
	{
		return sprintf('<input autocomplete="off" type="text" name="%s[%s]" id="ctrl_%s" class="tl_text" value="%s" size="5" style="width: %spx; text-align: center;" />', 
						$strName,
						$strId,
						$strId,
						$strValue,
						$intLength
					);
	
	}

	protected function renderTextBox($strName, $strId, $strTitle, $blnMandatory = false)
	{
		return sprintf('<label for="%s">%s</label> <input autocomplete="off" type="text" name="%s[%s]" id="ctrl_%s" class="tl_text%s" value="%s"%s />', 
						$strName,
						$strTitle,
						$strName,
						$strId,
						$strName,
						'',
						'',
						'');
	
	}
}

