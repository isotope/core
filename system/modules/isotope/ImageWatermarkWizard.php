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


class ImageWatermarkWizard extends Widget
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
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			case 'options':
				$this->arrOptions = deserialize($varValue);
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Return a parameter
	 * @return string
	 * @throws Exception
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'value':
				return $this->varValue;
				break;

			default:
				return isset($this->arrAttributes[$strKey]) ? $this->arrAttributes[$strKey] : $this->arrConfiguration[$strKey];
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
		$arrNames = array();

		foreach( $varInput as $k => $size )
		{
			if (in_array($size['name'], $arrNames))
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $GLOBALS['TL_LANG'][$this->strTable]['iwName']));
			}

			$arrNames[] = $size['name'];

			$this->mandatory = true;
			$this->rgxp = 'alpha';
			$this->spaceToUnderscore = true;
			$size['name'] = parent::validator($size['name']);

			$this->mandatory = false;
			$this->rgxp = 'digit';
			$this->spaceToUnderscore = false;
			$size['width'] = parent::validator($size['width']);
			$size['height'] = parent::validator($size['height']);

			$varInput[$k] = $size;
		}

		return $varInput;
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/backend.js';

		if (!is_array($this->varValue))
		{
			$this->varValue = array(array('name'=>'gallery'), array('name'=>'thumbnail'), array('name'=>'medium'), array('name'=>'large'));
		}

		$arrButtons = array('copy', 'delete');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			switch ($this->Input->get($strCommand))
			{
				case 'copy':
					$this->varValue = array_duplicate($this->varValue, $this->Input->get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}
		}


		// Begin table
		$return .= '<table cellspacing="0" cellpadding="0" class="tl_imagewatermarkwizard" id="ctrl_'.$this->strId.'" summary="Field wizard">
  <thead>
    <tr>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['iwName'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['iwWidth'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['iwHeight'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['iwMode'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['iwWatermark'].'</th>
      <th>'.$GLOBALS['TL_LANG'][$this->strTable]['iwPosition'].'</th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>';

		foreach( $this->varValue as $i => $size )
		{
			$arrModes = array();
			$arrPositions = array();

			foreach ($this->arrOptions as $arrOption)
			{
				$arrModes[] = sprintf('<option value="%s"%s>%s</option>',
									   specialchars($arrOption['value']),
									   ((is_array($this->varValue[$i]) && $this->varValue[$i]['mode'] == $arrOption['value']) ? ' selected="selected"' : ''),
									   $arrOption['label']);
			}

			foreach (array('tl', 'tc', 'tr', 'bl', 'bc', 'br', 'cc') as $option)
			{
				$arrPositions[] = sprintf('<option value="%s"%s>%s</option>',
										   specialchars($option),
										   ((is_array($this->varValue[$i]) && $this->varValue[$i]['position'] == $option) ? ' selected="selected"' : ''),
										   $GLOBALS['TL_LANG'][$this->strTable][$option]);
			}

			$filepicker = $this->generateImage('pickfile.gif', $GLOBALS['TL_LANG']['MSC']['filepicker'], 'style="vertical-align:top; cursor:pointer;" onclick="Backend.pickFile(this.getPrevious())"');

			$return .= '
    <tr>
      <td><input type="text" name="'.$this->strName.'['.$i.'][name]" id="'.$this->strId.'_name_'.$i.'" class="tl_text_4" value="'.specialchars($this->varValue[$i]['name']).'"" /></td>
      <td><input type="text" name="'.$this->strName.'['.$i.'][width]" id="'.$this->strId.'_width_'.$i.'" class="tl_text_4" value="'.specialchars($this->varValue[$i]['width']).'"" /></td>
      <td><input type="text" name="'.$this->strName.'['.$i.'][height]" id="'.$this->strId.'_height_'.$i.'" class="tl_text_4" value="'.specialchars($this->varValue[$i]['height']).'"" /></td>
      <td><select name="'.$this->strName.'['.$i.'][mode]" id="'.$this->strId.'_mode_'.$i.'" class="tl_select_interval" onfocus="Backend.getScrollOffset();">'.implode(' ', $arrModes).'</select></td>
      <td><input type="text" name="'.$this->strName.'['.$i.'][watermark]" id="'.$this->strId.'_watermark_'.$i.'" class="tl_text_2" value="'.specialchars($this->varValue[$i]['watermark']).'"" />'.$filepicker.'</td>
      <td><select name="'.$this->strName.'['.$i.'][position]" id="'.$this->strId.'_position_'.$i.'" class="tl_select_unit" onfocus="Backend.getScrollOffset();">'.implode(' ', $arrPositions).'</select></td>';

      		$return .= '
      <td>';

			foreach ($arrButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button]).'" onclick="Isotope.imageWatermarkWizard(this, \''.$button.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button], 'class="tl_listwizard_img"').'</a> ';
			}

			$return .= '</td>
    </tr>';
		}

		return $return.'
  </tbody>
  </table>';
	}
}

