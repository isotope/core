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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class IsotopeConditionalSelect extends Backend
{
	
	public function mergeData($arrData, $arrAttributes, &$objWidget=null, &$objProduct=null)
	{
		if (is_object($objProduct))
		{
			$arrData['eval']['conditionField'] = $arrAttributes['conditionField'] . '_' . $objProduct->id;
			
			if (is_object($objWidget))
			{
				$objWidget->conditionField .= '_' . $objProduct->id;
				
				$this->import('Isotope');
				$this->Isotope->mergeOptionData($arrData, $arrAttributes, $objWidget, $objProduct);
			}
			
			return $arrData;
		}
		
		$arrData['eval']['conditionField'] = $arrAttributes['conditionField'];
		
		$arrValues = array();
		$arrOptionsList = deserialize($arrAttributes['option_list']);
		
		if (is_array($arrOptionsList) && count($arrOptionsList))
		{
			$strGroup = '';
			foreach ($arrOptionsList as $arrOptions)
			{
				if ($arrOptions['group'])
				{
					$strGroup = $arrOptions['value'];
					continue;
				}
				
				$arrValues[$strGroup][$arrOptions['value']] = $arrOptions['label'];
			}
			
			$arrData['options'] = $arrValues;
		}
		
		return $arrData;
	}
	
	
	/**
	 * Returns an array of select-fields in the same form
	 */
	public function getConditionFields($dc)
	{
		$this->loadDataContainer('tl_product_data');
		
		$arrFields = array();
											
		foreach( $GLOBALS['TL_DCA']['tl_product_data']['fields'] as $field => $arrData )
		{
			if ($arrData['inputType'] == 'select' || $arrData['inputType'] == 'optionDataWizard')
			{
				$arrFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}
		
		return $arrFields;
	}
}