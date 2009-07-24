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
 * @copyright  Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */


/**
 * Class FormTextCollectionField
 *
 * Form field "text collection".
 * @copyright  Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Controller
 */
class FormTextCollectionField extends Widget
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
	protected $strTemplate = 'form_widget';


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
			/*
			case 'collectionsize':
				$this->arrConfiguration['collectionsize'] = $varValue;
				break;
			
			case 'prompt':
				$this->arrConfiguration['prompt'] = $varValue;
				break;
			*/	
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

		for($i=0; $i < $this->collectionsize; $i++)
		{
			$y = $i + 1;
			if($this->collectionsize > 1)
			{
						
				if($i!=0)
				{
					$return .= "<br />";
				}
			
			
				$return .= sprintf('<input type="text" name="%s[' . $i . ']" id="ctrl_%s" class="text%s" value="%s"%s />',
								$this->strName,
								$this->strId,
								(strlen($this->strClass) ? ' ' . $this->strClass : ''),
								specialchars($this->varValue),
								$this->getAttributes()) . $this->addSubmit();
			}else{
				$return .= sprintf('<input type="text" name="%s" id="ctrl_%s" class="text%s" value="%s"%s />',
								$this->strName,
								$this->strId,
								(strlen($this->strClass) ? ' ' . $this->strClass : ''),
								specialchars($this->varValue),
								$this->getAttributes()) . $this->addSubmit();
			}
		}
		
		return $return;
	}
}

?>