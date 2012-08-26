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
 * Class AttributeWizard
 *
 * Provide methods to handle attributes.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class AttributeWizard extends Widget
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
	 * Options
	 * @var array
	 */
	protected $arrOptions = array();

	/**
	 * A list of fields we do not want to show. This can be set by the product type class
	 * @var array
	 */
	protected $arrDisabledFields;

	/**
	 * Active record
	 * @param object
	 */
	protected $objActiveRecord;


	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'options':
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			case 'variants':
				$this->arrConfiguration[$strKey] = $varValue ? true : false;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Clear result if nothing has been submitted
	 */
	public function validate()
	{
		parent::validate();

		// Workaround for key sorting in DataContainer ~line 285
		$i = 0;
		foreach ($this->varValue as $k => $v)
		{
			if (!$v['enabled'])
			{
				unset($this->varValue[$k]);
				continue;
			}

			$this->varValue[$k]['position'] = $i++;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$this->import('Database');

		$this->objActiveRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")->execute($this->currentRecord);
		$this->arrDisabledFields = $GLOBALS['ISO_PRODUCT'][$this->objActiveRecord->class]['disabledFields'];
		$this->arrOptions = $this->getOptions();

		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/backend.js';

		$arrButtons = array('up', 'down');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			$this->import('Database');

			switch ($this->Input->get($strCommand))
			{
				case 'up':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;
			}

			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);

			$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
		}

		$state = $this->Session->get('checkbox_groups');

		// Toggle checkbox group
		if ($this->Input->get('cbc'))
		{
			$state[$this->Input->get('cbc')] = (isset($state[$this->Input->get('cbc')]) && $state[$this->Input->get('cbc')] == 1) ? 0 : 1;
			$this->Session->set('checkbox_groups', $state);

			$this->redirect(preg_replace('/(&(amp;)?|\?)cbc=[^& ]*/i', '', $this->Environment->request));
		}

		// Sort options
		if ($this->varValue)
		{
			$arrOptions = array();

			// Move selected and sorted options to the top
			foreach ($this->arrOptions as $i => $arrOptionGroup)
			{
				$arrOptions[$i] = array();
				$arrTemp = $this->arrOptions[$i];

				foreach ($arrOptionGroup as $k => $arrOption)
				{
					if ($this->varValue[$arrOption['value']]['enabled'])
					{
						$arrOptions[$i][$this->varValue[$arrOption['value']]['position']] = $arrOption;
						unset($arrTemp[$k]);
					}
				}

				ksort($arrOptions[$i]);
				$arrOptions[$i] = array_merge($arrOptions[$i], $arrTemp);
			}

			$this->arrOptions = $arrOptions;
		}

		$cid = 0;
		$blnFirst = true;
		$blnCheckAll = true;
		$arrOptions = array();

		foreach ($this->arrOptions as $i => $arrOptionGroup)
		{
			$id = 'cbc_' . $this->strId . '_' . standardize($i, true);
			$img = 'folPlus';
			$display = 'none';

			if (!isset($state[$id]) || !empty($state[$id]))
			{
				$img = 'folMinus';
				$display = 'block';
			}

			$arrOptions[] = '<div class="checkbox_toggler' . ($blnFirst ? '_first' : '') . '"><a href="' . $this->addToUrl('cbc=' . $id) . '" onclick="AjaxRequest.toggleCheckboxGroup(this, \'' . $id . '\'); Backend.getScrollOffset(); return false;"><img src="system/themes/' . $this->getTheme() . '/images/' . $img . '.gif" alt="toggle checkbox group"></a>' . $GLOBALS['TL_LANG']['tl_iso_products'][$i] .	'</div><div id="' . $id . '" class="checkbox_options" style="display:' . $display . ';"><span class="fixed"><input type="checkbox" id="check_all_' . $id . '" class="tl_checkbox" onclick="Isotope.toggleCheckboxGroup(this, \'' . $id . '\')"> <label for="check_all_' . $id . '" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label></span>';

			foreach ($arrOptionGroup as $arrOption)
			{
				$strButtons = '';
				$k = (is_array($this->varValue) && in_array($arrOption['value'], $this->varValue)) ? $cid++ : '';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$strButton.'&amp;cid='.$k.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$strButton][0]).'" onclick="Isotope.attributeWizard(this, \''.$strButton.'\', \''.$id.'\'); return false;">'.$this->generateImage($strButton.'.gif', $GLOBALS['TL_LANG'][$this->strTable][$strButton][0], 'class="tl_checkbox_wizard_img"').'</a> ';
				}

				$arrOptions[] = $this->generateCheckbox($arrOption, $i, $strButtons, $cid);
			}

			$arrOptions[] = '</div>';
			$blnFirst = false;
			$blnCheckAll = false;
		}


		// Add a "no entries found" message if there are no options
		if (!count($arrOptions))
		{
			$arrOptions[]= '<p class="tl_noopt">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
			$blnCheckAll = false;
		}

        return sprintf('%s<div id="ctrl_%s" class="%s%s">%s%s</div>%s',
        				$this->generateInfoBar(),
						$this->strId,
						'tl_checkbox_container tl_checkbox_wizard tl_attributewizard',
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						($blnCheckAll ? '<span class="fixed"><input type="checkbox" id="check_all_' . $this->strId . '" class="tl_checkbox" onclick="Isotope.toggleCheckboxGroup(this, \'ctrl_' . $this->strId . '\')"> <label for="check_all_' . $this->strId . '" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label></span>' : ''),
						implode('', $arrOptions),
						$this->wizard);
	}


	/**
	 * Generate a checkbox and return it as string
	 * @param array
	 * @param string
	 * @param string
	 * @return string
	 */
	protected function generateCheckbox($arrOption, $strGroup, $strButtons, $cid)
	{
		$strBuffer = '<span class="row" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><span class="cell label">';

		if ($arrOption['disabled'])
		{
			$strBuffer .= sprintf('<input type="hidden" name="%s" value="1"%s><input id="opt_%s" type="checkbox" class="tl_checkbox" disabled="disabled" checked="checked"> %s <label for="opt_%s">%s&nbsp;<span>[%s]</span></label>',
							$this->strName . '[' . $arrOption['value'] . '][enabled]',
							$this->getAttributes(),
							$this->strId.'_'.$arrOption['value'],
							$strButtons,
							$this->strId.'_'.$arrOption['value'],
							$arrOption['label'],
							$arrOption['value']);
		}
		else
		{
			$strBuffer .= sprintf('<input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="1"%s%s onfocus="Backend.getScrollOffset();"> %s <label for="opt_%s">%s&nbsp;<span>[%s]</span></label>',
							$this->strName . '[' . $arrOption['value'] . '][enabled]',
							$this->strId.'_'.$arrOption['value'],
							((is_array($this->varValue) && $this->varValue[$arrOption['value']]['enabled']) ? ' checked="checked"' : ''),
							$this->getAttributes(),
							$strButtons,
							$this->strId.'_'.$arrOption['value'],
							$arrOption['label'],
								$arrOption['value']);
		}

		// Add tl_class options from DCA
		$strBuffer .= '</span><span class="cell tl_class_select"><select class="select" name="' . $this->strName . '[' . $arrOption['value'] . '][tl_class_select]"><option value="">-</option>';

		foreach ($this->tl_classes as $class)
		{
			$strBuffer .= '<option value="' . $class . '"' . $this->optionSelected($this->varValue[$arrOption['value']]['tl_class_select'], $class) . '>' . $class . '</option>';
		}

		return $strBuffer . '
	</select></span>
	<span class="cell tl_class_text"><input type="text" class="tl_text_4" name="' . $this->strName . '[' . $arrOption['value'] . '][tl_class_text]" value="' . $this->varValue[$arrOption['value']]['tl_class_text'] . '"></span>
	<span class="cell mandatory_default"><input type="radio" name="' . $this->strName . '[' . $arrOption['value'] . '][mandatory]" value="0"' . $this->optionChecked($this->varValue[$arrOption['value']]['mandatory'], 0) . '></span>
	<span class="cell mandatory_no"><input type="radio" name="' . $this->strName . '[' . $arrOption['value'] . '][mandatory]" value="1"' . $this->optionChecked($this->varValue[$arrOption['value']]['mandatory'], 1) . '></span>
	<span class="cell mandatory_yes"><input type="radio" name="' . $this->strName . '[' . $arrOption['value'] . '][mandatory]" value="2"' . $this->optionChecked($this->varValue[$arrOption['value']]['mandatory'], 2) . '></span>
</span>';
	}


	/**
	 * Return attributes as associative array with legends as keys
	 * @return array
	 */
	protected function getOptions()
	{
		$this->import('Database');
		$this->loadDataContainer('tl_iso_attributes');
		$this->loadDataContainer('tl_iso_products');
		$this->loadLanguageFile('tl_iso_products');

		$arrAttributes = array();
		$arrDca = $GLOBALS['TL_DCA']['tl_iso_products']['fields'];

		foreach ($arrDca as $field => $arrData)
		{
			if (is_array($arrData['attributes']) && $arrData['attributes']['legend'] != '' && (!is_array($this->arrDisabledFields) || !in_array($field, $this->arrDisabledFields)))
			{
				// Variant options are not available
				if ($this->variants && ($arrData['attributes']['variant_option'] || $arrData['attributes']['inherit']))
				{
					continue;
				}

				$arrAttributes[$arrData['attributes']['legend']][] = array
				(
					'label'		=> (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field),
					'value'		=> $field,
					'disabled'	=> ($this->variants ? $arrData['attributes']['variant_fixed'] : $arrData['attributes']['fixed']),
				);
			}
		}

		uksort($arrAttributes, create_function('$a,$b', 'return (array_search($a, $GLOBALS["TL_DCA"]["tl_iso_attributes"]["fields"]["legend"]["options"]) > array_search($b, $GLOBALS["TL_DCA"]["tl_iso_attributes"]["fields"]["legend"]["options"])) ? 1 : -1;'));

		return $arrAttributes;
	}


	/**
	 * Generates the info icons that contain the description for the columns
	 * @return string
	 */
	private function generateInfoBar()
	{
		$return = '<div class="tl_attributewizard_columninfo"><span class="cell label"></span>';

		$arrColumns = array
		(
			'tl_class_select',
			'tl_class_text',
			'mandatory_default',
			'mandatory_no',
			'mandatory_yes'
		);

		foreach ($arrColumns as $strClass)
		{
			$strLabel = $GLOBALS['TL_LANG']['tl_iso_producttypes']['attrwiz'][$strClass];
			$return .= '<span class="cell ' . $strClass . '">' . $this->generateImage('show.gif', $strLabel, 'class="' . $strClass . '" title="' . $strLabel . '"') . '</span>';
		}

		return $return . '</div>';
	}
}

