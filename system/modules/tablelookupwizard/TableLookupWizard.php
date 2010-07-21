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


class TableLookupWizard extends Widget
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
	 * Allowed row ids
	 * @var array
	 */
	protected $arrIds = false;


	/**
	 * Make sure we know the ID for ajax upload session data
	 * @param array
	 */
	public function __construct($arrAttributes=false)
	{
		$this->strId = $arrAttributes['id'];
		
		parent::__construct($arrAttributes);
		
		$_SESSION['AJAX-FFL'][$this->strId]['type'] = 'tableLookup';
		
		$this->import('Database');
	}
	
	
	/**
	 * Store config for ajax upload.
	 * 
	 * @access public
	 * @param string $strKey
	 * @param mixed $varValue
	 * @return void
	 */
	public function __set($strKey, $varValue)
	{
		if (!is_object($varValue))
		{
			$_SESSION['AJAX-FFL'][$this->strId][$strKey] = $varValue;
		}
		
		switch ($strKey)
		{
			case 'allowedIds':
				$this->arrIds = deserialize($varValue);
				break;
				
			case 'searchFields':
				$arrFields = array();
				foreach( $varValue as $k => $v )
				{
					if (is_numeric($k))
					{
						$arrFields[] = $v;
					}
					else
					{
						$arrFields[] = $v . ' AS ' . $k;
					}
				}
				parent::__set($strKey, $arrFields);
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
	 * Validate input and set value
	 */
	public function validator($varInput)
	{
		if ($this->mandatory && (!is_array($varInput) || !count($varInput)))
		{
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
		}
		
		return $varInput;
	}

	
	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_CSS'][] = 'system/modules/tablelookupwizard/html/tablelookup.css';
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/tablelookupwizard/html/tablelookup.js';
		
		$this->loadLanguageFile($this->foreignTable);
		
		$arrIds = deserialize($this->varValue);
		
		if (!is_array($arrIds) || !count($arrIds))
		{
			$arrIds = array(0);
		}
				
		// User has javascript disabled an clicked on link
		if ($this->Input->get('noajax'))
		{
			$arrResults = $this->Database->execute("SELECT id, " . implode(', ', $this->listFields) . " FROM {$this->foreignTable}" . (strlen($this->sqlWhere) ? " WHERE {$this->sqlWhere}" : '') . " ORDER BY id=" . implode(' DESC, id=', $arrIds) . " DESC, name")->fetchAllAssoc();
			$strResults = $this->listResults($arrResults);
		}
		else
		{
			$arrResults = $this->Database->execute("SELECT id, " . implode(', ', $this->listFields) . " FROM {$this->foreignTable} WHERE id IN (" . implode(',', $arrIds) . ")" . (strlen($this->sqlWhere) ? " AND {$this->sqlWhere}" : ''))->fetchAllAssoc();
			$strResults = $this->listResults($arrResults);
			
			$strResults .= '
    <tr class="jserror">
      <td colspan="' . (count($this->listFields)+1) . '"><a href="' . $this->addToUrl('noajax=1') . '">' . $GLOBALS['TL_LANG']['MSC']['tlwJavascript'] . '</a></td>
    </tr>
    <tr class="search" style="display:none">
      <td colspan="' . (count($this->listFields)+1) . '"><label for="ctrl_' . $this->strId . '_search">' . ($this->searchLabel=='' ? $GLOBALS['TL_LANG']['MSC']['searchLabel'] : $this->searchLabel) . ':</label> <input type="text" id="ctrl_' . $this->strId . '_search" name="keywords" class="tl_text" autocomplete="off" /></td>
    </tr>';
		}
		
		
		$strBuffer = '
<table cellspacing="0" cellpadding="0" id="ctrl_' . $this->strId . '" class="tl_tablelookupwizard" summary="Table data">
  <thead>
    <tr>
      <th class="head_0 col_first">&nbsp;</th>';
      
      	$i = 1;
      	foreach( $this->listFields as $k => $v )
      	{
      		$field = is_numeric($k) ? $v : $k;
      		
      		$strBuffer .= '
  	  <th class="head_' . $i . ($i==count($this->listFields) ? ' col_last' : '') . '">' . $GLOBALS['TL_LANG'][$this->foreignTable][$field][0] . '</th>';
      		
      		$i++;
      	}
      	
  	  	$strBuffer .= '
    </tr>
  </thead>
  <tbody>
' . $strResults . '
  </tbody>
</table>
<script type="text/javascript">
<!--//--><![CDATA[//><!--' . "
window.addEvent('domready', function() {
  new TableLookupWizard('" . $this->strId . "');
});
" . '//--><!]]>
</script>';

		return $strBuffer;
	}
	
	
	public function generateAjax()
	{
		$arrKeywords = trimsplit(' ', $this->Input->post('keywords'));

		$strFilter = '';
		$arrProcedures = array();
		$arrValues = array();
		
		foreach( $arrKeywords as $keyword )
		{
			if (!strlen($keyword))
				continue;
				
			$arrProcedures[] .= implode(' LIKE ? OR ', $this->searchFields) . ' LIKE ?';
			$arrValues += array_fill(0, count($this->searchFields), '%'.$keyword.'%');
		}
		
		if (!count($arrProcedures))
			return '';
		
		$arrData = $this->Input->post($this->strName);
		if (is_array($arrData) && count($arrData))
		{
			$strFilter = ") AND id NOT IN (" . implode(',', $arrData);
		}
		
		$arrResults = $this->Database->prepare("SELECT id, " . implode(', ', $this->listFields) . " FROM {$this->foreignTable} WHERE (" . implode(' OR ', $arrProcedures) . $strFilter . ")" . (strlen($this->sqlWhere) ? " AND {$this->sqlWhere}" : ''))
									  ->execute($arrValues)
									  ->fetchAllAssoc();
									  
		$strBuffer = $this->listResults($arrResults, true);
		
		if (!strlen($strBuffer))
			return '<tr class="found empty"><td colspan="' . (count($this->listFields)+1) . '">' . sprintf($GLOBALS['TL_LANG']['MSC']['tlwNoResults'], $this->Input->post('keywords')) . '</td></tr>';
			
		return $strBuffer;
	}
	
	
	protected function listResults($arrResults, $blnAjax=false)
	{
		$c=0;
		$strResults = '';
		
		foreach( $arrResults as $row )
		{
			if (is_array($this->arrIds) && !in_array($row['id'], $this->arrIds))
				continue;
				
			$strResults .= '
    <tr class="' . ($c%2 ? 'even' : 'odd') . ($c==0 ? ' row_first' : '') . ($blnAjax ? ' found' : '') . '">
      <td class="col_0 col_first"><input type="checkbox" class="checkbox" name="' . $this->strId . '[]" value="' . $row['id'] . '"' . ($blnAjax ? '' : $this->optionChecked($row['id'], $this->varValue)) . ' /></td>';
      
      		$i = 1;
      		foreach( $row as $field => $value )
      		{
      			if ($field == 'id')
      				continue;
      				
      			$strResults .= '
      <td class="col_' . $i . '">' . $value . '</td>';
      
      			$i++;
      		}
      		
      		$strResults .= '
    </tr>';
    		
    		$c++;
		}
		
		return $strResults;
	}
}

