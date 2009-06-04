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
 * @copyright  Fred Bliss / Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Class ProductOptionWizard
 *
 * Provide methods to handle product options.  Based upon the TYPOlight option wizard by Leo Feyer
 * @copyright  Fred Bliss / Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Controller
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
		if (is_array($options))
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
		}

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
		$arrButtons = array('copy', 'up', 'down', 'delete');
		$strCommand = 'cmd_' . $this->strField;

		$arrAttributes = $this->getAttributes($this->strTable);
		
	//	$arrOptionValues = $this->getAllOptionValues($this->Input->get());
				
		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			$this->import('Database');

			switch ($this->Input->get($strCommand))
			{
				case 'copy':
					array_insert($this->varValue, $this->Input->get('cid'), array($this->varValue[$this->Input->get('cid')]));
					break;

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

			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);

			$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
		}

		// Make sure there is at least an empty array
		if (!is_array($this->varValue) || !$this->varValue[0])
		{
			$this->varValue = array(array(''));
		}

		// Begin table
		$return .= '<table cellspacing="0" cellpadding="0" class="tl_ProductOptionWizard" id="ctrl_'.$this->strId.'" summary="Field wizard">
  <thead>
    <tr>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['opAttribute'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['opValueSets'].'</th>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>';

		// Add fields
		for ($i=0; $i<count($this->varValue); $i++)
		{
			$strCurrOptionName = $this->strId.'['.$i.'][value]';
			
			$varCurrOptionValue = $this->Input->post($strCurrOptionName);
			
			$return .= '
    <tr>
      <td><select name="'.$this->strId.'['.$i.'][value]" id="'.$this->strId.'_attribute_'.$i.'" class="tl_select_2" onchange="" value="'.$this->varValue[$i]['value'].'">';
      foreach($arrAttributes as $attribute)
      {
      	$return .= '<option value="' . $attribute['value'] . '"' . ($varCurrOptionValue==$this->varValue[$i]['value'] ? ' selected' : '') . '>' . $this->varValue[$i]['label'] . '</option>';
      }
      
      $return .= '</select>
      </td>
      <td>
      &nbsp;
      </td>';
      
      /*
      foreach($arrCurrentAttributeValues as $value)
      {
      	'<input type="checkbox" name="'.$this->strId.'['.$i.'][label]" id="'.$this->strId.'_label_'.$i.'" class="tl_text_2" value="'.specialchars($this->varValue[$i]['label']).'" />';
      }
      
      $return .= '</td>';
      */
      $return .= '
      <td><input type="checkbox" name="'.$this->strId.'['.$i.'][default]" id="'.$this->strId.'_default_'.$i.'" class="fw_checkbox" value="1"'.($this->varValue[$i]['default'] ? ' checked="checked"' : '').' /> <label for="'.$this->strId.'_default_'.$i.'">'.$GLOBALS['TL_LANG'][$this->strTable]['opDefault'].'</label></td>
      <td><input type="checkbox" name="'.$this->strId.'['.$i.'][group]" id="'.$this->strId.'_group_'.$i.'" class="fw_checkbox" value="1"'.($this->varValue[$i]['group'] ? ' checked="checked"' : '').' /> <label for="'.$this->strId.'_group_'.$i.'">'.$GLOBALS['TL_LANG'][$this->strTable]['opGroup'].'</label></td>';
			
			// Add row buttons
			$return .= '
      <td style="white-space:nowrap; padding-left:3px;">';

			foreach ($arrButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$button][0]).'" onclick="Backend.optionsWizard(this, \''.$button.'\', \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable][$button][0]).'</a> ';
			}

			$return .= '</td>
    </tr>';
		}

		return $return.'
  </tbody>
  </table>';
	}
	
	
	protected function getAttributes($strTable)
	{
		//Get attributes that are is_customer_defined 
		$objOptionAttributes = $this->Database->prepare("SELECT id, field_name FROM tl_product_attributes WHERE is_customer_defined=?")
											  ->execute(1);
		
		if($objOptionAttributes->numRows < 1)
		{
			return array();			
		}
		
		$arrAttributes = $objOptionAttributes->fetchAllAssoc();
								
		return $arrAttributes;
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
		return sprintf('<span><input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s onfocus="Backend.getScrollOffset();" /> %s <label for="opt_%s">%s</label></span>',
						$this->strName . ($this->multiple ? '[]' : ''),
						$this->strId.'_'.$i,
						($this->multiple ? specialchars($arrOption['value']) : 1),
						((is_array($this->varValue) && in_array($arrOption['value'] , $this->varValue) || $this->varValue == $arrOption['value']) ? ' checked="checked"' : ''),
						$this->getAttributes(),
						$strButtons,
						$this->strId.'_'.$i,
						$arrOption['label']);
	}
	
	/**
	 * @param string
	 * @return void
	 */
	public function executePreActions($strAction)
	{
	    if ($strAction == 'addPOAttribute')
	    {
	    	$intAttributeId = $this->Input->get('attribute_id');
	    	
	    	$objAttributeValues = $this->Database->prepare("SELECT options FROM tl_product_attributes WHERE id=?")
	       										->execute($intAttributeId);
	       
	        if($objAttributeValues->numRows < 1)
	        {
	        	return array();	        
	        }
	        
	        $arrOptions = deserialize($objAttributeValues->options);
	        
	        return $arrOptions;
	    }
	}
}


?>