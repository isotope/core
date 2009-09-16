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
 * @package    ProductsOptionWizard
 * @license    LGPL
 * @filesource
 */


/**
 * Class ProductOptionsWizard
 *
 * Provide methods to handle product options.  Based upon the TYPOlight option wizard by Leo Feyer
 * @copyright  Fred Bliss / Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Controller
 */
class ProductOptionsWizard extends Widget
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

			case 'maxlength':
				$this->arrAttributes[$strKey] = ($varValue > 0) ? $varValue : '';
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			case 'readonly':
				$this->arrAttributes['readonly'] = 'readonly';
				$this->blnSubmitInput = false;
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
	protected function validator($varInput)
	{
		if (is_array($varInput))
		{
			return parent::validator($varInput);
		}

		return parent::validator(trim($varInput));
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{

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

	public function getSubProductsInterface($arrValues)
	{
		//Load existing subproduct values
		$objSubProducts = $this->Database->prepare("SELECT * FROM tl_product_data WHERE pid=?")
		
		if($objSubProducts->numRows < 1)
		{
			return '<em>' . $GLOBALS['TL_LANG']['ERR']['noSubProducts'] . '</em>';
		}
		
		//Add new values
		foreach($arrValues as $value)
		{
		
		
		}
	
		return join('\n', $arrHtml);

	}
*/
}