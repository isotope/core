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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class OptionDataWizard extends Widget
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
				$this->varValue = deserialize($varValue, true);
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			case 'maxlength':
				$this->arrAttributes[$strKey] = ($varValue > 0) ? $varValue : '';
				break;
				
			case 'options':
				$this->arrOptions = deserialize($GLOBALS['TL_DCA']['tl_product_data']['fields'][$this->strName]['attributes']['option_list']);
				
				if (!is_array($this->arrOptions))
				{
					$this->arrOptions = deserialize($varValue, true);
				}
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

				if (strlen($options[$key]['label']))
				{
					$this->mandatory = false;
				}
			}
		}

		$varInput = $this->validator($options);
		
		if (isset($varInput['blankOptionLabel']))
		{
			$varInput[''] = $varInput['blankOptionLabel'];
			unset($varInput['blankOptionLabel']);
		}

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
		// Begin table
		$return .= '<table cellspacing="0" cellpadding="0" class="tl_optionwizard" id="ctrl_'.$this->strId.'" summary="Field wizard">
  <thead>
    <tr>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['opValue'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['opLabel'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['opPrice'].'</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>';

		// Add fields
		for ($i=0; $i<count($this->arrOptions); $i++)
		{
			$strName = $this->strId.'['.($this->arrOptions[$i]['value'] ? $this->arrOptions[$i]['value'] : 'blankOptionLabel').']';
			
			$return .= '
    <tr>
      <td><input type="text" id="'.$this->strId.'_value_'.$i.'" class="tl_text_2" value="'.specialchars($this->arrOptions[$i]['value']).'" disabled="disabled" /></td>
      <td><input type="text" name="'.$strName.'[label]" id="'.$this->strId.'_label_'.$i.'" class="tl_text_2" value="'.specialchars((strlen($this->varValue[$this->arrOptions[$i]['value']]['label']) ? $this->varValue[$this->arrOptions[$i]['value']]['label'] : $this->arrOptions[$i]['label'])).'" /></td>
      <td><input type="text" name="'.$strName.'[price]" id="'.$this->strId.'_price_'.$i.'" class="tl_text_2" value="'.specialchars($this->varValue[$this->arrOptions[$i]['value']]['price']).'" /></td>
      <td><input type="checkbox" name="'.$strName.'[disable]" id="'.$this->strId.'_disable_'.$i.'" class="fw_checkbox" value="1"'.($this->varValue[$this->arrOptions[$i]['value']]['disable'] ? ' checked="checked"' : '').' /> <label for="'.$this->strId.'_disable_'.$i.'">'.$GLOBALS['TL_LANG'][$this->strTable]['opDisable'].'</label></td>';
			
			$return .= '
    </tr>';
		}

		return $return.'
  </tbody>
  </table>';
	}
}

