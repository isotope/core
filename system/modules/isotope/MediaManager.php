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


class MediaManager extends Widget implements uploadable
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
			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;
				
			case 'value':
				$this->varValue = deserialize($varValue);
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
		$this->varValue = $this->getPost($this->strName);
		
		// No file specified
		if (!isset($_FILES[$this->strName]) || empty($_FILES[$this->strName]['name']))
		{			
			if ($this->mandatory)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			}

			return;
		}

		$file = $_FILES[$this->strName];
		$maxlength_kb = number_format(($GLOBALS['TL_CONFIG']['maxFileSize']/1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']);

		// Romanize the filename
		$file['name'] = utf8_romanize($file['name']);

		// File was not uploaded
		if (!is_uploaded_file($file['tmp_name']))
		{			
			if (in_array($file['error'], array(1, 2)))
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $maxlength_kb));
				$this->log('File "'.$file['name'].'" exceeds the maximum file size of '.$maxlength_kb.' kB', 'FormFileUpload validate()', TL_ERROR);
			}

			if ($file['error'] == 3)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filepartial'], $file['name']));
				$this->log('File "'.$file['name'].'" was only partially uploaded', 'FormFileUpload validate()', TL_ERROR);
			}

			unset($_FILES[$this->strName]);
			return;
		}

		// File is too big
		if ($GLOBALS['TL_CONFIG']['maxFileSize'] > 0 && $file['size'] > $GLOBALS['TL_CONFIG']['maxFileSize'])
		{			
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $maxlength_kb));
			$this->log('File "'.$file['name'].'" exceeds the maximum file size of '.$maxlength_kb.' kB', 'FormFileUpload validate()', TL_ERROR);

			unset($_FILES[$this->strName]);
			return;
		}

		$pathinfo = pathinfo($file['name']);
		$uploadTypes = trimsplit(',', $GLOBALS['TL_CONFIG']['uploadTypes']);

		// File type is not allowed
		if (!in_array(strtolower($pathinfo['extension']), $uploadTypes))
		{			
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $pathinfo['extension']));
			$this->log('File type "'.$pathinfo['extension'].'" is not allowed to be uploaded ('.$file['name'].')', 'FormFileUpload validate()', TL_ERROR);

			unset($_FILES[$this->strName]);
			return;
		}

		if (($arrImageSize = @getimagesize($file['tmp_name'])) != false)
		{
			// Image exceeds maximum image width
			if ($arrImageSize[0] > $GLOBALS['TL_CONFIG']['imageWidth'] || $arrImageSize[0] > 3000)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], $file['name'], $GLOBALS['TL_LANG']['MSC']['scalingImageWidth']));
				$this->log('File "'.$file['name'].'" exceeds the maximum image width of '.$GLOBALS['TL_LANG']['MSC']['scalingImageWidth'].' pixels', 'FormFileUpload validate()', TL_ERROR);

				unset($_FILES[$this->strName]);
				return;
			}

			// Image exceeds maximum image height
			if ($arrImageSize[1] > $GLOBALS['TL_CONFIG']['imageHeight'] || $arrImageSize[1] > 3000)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], $file['name'], $GLOBALS['TL_LANG']['MSC']['scalingImageHeight']));
				$this->log('File "'.$file['name'].'" exceeds the maximum image height of '.$GLOBALS['TL_LANG']['MSC']['scalingImageHeight'].' pixels', 'FormFileUpload validate()', TL_ERROR);

				unset($_FILES[$this->strName]);
				return;
			}
		}

		// Store file in the isotope folder
		if (!$this->hasErrors())
		{			
			$this->import('Files');
			$this->import('Database');
			
			$pathinfo = pathinfo($file['name']);
		
			// Make sure directory exists
			$this->Files->mkdir('isotope/' . substr($pathinfo['basename'], 0, 1) . '/');
			
			$strCacheName = $pathinfo['basename'] . '-' . substr(md5_file($file['tmp_name']), 0, 8) . '.' . $pathinfo['extension'];
			
			$this->Files->move_uploaded_file($file['tmp_name'], 'isotope/' . substr($pathinfo['basename'], 0, 1) . '/' . $strCacheName);
			
			if (!is_array($this->varValue))
			{
				$this->varValue = array();
			}
			
			$this->varValue[] = array('src'=>$strCacheName);
		}
		
		unset($_FILES[$this->strName]);
    }
    
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$this->import('Database');
		
		$GLOBALS['TL_CSS'][] = 'plugins/slimbox/css/slimbox.css';
		$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/backend.css';
		$GLOBALS['TL_JAVASCRIPT'][] = 'plugins/slimbox/js/slimbox.js';
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/backend.js';

		
		$arrButtons = array('up', 'down', 'delete');
		$strCommand = 'cmd_' . $this->strField;

		// Change the order
		if ($this->Input->get($strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{
			switch ($this->Input->get($strCommand))
			{
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
			
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						   ->execute(serialize($this->varValue), $this->currentRecord);
						   
			$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
		}

		$upload = sprintf('<h3><label for="ctrl_%s_upload">%s</label></h3><p><input type="file" name="%s" id="ctrl_%s_upload" class="upload%s" /></p>',
						$this->strId,
						$GLOBALS['TL_LANG']['MSC']['mmUploadImage'],
						$this->strName,
						$this->strId,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''));

		if (!is_array($this->varValue) || !count($this->varValue))
			return $GLOBALS['TL_LANG']['MSC']['mmNoImagesUploaded'] . $upload;

		// Add label and return wizard
		$return = '<table cellspacing="0" cellpadding="0" id="ctrl_'.$this->strId.'" class="tl_mediamanager" summary="Media Manager">
  <thead>
  <tr>
    <td>'.$GLOBALS['TL_LANG'][$this->strTable]['mmSrc'].'</td>
    <td>'.$GLOBALS['TL_LANG'][$this->strTable]['mmAlt'].'</td>
    <td>'.$GLOBALS['TL_LANG'][$this->strTable]['mmDesc'].'</td>
    <td>&nbsp;</td>
  </tr>
  </thead>
  <tbody>';

		// Add input fields
		for ($i=0; $i<count($this->varValue); $i++)
		{
			$strImage = 'isotope/' . substr($this->varValue[$i]['src'], 0, 1) . '/' . $this->varValue[$i]['src'];

			if (!is_file(TL_ROOT . '/' . $strImage))
			{								
				continue;
			}
			$return .= '
  <tr>
    <td><input type="hidden" name="' . $this->strName . '['.$i.'][src]" value="' . $this->varValue[$i]['src'] . '" /><a href="' . $strImage . '" rel="lightbox"><img src="' . $this->getImage($strImage, 50, 50) . '" alt="' . $this->varValue[$i]['src'] . '" /></a></td>
    <td><input type="text" class="tl_text_2" name="' . $this->strName . '['.$i.'][alt]" value="' . $this->varValue[$i]['alt'] . '" /></td>
    <td><textarea name="' . $this->strName . '['.$i.'][desc]" cols="40" rows="3" class="tl_textarea">' . $this->varValue[$i]['desc'] . '</textarea></td>
    <td>';

			foreach ($arrButtons as $button)
			{
				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button]).'" onclick="Isotope.mediaManager(this, \''.$button.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button], 'class="tl_listwizard_img"').'</a> ';
			}

			$return .= '</td>
  </tr>';
		}

		return $return.'
  </tbody>
  </table>' . $upload;
	}
}


