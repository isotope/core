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
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */


/**
 * Class ModuleAddressBook
 *
 * Based on the Front end module "personal data" by Leo Feyer
 * @copyright  Fred Bliss / Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Controller
 */
class ModuleAddressBook extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_address_book_list';

	protected $strEditTemplate = 'iso_address_book_edit';
	
	/**
	 * User Id
	 * @var integer
	 */
	protected $intUserId;
	
	
	
	/**
	 * Return a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ADDRESS BOOK ###';

			return $objTemplate->parse();
		}

		$this->isoEditable = deserialize($this->isoEditable);

		// Return if there are not editable fields or if there is no logged in user
		if (!FE_USER_LOGGED_ID)//!is_array($this->isoEditable) || count($this->isoEditable) < 1 || !FE_USER_LOGGED_IN)
		{
			return '';
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		global $objPage;
		
		$this->import('FrontendUser', 'User');

		$this->intUserId = $this->User->id;
		
		$GLOBALS['TL_LANGUAGE'] = $objPage->language;

		$this->loadLanguageFile('tl_address_book');
		$this->loadDataContainer('tl_address_book');

		// Call onload_callback (e.g. to check permissions)
		if (is_array($GLOBALS['TL_DCA']['tl_address_book']['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_address_book']['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]();
				}
			}
		}
			
			
		
		// Set template
		if (strlen($this->addressBookTemplate))
		{
			$this->Template = new FrontendTemplate($this->addressBookTemplate);
		}

		
		
		$strAction = $this->Input->get('ab_action');
		
		switch($strAction)
		{
			case 'edit':
				if($this->Input->get('id'))
				{
					$this->editAddress($this->intUserId, $this->Input->get('id'));
				}
				break;
			case 'create':
				$this->editAddress($this->intUserId);
				break;
			case 'delete':
				if($this->Input->get('id'))
				{
					$this->deleteAddress($this->intUserId, $this->Input->get('id'));
				}
				break;
			default:
				$this->showAllAddresses($this->intUserId);
				break;
		
		}	
		
	}

	protected function showAllAddresses($intUserId)
	{
		global $objPage;
						
		$blnShowAddressList = true;

		//Get page info for address book functionality urls
		$objPageData = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
					  			      ->limit(1)
						  			  ->execute($objPage->id);

			
		if($objPageData->numRows > 0)
		{
			$arrPage = $objPageData->fetchAssoc();
		}else{
			return '';
		}	
		//End page info
		
			
		$strCreateNewUrl = ampersand($this->generateFrontendUrl($arrPage, '/ab_action/create'));
		

		$objAddresses = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=?")
									   ->execute($intUserId);
		
		if($objAddresses->numRows < 1)
		{
			
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'];
			$blnShowAddressList = false;
		}else{
		
			$arrAddressData = $objAddresses->fetchAllAssoc();
						
			foreach($arrAddressData as $row)
			{
				if(strlen($row['street_2'])<1)
				{
					unset($row['street_2']);
				}else{
					$row['street'] .= '<br />' . $row['street_2'];
				}
				
				if(strlen($row['street_3'])<1)
				{
					unset($row['street_3']);
				}else{
					$row['street'] .= '<br />' . $row['street_3'];
				}

				$strEditUrl = ampersand($this->generateFrontendUrl($arrPage, '/ab_action/edit/id/' . $row['id']));
				$strDeleteUrl = ampersand($this->generateFrontendUrl($arrPage, '/ab_action/delete/id/' . $row['id']));

				$arrAddressListingFields = array
				(
					'name'			=> $row['firstname'] . ' ' . $row['lastname'],
					'address'		=> $row['street'],
					'city_state'	=> $row['city'] . ', ' . $row['state'] . ' ' . $row['postal'],
					'country'		=> $GLOBALS['TL_LANG']['CNT'][$row['country']]
				);
								
				$arrAddresses[] = array
				(
					'id'			=> $row['id'],
					'text'			=> implode("<br />", $arrAddressListingFields),
					'edit_url'		=> $strEditUrl,
					'delete_url'	=> $strDeleteUrl
				);
						
			}
		
		}
		
		
		$this->Template->addressLabel = $GLOBALS['TL_LANG']['addressBookLabel'];
		$this->Template->addNewAddressLabel= $GLOBALS['TL_LANG']['createNewAddressLabel'];
		$this->Template->editAddressLabel = $GLOBALS['TL_LANG']['editAddressLabel'];
		$this->Template->deleteAddressLabel = $GLOBALS['TL_LANG']['deleteAddressLabel'];
		$this->Template->addresses = $arrAddresses;
		$this->Template->isotopeBase = $GLOBALS['TL_CONFIG']['isotope_upload_path'];
		$this->Template->addNewAddress = $strCreateNewUrl;
		
	}
	
	
	protected function editAddress($intUserId, $intAddressId=0)
	{
		$this->Template = new FrontendTemplate($this->strEditTemplate);

		$arrFields = array();
		$doNotSubmit = false;
		$hasUpload = false;
		$this->Template->fields = '';

		if($intAddressId==0)
		{
			$arrRawAddressFields = $this->Database->listFields('tl_address_book');

			if($this->Input->post('FORM_SUBMIT') != 'tl_address_book_' . $this->id)
			{
				
				foreach($arrRawAddressFields as $field)
				{
					$arrAddressFields[$field['name']] = NULL;
				}
			}else{
				foreach($arrRawAddressFields as $field)
				{
					$arrAddressFields[$field['name']] = $this->Input->post($field['name']);
				}
				
			}		
		}else{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=? AND pid=?")
									 ->limit(1)
									 ->execute($intAddressId, $intUserId);
		
			if($intAddressId!=0 && $objAddress->numRows < 1)
			{
				return $GLOBALS['TL_LANG']['ERR']['addressDoesNotExist'];
			}
					
			$arrAddressFields = $objAddress->fetchAssoc();
			
			
		}	
		
		
		
		$this->loadLanguageFile('tl_address_book');
		$this->loadDataContainer('tl_address_book');
		
		foreach($arrAddressFields as $k=>$v)
		{
			if($GLOBALS['TL_DCA']['tl_address_book']['fields'][$k]['eval']['isoEditable'])
			{
				$arrEditableFields[] = $k;
			}
		}
			
		// Build form
		foreach ($arrEditableFields as $i=>$field)
		{
			$arrData = &$GLOBALS['TL_DCA']['tl_address_book']['fields'][$field];
			$strGroup = $arrData['eval']['feGroup'];

			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass) || !$arrData['eval']['feEditable'])
			{
				continue;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, $field, $arrAddressFields[$field]));

			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == 'tl_address_book_' . $this->id)
			{
				$objWidget->validate();
				$varValue = $objWidget->value;
				$strUsername = strlen($this->Input->post('username')) ? $this->Input->post('username') : $objUser->username;

				// Check whether the password matches the username
				if ($objWidget instanceof FormPassword && $varValue == sha1($strUsername))
				{
					$objWidget->addError($GLOBALS['TL_LANG']['ERR']['passwordName']);
				}

				// Convert date formats into timestamps
				if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Make sure that unique fields are unique
				if ($arrData['eval']['unique'])
				{
					$objUnique = $this->Database->prepare("SELECT * FROM tl_address_book WHERE " . $field . "=? AND id!=?")
												->limit(1)
												->execute($varValue, $intAddressId);

					if ($objUnique->numRows)
					{
						$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field)));
					}
				}

				// Do not submit if there are errors
				if ($objWidget->hasErrors())
				{
					$doNotSubmit = true;
				}

				// Store current value
				elseif ($objWidget->submitInput())
				{
					// Save callback
					if (is_array($arrData['save_callback']))
					{
						foreach ($arrData['save_callback'] as $callback)
						{
							$this->import($callback[0]);
							$varValue = $this->$callback[0]->$callback[1]($varValue, $this->User);
						}
					}

					// Set new value
					$arrAddressFields[$field] = $varValue;
					$_SESSION['FORM_DATA'][$field] = $varValue;
					$varSave = is_array($varValue) ? serialize($varValue) : $varValue;

					if($intAddressId==0)
					{
						$arrSet = array
						(
							'pid'			=> $intUserId,
							'tstamp'		=> time()
						);

						$arrValues[$field] = $varSave;
					}else{
						// Save field
						$this->Database->prepare("UPDATE tl_address_book SET " . $field . "=? WHERE id=?")
									   ->execute($varSave, $intAddressId);

					}
				}
			}

			if ($objWidget instanceof uploadable)
			{
				$hasUpload = true;
			}

			$temp = $objWidget->parse();

			$this->Template->fields .= $temp;
			$arrFields[$strGroup][$field] .= $temp;
		}

		if($intAddressId==0 && $this->Input->post('FORM_SUBMIT') == 'tl_address_book_' . $this->id)
		{
			foreach($arrValues as $k=>$v)
			{
				$arrSet[$k] = $v;
				unset($arrSet['id']);	//For some reason this is producing an invalid increment so we're going to just unset it altogether.					
			}
		
			$this->Database->prepare("INSERT INTO tl_address_book %s")
							->set($arrSet)
							->execute();

			$strReturnUrl = $_SESSION['FE_DATA']['referer']['last']; //$arrUrlBits[0] . '.html';		
											
			$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));
		}
		
		// Redirect or reload if there was no error
		if ($this->Input->post('FORM_SUBMIT') == 'tl_address_book_' . $this->id && !$doNotSubmit)
		{
			$strReturnUrl = $_SESSION['FE_DATA']['referer']['current']; //$arrUrlBits[0] . '.html';		
											
			$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));
		
		}

		$this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_address_book']['loginDetails'];
		$this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_address_book']['addressDetails'];
		$this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_address_book']['contactDetails'];
		$this->Template->AddressBook = $GLOBALS['TL_LANG']['tl_address_book']['AddressBook'];

		// Add groups
		foreach ($arrFields as $k=>$v)
		{
			$this->Template->$k = $v;
		}

		$this->Template->formId = 'tl_address_book_' . $this->id;
		$this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
		$this->Template->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->rowLast = 'row_' . count($this->editable) . ((($i % 2) == 0) ? ' odd' : ' even');

		// HOOK: add memberlist fields
		if (in_array('memberlist', $this->Config->getActiveModules()))
		{
			$this->Template->profile = $arrFields['profile'];
			$this->Template->profileDetails = $GLOBALS['TL_LANG']['tl_address_book']['profileDetails'];
		}

		// HOOK: add newsletter fields
		if (in_array('newsletter', $this->Config->getActiveModules()))
		{
			$this->Template->newsletter = $arrFields['newsletter'];
			$this->Template->newsletterDetails = $GLOBALS['TL_LANG']['tl_address_book']['newsletterDetails'];
		}

		// HOOK: add helpdesk fields
		if (in_array('helpdesk', $this->Config->getActiveModules()))
		{
			$this->Template->helpdesk = $arrFields['helpdesk'];
			$this->Template->helpdeskDetails = $GLOBALS['TL_LANG']['tl_address_book']['helpdeskDetails'];
		}
	}
	
	protected function deleteAddress($intUserId, $intAddressId)
	{
		if($this->addressExists($intUserId, $intAddressId))
		{
			$this->Database->prepare("DELETE FROM tl_address_book WHERE id=? AND pid=?")
							->execute($intAddressId, $intUserId);
							
			//Delete it from the database
			$strReturnUrl = $_SESSION['FE_DATA']['referer']['current']; //$arrUrlBits[0] . '.html';		
											
			$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));

		}
	
		$strReturnUrl = $_SESSION['FE_DATA']['referer']['current']; //$arrUrlBits[0] . '.html';		
											
		$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));

	}
	
	
	protected function addressExists($intUserId, $intAddressId)
	{
		$objAddressExists = $this->Database->prepare("SELECT COUNT(*) as count FROM tl_address_book WHERE id=? AND pid=?")
										   ->limit(1)
										   ->execute($intAddressId, $intUserId);
		
		if($objAddressExists->count < 1 || $objAddressExists->numRows < 1)
		{
			return false;
		}
		
		return true;
	}
}

?>