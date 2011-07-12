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
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class VariantWizard extends Widget
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

			case 'maxlength':
				$this->arrAttributes[$strKey] = ($varValue > 0) ? $varValue : '';
				break;

			case 'options':
				$this->arrOptions = deserialize($varValue, true);
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
		// This widget has no data...
		$this->varValue = '';

		$this->import('Database');

		$arrOptions = array();
		$arrValue = deserialize($this->getPost($this->strName));

		if (!is_array($arrValue))
		{
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			return;
		}

		foreach( $arrValue as $k => $v )
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$k];
			//convert to timestamp if necessary
			switch($arrData['eval']['rgxp'])
			{
				case 'date':
				case 'time':
				case 'datim':
					$objDate = new Date($v, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$v = $objDate->tstamp;
					break;
			}

			if (!strlen($v))
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			}

			$arrOptions[$k] = $v;
		}

		$objVariant = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE " . implode('=? AND ', array_keys($arrOptions)) . "=? AND id!=? AND pid=(SELECT pid FROM tl_iso_products WHERE id=?)")->execute(array_merge($arrOptions, array($this->currentRecord, $this->currentRecord)));

		if ($objVariant->numRows)
		{
			$this->addError($GLOBALS['TL_LANG']['ERR']['variantDuplicate']);
		}

		if (!$this->hasErrors())
		{
			$arrOptions['tstamp'] = time();

			$this->Database->prepare("UPDATE tl_iso_products %s WHERE id=?")->set($arrOptions)->execute($this->currentRecord);
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		if (!is_array($this->arrOptions) || !count($this->arrOptions))
		{
			return '';
		}

		$this->import('Database');
		$objVariant = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id=?")->limit(1)->execute($this->currentRecord);

		// Begin table
		$return = '<table class="tl_variantwizard" id="ctrl_'.$this->strId.'">
  ';

		// Add fields
		foreach ($this->arrOptions as $option)
		{
			$datepicker = '';

			$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$option['value']];

			switch($arrData['inputType'])
			{
				case 'text':
					$objWidget = new TextField($this->prepareForWidget($arrData, $this->strId.'['.$option['value'].']', $objVariant->{$option['value']}));

					if ($arrData['eval']['datepicker'])
					{
						$objWidget->id = str_replace('[', '_', $objWidget->id);
						$objWidget->id = str_replace(']', '_', $objWidget->id);

						$datepicker = '
			  <script>
			  window.addEvent(\'domready\', function() { ' . sprintf($arrData['eval']['datepicker'], 'ctrl_' . $objWidget->id) . ' });
			  </script>';
					}
					break;

				default:
					$arrField = $this->prepareForWidget($arrData, $this->strId.'['.$option['value'].']', $objVariant->{$option['value']});

					foreach( $arrField['options'] as $k => $v )
					{
						if ($v['value'] == '')
							unset($arrField['options'][$k]);
					}

					$objWidget = new SelectMenu($arrField);
					break;
			}

			$return .= '
    <tr>
      <td>' . $objWidget->generateLabel() . '&nbsp;</td>
      <td>' . $objWidget->generate().$datepicker.'&nbsp;</td>
    </tr>';
		}

		return $return . '
</table>';
	}
}