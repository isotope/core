<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class IsotopeEmail extends Controller
{

	/**
	 * The unterlying Contao Email object
	 * @var object
	 */
	protected $objEmail;
	
	/**
	 * Contain the simple tokens for this email
	 * @var array
	 */
	protected $arrSimpleTokens = array();

	/**
	 * The current language for the email
	 * @var string
	 */
	protected $strLanguage;
	
	/**
	 * Email template file
	 * @var string
	 */
	protected $strTemplate;
	
	/**
	 * Contao CSS file to include
	 * @var string
	 */
	protected $strCssFile = 'isotope';
	
	/**
	 * Collection PDF data
	 * @var mixed
	 */
	protected $varDocumentData;
	
	/**
	 * Collection PDF title
	 * @var string
	 */
	protected $strDocumentTitle;
	
	/**
	 * if attachments have been added (= reset $objEmail if language changes)
	 * @var bool
	 */
	protected $attachmentsDone = false;
	
	/**
	 * the id of the mail template 
	 * @var int
	 */
	protected $intId;

	/**
	 * Construct object
	 *
	 * @param	int
	 * @param	string
	 * @return	void
	 */
	public function __construct($intId, $strLanguage=null, $objCollection=null)
	{
		parent::__construct();
		$this->import('Database');
		
		// Verify collection object type
		if (!($objCollection instanceof IsotopeProductCollection))
		{
			$objCollection = null;
		}

		$this->intId = $intId;
		$this->initializeTemplate($strLanguage, $objCollection);
	}


	/**
	 * Set an object property
	 *
	 * @param	string
	 * @param	mixed
	 * @return	void
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'simpleTokens':
				$this->arrSimpleTokens = $varValue;
				break;
			
			case 'language':
				$strLanguage = substr($varValue, 0, 2);
				if ($strLanguage != $this->strLanguage)
				{
					$this->initializeTemplate($strLanguage);
				}
				break;
			
			case 'collection':
				if ($varValue instanceof IsotopeProductCollection)
				{
					$this->initializeTemplate($this->strLanguage, $objCollection);
				}
				break;
			
			default:
				$this->objEmail->__set($strKey, $varValue);
				break;
		}
	}

	/**
	 * Return an object property
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'language':
				return $this->strLanguage;
				break;
			
			default:
				return $this->objEmail->__get($strKey);
				break;
		}
	}
	
	
	/**
	 * Call parent Email object method
	 *
	 * @param	string
	 * @param	array
	 * @return	mixed
	 */
	public function __call($function, array $param_arr)
	{
		return call_user_func_array(array($this->objEmail, $function), $param_arr);
	}
	
	
	/**
	 * Send to give address with tokens
	 *
	 * @param	mixed
	 * @param	array|null
	 * @param	string|null
	 */
	public function send($varRecipients, $arrTokens=null, $strLanguage=null)
	{
		if ($strLanguage)
		{
			$this->language = $strLanguage;
		}
		
		if (is_array($arrTokens))
		{
			$this->simpleTokens = $arrTokens;
		}
		
		return $this->sendTo($varRecipients);
	}


	/**
	 * Set the data and send the email. 
	 * DON'T CALL THIS METHOD BEFORE YOU HAVE DONE ALL MODIFICATIONS ON THE MAIL TEMPLATE
	 */
	public function sendTo()
	{
		// Use current page language if none is set
		if (!$this->strLanguage)
		{
			$this->strLanguage = $GLOBALS['TL_LANGUAGE'];
		}
		
		// get the data for the active language
		$objLanguage = $this->Database->prepare("SELECT * FROM tl_iso_mail_content WHERE pid={$this->intId} AND (language='{$this->strLanguage}' OR fallback='1') ORDER BY fallback")
									  ->limit(1)
									  ->execute();

		if (!$objLanguage->numRows)
		{
			throw new Exception('No fallback language found for mail template ID '.$this->intId);
		}
		
		$this->strLanguage = $objLanguage->language;
		
		$arrData = $this->arrSimpleTokens;
		$arrPlainData = array_map('strip_tags', $this->arrSimpleTokens);
		
		$this->objEmail->subject = $this->parseSimpleTokens($this->replaceInsertTags($objLanguage->subject), $arrPlainData);
		$this->objEmail->text = $this->parseSimpleTokens($this->replaceInsertTags($objLanguage->text), $arrPlainData);

		// html
		if (!$objLanguage->textOnly && $objLanguage->html != '')
		{
			$arrData['head_css'] = '';

			// Add style sheet
			if (is_file(TL_ROOT . '/system/scripts/' . $this->strCssFile . '.css'))
			{
				$buffer = file_get_contents(TL_ROOT . '/system/scripts/' . $this->strCssFile . '.css');
				$buffer = preg_replace('@/\*\*.*\*/@Us', '', $buffer);
	
				$css  = '<style type="text/css">' . "\n";
				$css .= trim($buffer) . "\n";
				$css .= '</style>' . "\n";
				$arrData['head_css'] = $css;
			}
			
			$objTemplate = new FrontendTemplate($this->strTemplate);
			$objTemplate->body = $objLanguage->html;
			$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
			$objTemplate->css = '##head_css##';

			// Prevent parseSimpleTokens from stripping important HTML tags
			$GLOBALS['TL_CONFIG']['allowedTags'] .= '<doctype><html><head><meta><style><body>';
			$strHtml = str_replace('<!DOCTYPE', '<DOCTYPE', $objTemplate->parse());
			$strHtml = $this->parseSimpleTokens($this->replaceInsertTags($strHtml), $arrData);
			$strHtml = str_replace('<DOCTYPE', '<!DOCTYPE', $strHtml);

			// Parse template
			$this->objEmail->html = $strHtml;
		}
		
		if (!$this->attachmentsDone)
		{
			foreach (deserialize($objLanguage->attachments, true) as $file)
			{
				if ($file != '' && is_file(TL_ROOT . '/' . $file))
				{
					$this->objEmail->attachFile(TL_ROOT . '/' . $file);
				}
			}
			
			// @todo the PDF name could contain user specific information if sent to multiple recipients
			if ($this->strDocumentTitle != '')
			{
				$strTitle = $this->parseSimpleTokens($this->replaceInsertTags($this->strDocumentTitle), $arrPlainData);
				$this->objEmail->attachFileFromString($this->varDocumentData, $strTitle.'.pdf', 'application/pdf');
			}
			
			$this->attachmentsDone = true;
		}

		return $this->objEmail->sendTo(func_get_args());
	}
	
	
	/**
	 * Initialize from template and reset attachments if language changes
	 *
	 * @param	string
	 * @return	void
	 * @throws	Exception
	 */
	protected function initializeTemplate($strLanguage, $objCollection)
	{
		$this->objEmail = new Email();
		$this->attachmentsDone = false;
		
		$objTemplate = $this->Database->execute("SELECT * FROM tl_iso_mail WHERE id=" . $this->intId);

		if ($objTemplate->numRows < 1)
		{
			throw new Exception('No mail template with ID "' . $this->intId . '" found.');
		}
		
		$this->strLanguage = $strLanguage;

		// set the options
		$this->objEmail->imageDir = TL_ROOT . '/';
		$this->objEmail->fromName = $objTemplate->senderName ? $objTemplate->senderName : $GLOBALS['TL_ADMIN_NAME'];
		$this->objEmail->from = $objTemplate->sender ? $objTemplate->sender : $GLOBALS['TL_ADMIN_EMAIL'];
		$this->objEmail->priority = $objTemplate->priority;

		// recipient_cc
		$arrCc = trimsplit(',', $objTemplate->cc);
		foreach ((array)$arrCC as $email)
		{
			if ($email == '' || !$this->isValidEmailAddress($email))
				continue;

			$this->objEmail->sendCc($email);
		}
		
		// recipient_bcc
		$arrBcc = trimsplit(',', $objTemplate->bcc);
		foreach ((array)$arrBcc as $email)
		{
			if ($email == '' || !$this->isValidEmailAddress($email))
				continue;

			$this->objEmail->sendBcc($email);
		}
		
		$this->strTemplate = $objTemplate->template ? $objTemplate->template : 'mail_default';
		
		if ($objTemplate->attachDocument && $objCollection instanceof IsotopeProductCollection)
		{
			$objPdf = $objCollection->generatePDF(($objTemplate->documentTemplate ? $objTemplate->documentTemplate : null), null, false);
			$objPdf->lastPage();
			
			$this->varDocumentData = $objPdf->Output('collection.pdf', 'S');
			$this->strDocumentTitle = $objTemplate->documentTitle;
		}
	}
}

