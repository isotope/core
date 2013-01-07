<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */

namespace Isotope;


/**
 * Class tl_iso_attribuets
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_attributes extends \Backend
{

	/**
	 * Disable the internal field name field if it is not empty.
	 * @param object
	 * @return	void
	 */
	public function disableFieldName($dc)
	{
		// Hide the field in editAll & overrideAll mode (Thanks to Yanick Witschi)
		if (\Input::get('act') == 'editAll' || \Input::get('act') == 'overrideAll')
		{
			$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['doNotShow'] = true;
		}
		elseif ($dc->id)
		{
			$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");

			if ($objAttribute->field_name != '')
			{
				$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['disabled'] = true;
				$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['mandatory'] = false;
			}
		}
	}


	/**
	 * Hide certain options if this is a variant option
	 * @param DataContainer
	 */
	public function prepareForVariantOptions($dc)
	{
		$objAttribute = $this->Database->prepare("SELECT * FROM tl_iso_attributes WHERE id=?")->execute($dc->id);

		if ($objAttribute->variant_option)
		{
			unset($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['options']['eval']['columnFields']['default']);
			unset($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['options']['eval']['columnFields']['group']);
		}
	}


	/**
	 * Create an attribute column in tl_iso_products table
	 * @param mixed
	 * @param object
	 * @return mixed
	 * @throws Exception
	 */
	public function createColumn($varValue, $dc)
	{
		$varValue = standardize($varValue);

		if (in_array($varValue, array('id', 'pid', 'tstamp', 'dateAdded', 'type', 'language', 'pages', 'inherit')))
		{
			throw new InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}

		if ($varValue != '' && !$this->Database->fieldExists($varValue, 'tl_iso_products'))
		{
			$strType = $GLOBALS['ISO_ATTR'][\Input::post('type')]['sql'] == '' ? 'text' : \Input::post('type');

			$this->Database->query(sprintf("ALTER TABLE tl_iso_products ADD %s %s", $varValue, $GLOBALS['ISO_ATTR'][$strType]['sql']));
		}

		return $varValue;
	}


	/**
	 * Alter an attribtue column in tl_iso_products table
	 * @param object
	 * @return void
	 */
	public function modifyColumn($dc)
	{
		$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");

		if ($objAttribute->field_name == '')
		{
			return;
		}

		if ($dc->activeRecord->type != '' && $objAttribute->type != $dc->activeRecord->type && $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['sql'] != '' && $this->Database->fieldExists($dc->activeRecord->field_name, 'tl_iso_products'))
		{
			$this->Database->query(sprintf("ALTER TABLE tl_iso_products MODIFY %s %s", $objAttribute->field_name, $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['sql']));
		}

		if ($objAttribute->fe_filter && $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['useIndex'])
		{
			$arrFields = $this->Database->listFields('tl_iso_products');

			if ($arrFields[$objAttribute->field_name]['type'] != 'index')
			{
				$this->Database->query("ALTER TABLE `tl_iso_products` ADD KEY `{$objAttribute->field_name}` (`{$objAttribute->field_name}`);");
			}
		}
	}


	/**
	 * Remove field that are not available in certain attributes and could cause unwanted results
	 * @param object
	 * @return void
	 */
	public function cleanFieldValues($dc)
	{
		$strPalette = $GLOBALS['TL_DCA']['tl_iso_attributes']['palettes'][$dc->activeRecord->type];

		if ($dc->activeRecord->variant_option && $GLOBALS['TL_DCA']['tl_iso_attributes']['palettes'][$dc->activeRecord->type.'variant_option'] != '')
		{
			$strPalette = $GLOBALS['TL_DCA']['tl_iso_attributes']['palettes'][$dc->activeRecord->type.'variant_option'];
		}

		$arrFields = array_keys($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']);
		$arrKeep = trimsplit(',|;', $strPalette);
		$arrSubpalettes = trimsplit(',', implode(',', (array)$GLOBALS['TL_DCA']['tl_iso_attributes']['subpalettes']));

		$arrClean = array_diff($arrFields, $arrKeep, $arrSubpalettes, array('pid', 'sorting'));

		$this->Database->execute("UPDATE tl_iso_attributes SET " . implode("='', ", $arrClean) . "='' WHERE id={$dc->id}");
	}


	/**
	 * Return an array of select-attributes
	 * @param object
	 * @return array
	 */
	public function getConditionFields($dc)
	{
		$this->loadDataContainer('tl_iso_products');
		$arrFields = array();

		foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
		{
			if ($arrData['inputType'] == 'select' || ($arrData['inputType'] == 'conditionalselect' && $field != $dc->activeRecord->field_name))
			{
				$arrFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrFields;
	}


	/**
	 * Return a list of available rte config files
	 * @param object
	 * @return array
	 */
	public function getRTE($dc)
	{
		$arrOptions = array();

		foreach (scan(TL_ROOT . '/system/config') as $file)
		{
			if (is_file(TL_ROOT . '/system/config/' . $file) && strpos($file, 'tiny') === 0)
			{
				$arrOptions[] = basename($file, '.php');
			}
		}

		return $arrOptions;
	}


	/**
	 * Validate table and field of foreignKey
	 * @param mixed
	 * @param object
	 * @return mixed
	 */
	public function validateForeignKey($varValue, $dc)
	{
		if ($varValue != '')
		{
			$arrLines = trimsplit('@\r\n|\n|\r@', $varValue);

			foreach ($arrLines as $foreignKey)
			{
				if ($foreignKey == '' || strpos($foreignKey, '#') === 0)
				{
					continue;
				}

				if (strpos($foreignKey, '=') === 2)
				{
					$foreignKey = substr($foreignKey, 3);
				}

				list($strTable, $strField) = explode('.', $foreignKey, 2);
				$this->Database->execute("SELECT $strField FROM $strTable");
			}
		}

		return $varValue;
	}


	/**
	 * To enable date picker, the rgxp must be date, time or datim
	 * @param mixed
	 * @param object
	 * @return mixed
	 */
	public function validateDatepicker($varValue, $dc)
	{
		if ($varValue && !in_array($dc->activeRecord->rgxp, array('date', 'time', 'datim')))
		{
			throw new UnexpectedValueException($GLOBALS['ISO_LANG']['ERR']['datepickerRgxp']);
		}

		return $varValue;
	}
}

