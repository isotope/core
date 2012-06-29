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
 * Class FieldWizard
 *
 * Provide methods to handle fields table.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class FieldWizard extends Widget
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

				/*if (!is_array($this->varValue))
				{
					$this->varValue = array();

					if ($this->table != '')
					{
						foreach( $GLOBALS['TL_DCA'][$this->table]['fields'] as $field => $arrData )
						{
							if ($arrData['eval']['feEditable'] && $arrData['eval']['mandatory'])
							{
								$this->varValue[] = array('value'=>$field, 'enabled'=>true, 'mandatory'=>true);
							}
						}
					}
				}*/
				break;

			case 'options':
				break;

			case 'table':
				$this->loadLanguageFile($varValue);
				$this->loadDataContainer($varValue);

				$this->arrOptions = array();

				foreach ($GLOBALS['TL_DCA'][$varValue]['fields'] as $name => $arrData)
				{
					if ($arrData['eval']['feEditable'])
					{
						$this->arrOptions[] = $name;
					}
				}

				parent::__set($strKey, $varValue);
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			case 'maxlength':
				$this->arrAttributes[$strKey] = ($varValue > 0) ? $varValue : '';
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Validate input and set value
	 */
	public function validate()
	{
		$mandatory = $this->mandatory;
		$options = deserialize($this->getPost($this->strName));

		// Check "enabled" only (values can be empty)
		if (is_array($options))
		{
			foreach ($options as $key=>$option)
			{
				$options[$key]['label'] = trim($option['label']);

				if ($options[$key]['enabled'])
				{
					$this->mandatory = false;
				}
			}
		}

		$varInput = $this->validator($options);

		if (!$this->hasErrors())
		{
			$this->varValue = $varInput;
		}

		// Reset the property
		if ($mandatory)
		{
			$this->mandatory = true;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
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

		// Sort options
		if ($this->varValue)
		{
			$arrOptions = array();
			$arrTemp = $this->arrOptions;

			// Move selected and sorted options to the top
			foreach ($this->varValue as $i=>$arrOption)
			{
				$arrOptions[$i] = $arrOption['value'];
				unset($this->arrOptions[array_search($arrOption['value'], $this->arrOptions)]);
			}

			ksort($arrOptions);
			$this->arrOptions = array_merge($arrOptions, $this->arrOptions);
		}

		// Begin table
		$return .= '<table class="tl_optionwizard" id="ctrl_'.$this->strId.'">
  <thead>
    <tr>
      <th>'.$this->generateImage('show.gif', '', 'title="'.$GLOBALS['TL_LANG'][$this->strTable]['fwEnabled'].'"').'</th>
      <th>&nbsp;</th>
      <th>'.$this->generateImage('show.gif', '', 'title="'.$GLOBALS['TL_LANG'][$this->strTable]['fwLabel'].'"').'</th>
      <th>'.$this->generateImage('show.gif', '', 'title="'.$GLOBALS['TL_LANG'][$this->strTable]['fwMandatory'].'"').'</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>';

		$tabindex = 0;

		// Add fields
		foreach ($this->arrOptions as $i=>$option)
		{
			$return .= '
    <tr>
      <td><input type="hidden" name="'.$this->strId.'['.$i.'][enabled]" value=""><input type="checkbox" name="'.$this->strId.'['.$i.'][enabled]" id="'.$this->strId.'_enabled_'.$i.'" class="fw_checkbox" tabindex="'.++$tabindex.'" value="1"'.($this->varValue[$i]['enabled'] ? ' checked="checked"' : '').'></td>
      <td><input type="hidden" name="'.$this->strId.'['.$i.'][value]" value="'.$option.'">'.$GLOBALS['TL_DCA'][$this->table]['fields'][$option]['label'][0].'</td>
      <td><input type="text" name="'.$this->strId.'['.$i.'][label]" id="'.$this->strId.'_label_'.$i.'" class="tl_text_4" tabindex="'.++$tabindex.'" value="'.specialchars($this->varValue[$i]['label']).'"></td>
      <td><input type="hidden" name="'.$this->strId.'['.$i.'][mandatory]" value=""><input type="checkbox" name="'.$this->strId.'['.$i.'][mandatory]" id="'.$this->strId.'_mandatory_'.$i.'" class="fw_checkbox" tabindex="'.++$tabindex.'" value="1"'.($this->varValue[$i]['mandatory'] ? ' checked="checked"' : '').'> <label for="'.$this->strId.'_mandatory_'.$i.'"></label></td>';

			// Add row buttons
			$return .= '
      <td style="white-space:nowrap; padding-left:3px;">';

			foreach ($arrButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$button][0]).'" onclick="Isotope.fieldWizard(this, \''.$button.'\', \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable][$button][0]).'</a> ';
			}

			$return .= '</td>
    </tr>';
		}

		return $return.'
  </tbody>
  </table>';
	}
}

?>