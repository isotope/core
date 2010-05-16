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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
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
	 * A list of fields we do not want to show. This can be set by the product type class.
	 */
	protected $arrDisabledFields;
	
	
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

		if (!isset($_POST[$this->strName]))
		{
			$this->varValue = '';
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
		$this->arrDisabledFields = $GLOBALS['ISO_PRODUCT'][$this->objActiveRecord->type]['disabledFields'];
		
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
			foreach ($this->arrOptions as $i=>$arrOptionGroup)
			{
				$arrOptions[$i] = array();
				$arrTemp = $this->arrOptions[$i];
				
				foreach( $arrOptionGroup as $k=>$arrOption )
				{
					if (($intPos = array_search($arrOption['value'], $this->varValue)) !== false)
					{
						$arrOptions[$i][$intPos] = $arrOption;
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
		
		
		foreach ($this->arrOptions as $i=>$arrOptionGroup)
		{
			$id = 'cbc_' . $this->strId . '_' . standardize($i);

			$img = 'folPlus';
			$display = 'none';

			if (!isset($state[$id]) || !empty($state[$id]))
			{
				$img = 'folMinus';
				$display = 'block';
			}

			$arrOptions[] = '<div class="checkbox_toggler' . ($blnFirst ? '_first' : '') . '"><a href="' . $this->addToUrl('cbc=' . $id) . '" onclick="AjaxRequest.toggleCheckboxGroup(this, \'' . $id . '\'); Backend.getScrollOffset(); return false;"><img src="system/themes/' . $this->getTheme() . '/images/' . $img . '.gif" alt="toggle checkbox group" /></a>' . $GLOBALS['TL_LANG']['tl_product_data'][$i] .	'</div><div id="' . $id . '" class="checkbox_options" style="display:' . $display . ';"><span class="fixed"><input type="checkbox" id="check_all_' . $id . '" class="tl_checkbox" onclick="Isotope.toggleCheckboxGroup(this, \'' . $id . '\')" /> <label for="check_all_' . $id . '" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label></span>';

			foreach ($arrOptionGroup as $arrOption)
			{
				$strButtons = '';
				
				$k = (is_array($this->varValue) && in_array($arrOption['value'], $this->varValue)) ? $cid++ : '';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$strButton.'&amp;cid='.$k.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$strButton][0]).'" onclick="Isotope.attributeWizard(this, \''.$strButton.'\', \''.$id.'\'); return false;">'.$this->generateImage($strButton.'.gif', $GLOBALS['TL_LANG'][$this->strTable][$strButton][0], 'class="tl_checkbox_wizard_img"').'</a> ';
				}
				
				$arrOptions[] = $this->generateCheckbox($arrOption, $i, $strButtons);
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

        return sprintf('<div id="ctrl_%s" class="%s%s">%s%s</div>%s',
						$this->strId,
						'tl_checkbox_container tl_checkbox_wizard',
						(strlen($this->strClass) ? ' ' . $this->strClass : ''),
						($blnCheckAll ? '<span class="fixed"><input type="checkbox" id="check_all_' . $this->strId . '" class="tl_checkbox" onclick="Isotope.toggleCheckboxGroup(this, \'ctrl_' . $this->strId . '\')" /> <label for="check_all_' . $this->strId . '" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label></span>' : ''),
						implode('', $arrOptions),
						$this->wizard);
	}


	/**
	 * Generate a checkbox and return it as string
	 * @param array
	 * @param integer
	 * @param string
	 * @return string
	 */
	protected function generateCheckbox($arrOption, $strGroup, $strButtons)
	{
		
		if ($arrOption['disabled'])
		{
			return sprintf('<span><input type="hidden" name="%s" value="%s"%s /><input id="opt_%s" type="checkbox" class="tl_checkbox" disabled="disabled" checked="checked" /> %s <label for="opt_%s">%s</label></span>',
							$this->strName . '[]',
							specialchars($arrOption['value']),
							$this->getAttributes(),
							$this->strId.'_'.$arrOption['value'],
							$strButtons,
							$this->strId.'_'.$arrOption['value'],
							$arrOption['label']);
		}
		
		return sprintf('<span><input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s onfocus="Backend.getScrollOffset();" /> %s <label for="opt_%s">%s</label></span>',
						$this->strName . '[]',
						$this->strId.'_'.$arrOption['value'],
						specialchars($arrOption['value']),
						((is_array($this->varValue) && in_array($arrOption['value'], $this->varValue)) ? ' checked="checked"' : ''),
						$this->getAttributes(),
						$strButtons,
						$this->strId.'_'.$arrOption['value'],
						$arrOption['label']);
	}
	
	
	/**
	 * Return attributes as associative array with legends as keys
	 */
	protected function getOptions()
	{
		$this->import('Database');
		$this->loadDataContainer('tl_product_data');
		$this->loadLanguageFile('tl_product_data');
		
		$arrAttributes = array();
		$arrDca = $GLOBALS['TL_DCA']['tl_product_data']['fields'];	
				
		foreach( $arrDca as $field => $arrData )
		{
			if (is_array($arrData['attributes']) && strlen($arrData['attributes']['legend']) && (!is_array($this->arrDisabledFields) || !in_array($field, $this->arrDisabledFields)))
			{
				// Variant options are not available
				if ($this->variants && ($arrData['attributes']['add_to_product_variants'] || $arrData['attributes']['inherit']))
					continue;
					
				$arrAttributes[$arrData['attributes']['legend']][] = array
				(
					'label'		=> (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field),
					'value'		=> $field,
					'disabled'	=> ($this->variants ? $arrData['attributes']['variant_fixed'] : $arrData['attributes']['fixed']),
				);
			}
		}
		
		uksort($arrAttributes, create_function('$a,$b', 'return (array_search($a, $GLOBALS["ISO_MSC"]["tl_product_data"]["groups_ordering"]) > array_search($b, $GLOBALS["ISO_MSC"]["tl_product_data"]["groups_ordering"])) ? 1 : -1;'));
				
		return $arrAttributes;
	}
}

