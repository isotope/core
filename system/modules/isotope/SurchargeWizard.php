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
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class SurchargeWizard extends Widget
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
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'value':
				$this->varValue = deserialize($varValue, true);
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/backend.js';

		$this->import('Database');

		//allows us to set which buttons can be enabled for this widget.
		/*foreach($this->enabledFunctions as $v)
		{
			$arrButtons[] = $v;
		}*/

		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			switch ($this->Input->get($strCommand))
			{

				case 'copy':
					$this->varValue = array_duplicate($this->varValue, $this->Input->get('cid'));
					break;

				case 'up':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}
		}

		$objTaxClasses = $this->Database->execute("SELECT id, name FROM tl_iso_tax_class");

		if($objTaxClasses->numRows)
		{
			$arrTaxClasses = $objTaxClasses->fetchAllAssoc();
		}

		if(!is_array($arrTaxClasses) || !count($arrTaxClasses))
		{
			$arrTaxClasses = array('');
		}

		// Get new value
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable)
		{
			$varValue = $this->Input->post($this->strId);
		}

		// Make sure there is at least an empty array
		if (!is_array($this->varValue) || !$this->varValue[0])
		{
			//$this->varValue = array('');
		}
		else
		{
			/*foreach($this->varValue as $v)
			{


			}*/
		}

		// Save the value
		if ($this->Input->get($strCommand) || $this->Input->post('FORM_SUBMIT') == $this->strTable)
		{
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);

			// Reload the page
			if (is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
			{
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
			}
		}

		// Add label and return wizard
		$return .= '<table class="tl_optionwizard" id="ctrl_'.$this->strId.'">
  <thead>';

		if(is_array($this->varValue) && count($this->varValue))
  		{
  			$return .= '
    <tr>
      <th><strong>'.$GLOBALS['TL_LANG'][$this->strTable]['opLabel'].'</strong></th>
      <th><strong>'.$GLOBALS['TL_LANG'][$this->strTable]['opPrice'].'</strong></th>
      <th><strong>'.$GLOBALS['TL_LANG'][$this->strTable]['opTaxClass'].'</strong></th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>';

			// Add rows
			for ($i=0; $i<count($this->varValue); $i++)
			{
				$arrRow = array();
				$arrRow = $this->varValue[$i];
				$blnEditable = false;

				if(is_array($arrRow))
				{
					$blnEditable = true;
				}
				else
				{
					continue;
				}

				$return .= '<tr>';
				$return .= '	<td>'.$this->varValue[$i]['label'].'<input type="hidden" name="'.$this->strId.'['.$i.'][label]" id="'.$this->strId.'_label_'.$i.'" value="'.$this->varValue[$i]['label'].'"></td>';
				$return .= '	<td>'.($blnEditable ? '<input type="text" name="'.$this->strId.'['.$i.'][total_price]" id="'.$this->strId.'_total_price_'.$i.'" class="tl_text_3" value="'.specialchars(round($this->varValue[$i]['total_price'], 2)).'">' : round($this->varValue[$i]['total_price'], 2)) . '</td>';
				$options = '';

				$options = '<option value=""'.$this->optionSelected(NULL,$this->varValue[$i]['tax_class']).'>-</option>';
				// Add Tax Classes
				foreach ($arrTaxClasses as $v)
				{
					$options .= '<option value="'.specialchars($v['id']).'"'.$this->optionSelected($v['id'], $this->varValue[$i]['tax_class']).'>'.$v['name'].'</option>';
					if($v['id']==$this->varValue[$i]['tax_class'])
					{
						$strTaxLabel = $v['name'];
					}
				}

				$return .= '
      <td>'.($blnEditable ? '<select name="'.$this->strId.'['.$i.'][tax_class]" class="tl_select_2" onfocus="Backend.getScrollOffset();">'.$options.'</select>' : $strTaxLabel).'</td>';

				$return .= '<td>';

				if(is_array($arrButtons))
				{
					foreach ($arrButtons as $button)
					{
						$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button]).'" onclick="Isotope.surchargeWizard(this, \''.$button.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button], 'class="tl_listwizard_img"').'</a> ';
					}
				}
				else
				{
					$return .= '&nbsp;';
				}

				$return .= '
      </td>
	</tr>';
			}
		}
		else
		{
				$return .= '
    <tr>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>' . $GLOBALS['TL_LANG']['MSC']['noSurcharges'] .'</td>
    </tr>';
		}

		return $return.'
  </tbody>
</table>';
	}
}

?>