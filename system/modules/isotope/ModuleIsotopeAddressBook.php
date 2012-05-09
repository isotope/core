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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ModuleIsotopeAddressBook
 * Front end module Isotope "address book".
 */
class ModuleIsotopeAddressBook extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_addressbook';

	/**
	 * Disable caching of the frontend page if this module is in use
	 * @var bool
	 */
	protected $blnDisableCache = true;

	/**
	 * Editable fields
	 * @var array
	 */
	protected $arrFields;


	/**
	 * Return a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ADDRESS BOOK ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		if (FE_USER_LOGGED_IN !== true)
		{
			return '';
		}

		$this->arrFields = array_unique(array_merge(deserialize($this->Isotope->Config->billing_fields_raw, true), deserialize($this->Isotope->Config->shipping_fields_raw, true)));

		// Return if there are not editable fields
		if (!count($this->arrFields) || (count($this->arrFields) == 1 && $this->arrFields[0] == ''))
		{
			return '';
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 * @return void
	 */
	protected function compile()
	{
		$this->loadLanguageFile('tl_iso_addresses');
		$this->loadDataContainer('tl_iso_addresses');

		// Call onload_callback (e.g. to check permissions)
		if (is_array($GLOBALS['TL_DCA']['tl_iso_addresses']['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_iso_addresses']['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]();
				}
			}
		}

		// Do not add a break statement. If ID is not available, it will show all addresses.
		switch ($this->Input->get('act'))
		{
			case 'create':
				return $this->edit();

			case 'edit':
				if (strlen($this->Input->get('address')))
				{
					return $this->edit($this->Input->get('address'));
				}

			case 'delete':
				if (strlen($this->Input->get('address')))
				{
					return $this->delete($this->Input->get('address'));
				}

			default:
				$this->show();
				break;
		}
	}


	/**
	 * List all addresses for the current frontend user
	 * @return void
	 */
	protected function show()
	{
		global $objPage;
		$arrAddresses = array();
		$strUrl = $this->generateFrontendUrl($objPage->row()) . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?');
		$objAddress = $this->Database->execute("SELECT * FROM tl_iso_addresses WHERE pid={$this->User->id} AND store_id={$this->Isotope->Config->store_id}");

		while ($objAddress->next())
		{
			$arrAddresses[] = array_merge($objAddress->row(), array
			(
				'id'				=> $objAddress->id,
				'class'				=> (($objAddress->isDefaultBilling ? 'default_billing' : '') . ($objAddress->isDefaultShipping ? ' default_shipping' : '')),
				'text'				=> $this->Isotope->generateAddressString($objAddress->row()),
				'edit_url'			=> ampersand($strUrl . 'act=edit&address=' . $objAddress->id),
				'delete_url'		=> ampersand($strUrl . 'act=delete&address=' . $objAddress->id),
				'default_billing'	=> ($objAddress->isDefaultBilling ? true : false),
				'default_shipping'	=> ($objAddress->isDefaultShipping ? true : false),
			));
		}

		if (empty($arrAddresses))
		{
			$this->Template->mtype = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'];
		}

		$this->Template->addNewAddressLabel= $GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'];
		$this->Template->editAddressLabel = $GLOBALS['TL_LANG']['MSC']['editAddressLabel'];
		$this->Template->deleteAddressLabel = $GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'];
		$this->Template->deleteAddressConfirm = specialchars($GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm']);
		$this->Template->addresses = IsotopeFrontend::generateRowClass($arrAddresses, '', 'class', 0, ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);;
		$this->Template->addNewAddress = ampersand($strUrl . 'act=create');
	}


	/**
	 * Edit an address record. Based on the PersonalData core module
	 * @param integer
	 * @return void
	 */
	protected function edit($intAddressId=0)
	{
		$this->loadLanguageFile('tl_member');

		if (!strlen($this->memberTpl))
		{
			$this->memberTpl = 'member_default';
		}

		$this->Template = new IsotopeTemplate($this->memberTpl);
		$this->Template->fields = '';
		$this->Template->tableless = $this->tableless;

		$arrSet = array();
		$arrFields = array();
		$doNotSubmit = false;
		$hasUpload = false;
		$row = 0;

		// No need to check: if the address does not exist, fields will be empty and a new address will be created
		$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=? AND pid={$this->User->id} AND store_id={$this->Isotope->Config->store_id}")->execute($intAddressId);

		// Build form
		foreach ($this->arrFields as $field)
		{
			// Make the address object look like a Data Container (for the save_callback)
			$objAddress->field = $field;
			
			// Reference DCA, it's faster to lookup than a deep array
			$arrData = &$GLOBALS['TL_DCA']['tl_iso_addresses']['fields'][$field];

			// Map checkboxWizard to regular checkbox widget
			if ($arrData['inputType'] == 'checkboxWizard')
			{
				$arrData['inputType'] = 'checkbox';
			}

			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass) || !$arrData['eval']['feEditable'])
			{
				continue;
			}

			// Special field "country"
			if ($field == 'country')
			{
				$arrCountries = array();
				$objConfigs = $this->Database->execute("SELECT billing_countries, shipping_countries FROM tl_iso_config WHERE store_id={$this->Isotope->Config->store_id}");

				while( $objConfigs->next() )
				{
					$arrCountries = array_merge($arrCountries, deserialize($objConfigs->billing_countries), deserialize($objConfigs->shipping_countries));
				}

				$arrData['options'] = array_values(array_intersect($arrData['options'], $arrCountries));
				$arrData['default'] = $this->Isotope->Config->billing_country;
			}

			$strGroup = $arrData['eval']['feGroup'];

			$arrData['eval']['tableless'] = $this->tableless;
			$arrData['eval']['required'] = ($objAddress->$field == '' && $arrData['eval']['mandatory']) ? true : false;

			$objWidget = new $strClass($this->prepareForWidget($arrData, $field, ($objAddress->$field ? $objAddress->$field : $arrData['default'])));

			$objWidget->storeValues = true;
			$objWidget->rowClass = 'row_'.$row . (($row == 0) ? ' row_first' : '') . ((($row % 2) == 0) ? ' even' : ' odd');

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == 'tl_iso_addresses_' . $this->id)
			{
				$objWidget->validate();
				$varValue = $objWidget->value;

				// Convert date formats into timestamps
				if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Save callback
				if (is_array($arrData['save_callback']))
				{
					foreach ($arrData['save_callback'] as $callback)
					{
						$this->import($callback[0]);

						try
						{
							$varValue = $this->$callback[0]->$callback[1]($varValue, $objAddress);
						}
						catch (Exception $e)
						{
							$objWidget->class = 'error';
							$objWidget->addError($e->getMessage());
						}
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
					// Set new value
					$varSave = is_array($varValue) ? serialize($varValue) : $varValue;
					$objAddress->$field = $varSave;

					// Save field
					if ($objAddress->id > 0)
					{
						$this->Database->prepare("UPDATE tl_iso_addresses SET " . $field . "=? WHERE pid={$this->User->id} AND id={$objAddress->id}")->executeUncached($varSave);
					}
					else
					{
						$arrSet[$field] = $varSave;
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
			++$row;
		}

		$this->Template->hasError = $doNotSubmit;

		// Redirect or reload if there was no error
		if ($this->Input->post('FORM_SUBMIT') == 'tl_iso_addresses_' . $this->id && !$doNotSubmit)
		{
			if (!$objAddress->id)
			{
				$arrSet['pid'] = $this->User->id;
				$arrSet['tstamp'] = time();
				$arrSet['store_id'] = $this->Isotope->Config->store_id;

				$objAddress->id = $this->Database->prepare("INSERT INTO tl_iso_addresses %s")->set($arrSet)->execute()->insertId;
			}

			// Call onsubmit_callback
			if (is_array($GLOBALS['TL_DCA']['tl_iso_addresses']['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA']['tl_iso_addresses']['config']['onsubmit_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]($objAddress);
					}
				}
			}

			global $objPage;
			$this->redirect($this->generateFrontendUrl(array('id'=>$objPage->id, 'alias'=>$objPage->alias)));
		}

		$this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_iso_addresses']['addressDetails'];
		$this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_iso_addresses']['contactDetails'];
		$this->Template->personalData = $GLOBALS['TL_LANG']['tl_iso_addresses']['personalData'];
		$this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_iso_addresses']['loginDetails'];

		// Add groups
		foreach ($arrFields as $k=>$v)
		{
			$this->Template->$k = $v;
		}

		$this->Template->formId = 'tl_iso_addresses_' . $this->id;
		$this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->rowLast = 'row_' . $row . ((($row % 2) == 0) ? ' even' : ' odd');
	}


	/**
	 * Delete the given address and make sure it belongs to the current frontend user
	 * @param integer
	 * @return void
	 */
	protected function delete($intAddressId)
	{
		$this->Database->prepare("DELETE FROM tl_iso_addresses WHERE id=? AND pid={$this->User->id}")->executeUncached($intAddressId);

		global $objPage;
		$this->redirect($this->generateFrontendUrl($objPage->row()));
	}
}

