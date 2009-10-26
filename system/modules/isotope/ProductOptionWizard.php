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
 * Class ProductOptionWizard
 *
 * Provide methods to handle product options.  Based upon the TYPOlight option wizard by Leo Feyer
 */
class ProductOptionWizard extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';

	/**
	 * Attribute Value Options
	 * @var array
	 */
	protected $arrAttributeOptions = array();
	
	
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

			case 'maxlength':
				$this->arrAttributes[$strKey] = ($varValue > 0) ? $varValue : '';
				break;

			case 'options':
				$this->arrCurrValueOptions = deserialize($varValue);
				break;
				
			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Validate input and set value
	 */
	public function validate()
	{
		$mandatory = $this->mandatory;
		$options = deserialize($this->getPost($this->strName));

		// Check labels only (values can be empty)
		/*if (is_array($options))
		{
			foreach ($options as $key=>$option)
			{
				$options[$key]['label'] = trim($option['label']);
				$options[$key]['value'] = trim($option['value']);

				if (strlen($options[$key]['label']))
				{
					$this->mandatory = false;
				}
			}
		}*/

		$varInput = $this->validator($options);

		if (!$this->hasErrors())
		{
			$this->varValue = $varInput;
		}

		// Reset the property
		if ($mandatory)
		{
			$this->mandatory = true;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/isotope.js';

		$this->import('Database');
			
		$arrButtons = array('copy', 'delete');
		$strCommand = 'cmd_' . $this->strField;
		
		$this->strTable = $this->Input->get('table');
		
		$arrOptionAttributes = $this->getOptionAttributes();
		
		$arrOptionValues = $this->getAllOptionValues($arrOptionAttributes);
				
		$arrColButtons = array('ccopy', 'cdelete');
		$arrRowButtons = array('rcopy', 'rdelete');

		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			$this->import('Database');

			switch ($this->Input->get($strCommand))
			{
					case 'ccopy':
					for ($i=0; $i<count($this->varValue); $i++)
					{
						$this->varValue[$i] = array_duplicate($this->varValue[$i], $this->Input->get('cid'));
					}
					break;
				
				case 'cmovel':
					for ($i=0; $i<count($this->varValue); $i++)
					{
						$this->varValue[$i] = array_move_up($this->varValue[$i], $this->Input->get('cid'));
					}
					break;

				case 'cmover':
					for ($i=0; $i<count($this->varValue); $i++)
					{
						$this->varValue[$i] = array_move_down($this->varValue[$i], $this->Input->get('cid'));
					}
					break;

				case 'cdelete':
					for ($i=0; $i<count($this->varValue); $i++)
					{
						$this->varValue[$i] = array_delete($this->varValue[$i], $this->Input->get('cid'));
					}
					break;

				case 'rcopy':
					$this->varValue = array_duplicate($this->varValue, $this->Input->get('cid'));
					break;

				case 'rup':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;

				case 'rdown':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;

				case 'rdelete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}

			if(!is_null($this->varValue))
			{
				
				$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
							   ->execute(serialize($this->varValue), $this->currentRecord);
						
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
			}
		}

		// Make sure there is at least an empty array
		if (!is_array($this->varValue) || !$this->varValue[0])
		{
			$this->varValue = array(array(''));
		}

		// Begin table
		$return .= '<div id="tl_tablewizard">
  <table cellspacing="0" cellpadding="0" class="tl_tablewizard" id="ctrl_'.$this->strId.'" summary="Table wizard">
  <tbody>
    <tr>';

		// Add column buttons
		for ($i=0; $i<count($this->varValue[0]); $i++)
		{
			$return .= '
      <td style="text-align:center; white-space:nowrap;">';

			// Add column buttons
			foreach ($arrColButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$button][0]).'" onclick="ProductsOptionWizard.tableWizard(this, \''.$button.'\', \'ctrl_'.$this->strId.'\',\''.$this->strId.'\'); return false;">'.$this->generateImage(substr($button, 1).'.gif', $GLOBALS['TL_LANG'][$this->strTable][$button][0], 'class="tl_tablewizard_img"').'</a> ';
			}

			$return .= '</td>';
		}

		$return .= '
      <td></td>
    </tr>';

		// Add rows
		for ($i=0; $i<count($this->varValue); $i++)
		{
			$return .= '
    <tr>';
			
			// Add cells
			for ($j=0; $j<count($this->varValue[$j]); $j++)
			{
				
				//for($k=0; $k<count($this->varValue[$j][$k]); $k++)
				//{
					$return .= '<td class="tcontainer">
					<select id="ctrl_' . $this->strId.'" name="' . $this->strId.'['.$i.']['.$j.']" class="tl_select"'.$this->getAttributes().' onchange="ProductsOptionWizard.getOptionValues(this,\'ctrl_'.$this->strId.'\',\''.$this->strId.'\');">';
							
								
					//return the attribute dropdown lists
					foreach($arrOptionAttributes as $k=>$v)
					{
						$return .= '<option value="' . $k . '"' . ($k==$this->varValue[$i][$j] ? ' selected' : '') . '>' . $v . '</option>';
	  				}
	  			
		  			$return .= '</select><br />';	
	
					$strValuesKey = $this->strId.'_values';
			
					if(sizeof($_SESSION['FORM_DATA'][$strValuesKey])>0)
					{
						
						$return .= '<div id="value_div['.$i.']['.$j.']">';
						
						$return .= '<select name="' . $this->strId.'_values['.$i.']['.$j.']" class="tl_select"'.$this->getAttributes().'>';
		
							//return the attribute value dropdown lists
							//foreach($arrOptionValues as $kk=>$vv)
							//{
								foreach($arrOptionValues[$this->varValue[$i][$j]] as $kk=>$vv)
								{
									
									$return .= '<option value="' . $vv['value'] . '"' . ($vv['value']==$_SESSION['FORM_DATA'][$strValuesKey][$i][$j] ? ' selected' : '') . '>' . $vv['label'] . '</option>';
			  					}
			  				//}
			  				
					
						
						$return .= '</select>';
						$return .= '</div>';
					
						/*	
						$return .= '
		      <td class="tcontainer"><textarea name="'.$this->strId.'['.$i.']['.$j.']" class="tl_textarea" rows="'.$this->intRows.'" cols="'.$this->intCols.'"'.$this->getAttributes().'>'.specialchars($this->varValue[$i][$j]).'</textarea></td>';
						*/
					}
				//}				
			}

			$return .= '
      <td style="white-space:nowrap;">';

			// Add row buttons
			foreach ($arrRowButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$button][0]).'" onclick="ProductsOptionWizard.tableWizard(this, \''.$button.'\', \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage(substr($button, 1).'.gif', $GLOBALS['TL_LANG'][$this->strTable][$button][0], 'class="tl_tablewizard_img"').'</a> ';
			}

			$return .= '</td>
    </tr>';
		}

		$return .= '
  </tbody>
  </table>
  </div>';

		if (!$this->Session->get('disable_cell_resizer'))
		{
			$return .= '
  <script type="text/javascript">
  <!--//--><![CDATA[//><!--
  ProductsOptionWizard.tableWizardResize();
  //--><!]]>
  </script>';
		}

		return $return;
	}
	
	/**
	 * Retrieve option attributes
	 * @param array
	 * @return array
	 */
	protected function getOptionAttributes()
	{
		$this->import('Database');
		
		$arrOptionAttributes = array();
		
		//Get attributes that are is_customer_defined 
		$objOptionAttributes = $this->Database->prepare("SELECT id AS value, name AS label FROM tl_product_attributes WHERE is_customer_defined=?")
											  ->execute(1);
	
		
		if($objOptionAttributes->numRows < 1)
		{
			return;			
		}
		
		$arrOptionAttributes['-'] = $GLOBALS['TL_LANG']['MSC']['selectItemPrompt'];
		
		$arrAttributes = $objOptionAttributes->fetchAllAssoc();
		
		foreach($arrAttributes as $attribute)
		{
			$arrOptionAttributes[$attribute['value']] = $attribute['label'];
		}
				
		return $arrOptionAttributes;
	}
	
	/**
	 * Retrieve option values for the product options wizard
	 * @param array
	 * @return array
	 */
	protected function getAllOptionValues($arrOptionAttributes)
	{
		
		
		foreach(array_keys($arrOptionAttributes) as $key)
		{
			if($key=="-")
			{
				continue;
			}
			
			$objAttributeValues = $this->Database->prepare("SELECT option_list FROM tl_product_attributes WHERE id=?")
												 ->limit(1)
												 ->execute($key);
			
			if($objAttributeValues->numRows < 1)
			{
				return array();
			}
			
			$arrAttributeValues = deserialize($objAttributeValues->option_list);
		
			$arrOptionValues[$key][] = array('-' => $GLOBALS['TL_LANG']['MSC']['selectItemPrompt']);

			foreach($arrAttributeValues as $option)
			{
				$arrOptionValues[$key][] = array
				(
					'value'		=> $option['value'],
					'label'		=> $option['label']
				);
			}

		}
				
		return $arrOptionValues;
	
	}
	
		
	/**
	 * @param string
	 * @return void
	 */
	public function executePostActions($strAction, DataContainer $dc)
	{
		
	    if ($strAction == 'addPOAttributeValues')
	    {
	    	$this->import('Database');
	    	
	    	$intId = $this->Input->post('aid');
	    	$intX = (integer)$this->Input->post('r');
	    	$intY = (integer)$this->Input->post('c');
	    	$strParentField = $this->Input->post('parent');
	    	
	    	$objAttributeValues = $this->Database->prepare("SELECT field_name, option_list FROM tl_product_attributes WHERE id=?")
	       										 ->limit(1)
	       										 ->execute($intId);
	       
	        if($objAttributeValues->numRows < 1)
	        {
	        	    
	        }else{
	        	
	        	$this->arrAttributeOptions = deserialize($objAttributeValues->option_list);
	       		unset($this->arrAttributeOptions[$intId]);	//remove the selected (current value from the list of attributes that can be used.
	       	}
			
			foreach($this->arrAttributeOptions as $option)	//This selection of values (option_list values) are stored as value/label combos
			{
				$arrFinalOptions[$option['value']] = $option['label'];
			}
			
	    	echo $this->generateAjax($dc, $strParentField, $intX, $intY, $arrFinalOptions); 
	    	
	    }
	    
	    if ($strAction == 'addAttributeList')
	    {
	    	$intId = $this->Input->post('aid');
	    	$intX = (integer)$this->Input->post('r');
	    	$intY = (integer)$this->Input->post('c');
	    	$strParentField = $this->Input->post('parent');
	    	
	    	$arrAttributes = $this->getOptionAttributes();
	    	
	    	if($intId!='-')
	    	{
	    		unset($arrAttributes[$intId]);	//remove the selected (current value from the list of attributes that can be used.
	    	}
	    	
	    	echo $this->generateAjax($dc, $strParentField, $intX, $intY, $arrAttributes, 'attributes');
	    }
	    
	    //return $this->arrAttributeOptions;
	}
	
	protected function generateAjax($objDc, $strId, $intX, $intY, $arrOptions, $strControlType = 'values')
	{
	
		$arrOptions;
				
		switch($strControlType)
		{
			case 'values':
				$strControlId = $strId . '_values[' . $intX . '][' . $intY . ']';
				$strOnChangeEvent = '';
				break;
			case 'attributes':
				$strControlId = $strId;
				$strOnChangeEvent = 'onchange="ProductsOptionWizard.getOptionValues(this,\'ctrl_'.$strId.'\',\''.$strId.'\');"';
				break;
			default:
				break;		
		}
		
				
				
		$return = '<select id="ctrl_'. $strControlId.'" name="' . $strControlId.'" class="tl_select"'.$this->getAttributes().$strOnChangeEvent.'>';
								
		//return the attribute dropdown lists
		foreach($arrOptions as $k=>$v)
		{
			$return .= '<option value="' . $k . '">' . $v . '</option>';
		}

		$return .= '</select>';
		
		return $return;
	}
	
	public function saveValues($varValue, DataContainer $dc)
	{
		
	
	}
	
	
	/**
	 * Generate a checkbox and return it as string	- REFERENCE ONLY
	 * @param array
	 * @param integer
	 * @param string
	 * @return string
	 */
	protected function generateCheckbox($arrOption, $i, $strButtons)
	{
		return sprintf('<span><input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s onfocus="AjaxRequest.getScrollOffset();" /> %s <label for="opt_%s">%s</label></span>',
						$this->strName . ($this->multiple ? '[]' : ''),
						$this->strId.'_'.$i,
						($this->multiple ? specialchars($arrOption['value']) : 1),
						((is_array($this->varValue) && in_array($arrOption['value'] , $this->varValue) || $this->varValue == $arrOption['value']) ? ' checked="checked"' : ''),
						$this->getAttributes(),
						$strButtons,
						$this->strId.'_'.$i,
						$arrOption['label']);
	}

}


