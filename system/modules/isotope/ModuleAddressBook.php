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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ModuleAddressBook
 *
 * Based on the Front end module "personal data" by Leo Feyer
 */
class ModuleAddressBook extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_address_book_list';

	protected $strEditTemplate = 'iso_address_book_edit';
	
	
	protected $arrAddressFields;
	
	
	
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
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->import('Isotope');

		$this->arrAddressFields = deserialize($this->Isotope->Store->billing_fields);

		// Return if there are not editable fields or if there is not logged in user
		if (!FE_USER_LOGGED_IN || !is_array($this->arrAddressFields) || !count($this->arrAddressFields))
		{
			return '';
		}
		
		$this->arrAddressFields[] = 'isDefaultBilling';
		$this->arrAddressFields[] = 'isDefaultShipping';
		
		$this->import('FrontendUser', 'User');

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
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
					$this->editAddress($this->Input->get('id'));
				}
				break;
			case 'create':
				$this->editAddress();
				break;
			case 'delete':
				if($this->Input->get('id'))
				{
					$this->deleteAddress($this->Input->get('id'));
				}
				break;
			default:
				$this->showAllAddresses();
				break;
		
		}	
		
	}

	protected function showAllAddresses()
	{
		global $objPage;
		
		$arrPage = array('id'=>$objPage->id, 'alias'=>$objPage->alias);
		

		$objAddresses = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=?")
									   ->execute($this->User->id);
		
		if($objAddresses->numRows < 1)
		{
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'];
		}
		else
		{
			while( $objAddresses->next() )
			{
				$strEditUrl = ampersand($this->generateFrontendUrl($arrPage, '/ab_action/edit/id/' . $objAddresses->id));
				$strDeleteUrl = ampersand($this->generateFrontendUrl($arrPage, '/ab_action/delete/id/' . $objAddresses->id));
								
				$arrAddresses[] = array
				(
					'id'			=> $objAddresses->id,
					'text'			=> $this->Isotope->generateAddressString($objAddresses->row()),
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
		$this->Template->addNewAddress = ampersand($this->generateFrontendUrl($arrPage, '/ab_action/create'));
	}
	
	
	protected function editAddress($intAddressId=0)
	{
		global $objPage;
		
		$this->Template = new FrontendTemplate($this->strEditTemplate);

		$doNotSubmit = false;
		$hasUpload = false;
		$this->Template->fields = '';

		if ($intAddressId==0)
		{
			$arrRawAddressFields = $this->Database->listFields('tl_address_book');

			if ($this->Input->post('FORM_SUBMIT') != 'tl_address_book_' . $this->id)
			{
				
				foreach( $arrRawAddressFields as $field )
				{
					$arrAddressFields[$field['name']] = NULL;
				}
			}
			else
			{
				foreach($arrRawAddressFields as $field)
				{
					$arrAddressFields[$field['name']] = $this->Input->post($field['name']);
				}
			}		
		}
		else
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=? AND pid=?")
									 ->limit(1)
									 ->execute($intAddressId, $this->User->id);
		
			if ($intAddressId!=0 && $objAddress->numRows < 1)
			{
				return $GLOBALS['TL_LANG']['ERR']['addressDoesNotExist'];
			}
					
			$arrAddressFields = $objAddress->fetchAssoc();
		}
			
		// Build form
		foreach ($this->arrAddressFields as $i=>$field)
		{
			$arrData = &$GLOBALS['TL_DCA']['tl_address_book']['fields'][$field];

			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass) || !$arrData['eval']['isoEditable'])
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
					$varSave = is_array($varValue) ? serialize($varValue) : $varValue;

					if($intAddressId==0)
					{
						$arrSet = array
						(
							'pid'			=> $this->User->id,
							'tstamp'		=> time(),
						);

						$arrValues[$field] = $varSave;
					}
					else
					{
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
			
			// Call onsubmit_callback
			if (is_array($GLOBALS['TL_DCA']['tl_address_book']['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA']['tl_address_book']['config']['onsubmit_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]();
					}
				}
			}
			
			$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));
		}

		$this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_address_book']['loginDetails'];
		$this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_address_book']['addressDetails'];
		$this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_address_book']['contactDetails'];
		$this->Template->AddressBook = $GLOBALS['TL_LANG']['tl_address_book']['AddressBook'];

		$this->Template->formId = 'tl_address_book_' . $this->id;
		$this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
		$this->Template->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->rowLast = 'row_' . count($this->editable) . ((($i % 2) == 0) ? ' odd' : ' even');
		
		$this->Template->backBT = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->backLink = $this->generateFrontendUrl(array('id'=>$objPage->id, 'alias'=>$objPage->alias));
	}
	
	protected function deleteAddress($intAddressId)
	{
		$this->Database->prepare("DELETE FROM tl_address_book WHERE id=? AND pid=?")
					   ->execute($intAddressId, $this->User->id);
	
		$strReturnUrl = $_SESSION['FE_DATA']['referer']['current']; //$arrUrlBits[0] . '.html';		
											
		$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));

	}
}

