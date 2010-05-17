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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Philipp Kaiblinger <philipp.kaiblinger@kaipo.at>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeTranslation extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_isotope_translation';


	/**
	 * Generate module
	 */
	protected function compile()
	{
		$this->import('BackendUser', 'User');
		
		if (!strlen($this->User->translation))
			$this->redirect('typolight/main.php?act=error');
		
		$this->import('Session');
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_translation_filters')
		{
			$arrFilter['filter_translation']['isotope_translation'] = array
			(
				'module'	=> $this->Input->post('module'),
				'file'		=> $this->Input->post('file'),
			);
			
			$this->Session->appendData($arrFilter);
			
			$this->reload();
		}
		
		$arrSession = $this->Session->get('filter_translation');
		$arrSession = $arrSession['isotope_translation'];
		
		
		$this->Template->headline = $GLOBALS['TL_LANG']['MSC']['translationSelect'];
		$this->Template->class = 'tl_info';
		$this->Template->action = ampersand($this->Environment->request);
		$this->Template->slabel = $GLOBALS['TL_LANG']['MSC']['save'];
		
		
		// get modules		
		$arrModules = array();
		foreach( $this->Config->getActiveModules() as $module )
		{
			if (strpos($module, 'isotope') === false)
				continue;
				
			$arrModules[] = array('value'=>$module, 'label'=>$module,'default'=>($arrSession['module'] == $module ? true : false));
		}
		
		// get files
		$arrFiles = array();
		if(strlen($arrSession['module']))
		{
			if (!is_dir(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation))
			{
				$this->import('Files');
				$this->Files->mkdir('system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation);
			}
			
			$arrFileSearch = scan(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/');
		
			foreach ($arrFileSearch as $file)
			{
				$arrFiles[] = array('value'=>$file, 'label'=>$file, 'default'=>($arrSession['file'] == $file ? true : false));
			}	
		}
		
		
		if (is_file(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/' . $arrSession['file']))
		{
			$arrSource = $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/' . $arrSession['file']);
			
			if ($this->Input->post('FORM_SUBMIT') == 'isotope_translation')
			{
				$strFile = "<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @author     Automated Translation Tool
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

";
				
				foreach( $arrSource as $key => $value )
				{
					$value = trim($this->Input->postRaw(standardize($key)));
					
					if (!strlen($value))
						continue;
						
					$strFile .= $key . " = '" . str_replace("'", "\'", $value) . "';\n";
				}
				
				$strFile .= "\n";
				
				$objFile = new File('system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/' . $arrSession['file']);
				$objFile->write($strFile);
				$objFile->close();
				
				$_SESSION['TL_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['translationSaved'];
				$this->reload();
			}
			
			$this->Template->edit = true;
			$this->Template->source = $arrSource;
			$this->Template->translation = $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/' . $arrSession['file']);
			$this->Template->headline = sprintf($GLOBALS['TL_LANG']['MSC']['translationEdit'], $arrSession['file'], $arrSession['module']);
			
			if (!is_array($this->Template->translation) || !is_array($this->Template->source))
			{
				$this->Template->edit = false;
				$this->Template->error = $GLOBALS['TL_LANG']['MSC']['translationError'];
				$this->Template->headline = (is_array($this->Template->translation) ? $this->Template->source : $this->Template->translation);
				$this->Template->class = 'tl_error';
			}
		}
		
		$this->Template->modules = $arrModules;
		$this->Template->moduleClass = strlen($arrSession['module']) ? ' active' : '';
		$this->Template->files = $arrFiles;
		$this->Template->fileClass = $this->Template->edit ? ' active' : '';
	}
	
	
	private function parseFile($strFile)
	{
		$return = array();
				
		if (!is_file($strFile))
		{
			return array();
		}
		
		$data = file($strFile);
		
		foreach ($data as $i => $line)
		{
			// Unset comments and empty lines
			if ($i == 0 || preg_match('@^/\*| \*|\*/|//@i', $line) || !strlen(trim($line)))
			{
				continue;
			}
			
			// Store Language Variable
			if(preg_match('@\$GLOBALS(\[.*?\])*@', $line, $match))
			{
				$table = $match[0];
			}
			else
			{
				return 'Line ' . ++$i . ': ' . $line;
			}
			
			if(preg_match("@\=[ \t]*'(.*?(?<!\\\\))'@", $line, $match))
			{
				$return[$table] = $match[1];
			}
			elseif(preg_match('@\=[ \t]*"(.*?(?<!\\\\))"@', $line, $match))
			{	
				$return[$table] = $match[1];
			}
			
			elseif(preg_match('@\=[ \t]*array\((.*?)\)[ \t]*;@', $line, $match))
			{	
				$vars = trimsplit('([\'"] *, *)', $match[1]);
				
				foreach( $vars as $key => $var )
				{
					$kv = trimsplit('=>', $var);
					
					if (count($kv) > 1)
					{
						$key = trim($kv[0]);
						$var = $kv[1];
					}
					
					$var = trim($var, '\'" ');
					
					if (strlen($var))
					{
						$return[$table . "[".$key."]"] = $var;
					}
				}
			}
		}
		
		return $return;
	}
}

