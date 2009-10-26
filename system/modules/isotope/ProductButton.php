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
 * Class ProductButton
 *
 * Provide methods to handle Isotope Product Buttons.
 */
abstract class ProductButton extends Controller
{
	
	/**
	 * Button Type
	 * @var string
	 */
	protected $strButtonType;

	/**
	 * Button Form Name
	 * @var string
	 */
	protected $strButtonFormName;

	/**
	 * Button Label
	 * @var string
	 */
	protected $strButtonLabel;

	/**
	 * Button Fallback Type - submit or image (submit)
	 * @var string
	 */
	protected $strButtonFallbackType;
	
	/**
	 * Button ID
	 * @var string
	 */
	protected $strButtonID;
	
	/**
	 * Button Click Event (for AJAX functionality)
	 * @var string
	 */
	protected $strButtonClickEvent;
	
	/**
	 * Button Image (file path)
	 * @var string
	 */
	protected $strButtonImage;
	
	/**
	 * Button Width
	 * @var integer
	 */
	protected $intButtonWidth;
	
	/**
	 * Button Height
	 * @var integer
	 */
	protected $intButtonHeight;

	/**
	 * Button Tab Index
	 * @var integer
	 */
	protected $intButtonTabIndex;

	/**
	 * Button Uses Image (true or false)
	 * @var boolean;
	 */
	protected $blnButtonUsesImage = false;
	
	/**
	 * Value
	 * @var mixed
	 */
	protected $varValue;

	/**
	 * CSS class
	 * @var string
	 */
	protected $strClass;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate;

	/**
	 * Attributes
	 * @var array
	 */
	protected $arrAttributes = array();


	/**
	 * Initialize the object
	 * @param array
	 */
	public function __construct($arrAttributes=false)
	{
		parent::__construct();
		$this->addAttributes($arrAttributes);
	}


	/**
	 * Set a parameter
	 * @param string
	 * @param mixed
	 * @throws Exception
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'buttonType':
				$this->strButtonType = $varValue;
			case 'buttonFormName':
				$this->strButtonFormName = $varValue;
				break;

			case 'buttonID':
				$this->strButtonId = $varValue;
				break;

			case 'buttonLabel':
				$this->strButtonLabel = $varValue;
				break;
				
			case 'buttonID':
				$this->strButtonID = $varValue;
				break;
			
			case 'buttonClickEvent':
				$this->strButtonClickEvent = $varValue;
				break;
				
			case 'buttonFallbackType':
				$this->strButtonFallbackType = $varValue;
				break;
			
			case 'buttonImage':
				$this->strButtonImage = $varValue;
				break;
			
			case 'buttonWidth':
				$this->intButtonWidth = $varValue;
				break;
				
			case 'buttonHeight':
				$this->intButtonHeight = $varValue;
				break;
			
			case 'buttonTabIndex':
				$this->intButtonTabIndex = $varValue;
				break;

			case 'value':
				$this->varValue = $varValue;
				break;

			case 'buttonUsesImage':
				$this->blnButtonUsesImage = $varValue;
				break;

			case 'template':
				$this->strTemplate = $varValue;
				break;

			case 'alt':
			case 'style':
			case 'onclick':
			case 'onchange':
			case 'accesskey':
			case 'disabled':
				$this->arrAttributes[$strKey] = $varValue;
				break;

			case 'mandatory':
				$this->arrConfiguration[$strKey] = false;
				break;

			case 'nospace':
			case 'allowHtml':
			case 'addSubmit':
			case 'storeFile':
			case 'useHomeDir':
			case 'storeValues':
			case 'trailingSlash':
			case 'spaceToUnderscore':
				$this->arrConfiguration[$strKey] = $varValue ? true : false;
				break;

			case 'required':
				if ($varValue)
				{
					$this->strClass = trim($this->strClass . ' mandatory');
				}
				// Do not add a break; statement here

			default:
				$this->arrConfiguration[$strKey] = $varValue;
				break;
		}
	}
}

