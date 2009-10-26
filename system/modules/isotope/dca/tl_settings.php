<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Palettes
 */
//$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{isotope_settings},isotope_base_path'; 


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['isotope_base_path'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['isotope_base_path'],
	'default'				  => 'assets',
	'inputType'               => 'text',
	'eval'					  => array('rgxp'=>'extnd'),
	'save_callback'			  => array
	(
		array('FileLock','lockField')
	)
);


/**
 * FileLock class.
 * 
 * @extends Backend
 */
class FileLock extends Backend
{
	
	/**
	 * lockField function.
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
	public function lockField($varValue, DataContainer $dc)
	{				
		if(!is_dir(TL_ROOT . '/' . 'isotope'))
		{
			
			if(!isset($GLOBALS['TL_CONFIG']['isotope_root']))
			{
				$this->Config->add("\$GLOBALS['TL_CONFIG']['isotope_root']", TL_ROOT . '/isotope');
			}
			
			if(!isset($GLOBALS['TL_CONFIG']['isotope_upload_path']))
			{
				$this->Config->add("\$GLOBALS['TL_CONFIG']['isotope_upload_path']", 'isotope');
			}
			
			new Folder('isotope');
		}	
		
		if($GLOBALS['TL_CONFIG']['is_locked']!=1)
		{			
			
			$this->Config->add("\$GLOBALS['TL_CONFIG']['is_locked']",'1');
									
			//Default to standard folder name			
			if(strlen($varValue)<1)
			{
				$strFolderName = $GLOBALS['TL_LANG']['MSC']['defaultAssetsBasePath'];
			}else{
				$strFolderName = $varValue;
			}
			
			//Default assets base path - all subfolders are directly related to products.				
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strFolderName))
			{
				new Folder('isotope' . '/' . $strFolderName);
				//$this->Files->chmod('isotope' . '/' . $strFolderName . '/', 0755);
			}
			
			//The Default Import Folder for Isotope
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath']))
			{
				new Folder('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath']);
				
				//$this->Files->chmod('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/', 0755);
			}
			
			//The Default Import Folder for Isotope images
			/*
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $GLOBALS['TL_LANG']['MSC']['images']))
			{
				new Folder('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder']);
				//$this->Files->chmod('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . 'images' . '/', 0755);
			}
			
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $GLOBALS['TL_LANG']['MSC']['audioFolder']))
			{
				new Folder('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $GLOBALS['TL_LANG']['MSC']['audioFolder']);
				//$this->Files->chmod('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . 'audio' . '/', 0755);
			}
			
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $GLOBALS['TL_LANG']['MSC']['videoFolder']))
			{
				new Folder('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $GLOBALS['TL_LANG']['MSC']['videoFolder']);
				//$this->Files->chmod('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . 'video' . '/', 0755);
			}
			*/
			
			return $strFolderName;
		}
		else
		{
			return $varValue;		
		}
	}
}

