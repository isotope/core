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


/**
 * Class MediaManager
 * Provide methods to handle media files.
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
	 * @return void
	 */
	public function validate()
	{
		$this->varValue = $this->getPost($this->strName);

		// No file specified
		if (!isset($_FILES[$this->strName]) || empty($_FILES[$this->strName]['name']))
		{
			if ($this->mandatory)
			{
				if (is_array($this->varValue))
				{
					foreach ($this->varValue as $file)
					{
						if (is_file(TL_ROOT . '/isotope/' . substr($file['src'], 0, 1) . '/' . $file['src']))
						{
							return;
						}
					}
				}

				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			}

			return;
		}

		$this->import('Files');
		$this->import('Database');

		$files = $_FILES[$this->strName];
		$maxlength_kb = number_format(($GLOBALS['TL_CONFIG']['maxFileSize']/1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']);

		for ($i=0; $i<count($files['name']); $i++)
		{
			// Generate the $file entry for the following routine
			$file = array
			(
				'name'     => $files['name'][$i],
				'type'     => $files['type'][$i],
				'tmp_name' => $files['tmp_name'][$i],
				'error'    => $files['error'][$i],
				'size'     => $files['size'][$i]
			);

			// Romanize the filename
			$file['name'] = utf8_romanize($file['name']);

			// File was not uploaded
			if (!is_uploaded_file($file['tmp_name']))
			{
				if (in_array($file['error'], array(1, 2)))
				{
					$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $maxlength_kb));
					$this->log('File "'.$file['name'].'" exceeds the maximum file size of '.$maxlength_kb.' kB', __METHOD__, TL_ERROR);
				}

				if ($file['error'] == 3)
				{
					$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filepartial'], $file['name']));
					$this->log('File "'.$file['name'].'" was only partially uploaded', __METHOD__, TL_ERROR);
				}

				continue;
			}

			// File is too big
			if ($GLOBALS['TL_CONFIG']['maxFileSize'] > 0 && $file['size'] > $GLOBALS['TL_CONFIG']['maxFileSize'])
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $maxlength_kb));
				$this->log('File "'.$file['name'].'" exceeds the maximum file size of '.$maxlength_kb.' kB', __METHOD__, TL_ERROR);
				continue;
			}

			$pathinfo = pathinfo($file['name']);
			$uploadTypes = trimsplit(',', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['extensions']);

			// File type is not allowed
			if (!in_array(strtolower($pathinfo['extension']), $uploadTypes))
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $pathinfo['extension']));
				$this->log('File type "'.$pathinfo['extension'].'" is not allowed to be uploaded ('.$file['name'].')', __METHOD__, TL_ERROR);
				continue;
			}

			if (($arrImageSize = @getimagesize($file['tmp_name'])) != false)
			{
				// Image exceeds maximum image width
				if ($arrImageSize[0] > $GLOBALS['TL_CONFIG']['imageWidth'] || $arrImageSize[0] > $GLOBALS['TL_CONFIG']['gdMaxImgWidth'])
				{
					$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], $file['name'], $GLOBALS['TL_CONFIG']['imageWidth']));
					$this->log('File "'.$file['name'].'" exceeds the maximum image width of '.$GLOBALS['TL_CONFIG']['imageWidth'].' pixels', __METHOD__, TL_ERROR);
					continue;
				}

				// Image exceeds maximum image height
				if ($arrImageSize[1] > $GLOBALS['TL_CONFIG']['imageHeight'] || $arrImageSize[1] > $GLOBALS['TL_CONFIG']['gdMaxImgHeight'])
				{
					$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], $file['name'], $GLOBALS['TL_CONFIG']['imageHeight']));
					$this->log('File "'.$file['name'].'" exceeds the maximum image height of '.$GLOBALS['TL_CONFIG']['imageHeight'].' pixels', __METHOD__, TL_ERROR);
					continue;
				}
			}

			// No error if we get the, so save file in the isotope folder
			$pathinfo = pathinfo($file['name']);
			$strCacheName = standardize($pathinfo['filename']) . '.' . $pathinfo['extension'];
			$uploadFolder = 'isotope/' . substr($strCacheName, 0, 1);

			if (is_file(TL_ROOT . '/' . $uploadFolder . '/' . $strCacheName) && md5_file($file['tmp_name']) != md5_file(TL_ROOT . '/' . $uploadFolder . '/' . $strCacheName))
			{
				$strCacheName = standardize($pathinfo['filename']) . '-' . substr(md5_file($file['tmp_name']), 0, 8) . '.' . $pathinfo['extension'];
				$uploadFolder = 'isotope/' . substr($strCacheName, 0, 1);
			}

			// Make sure directory exists
			$this->Files->mkdir($uploadFolder);
			$this->Files->move_uploaded_file($file['tmp_name'], $uploadFolder . '/' . $strCacheName);

			if (!is_array($this->varValue))
			{
				$this->varValue = array();
			}

			$this->varValue[] = array('src'=>$strCacheName, 'translate'=>(!$_SESSION['BE_DATA']['language'][$this->strTable][$this->currentRecord] ? '' : 'all'));
		}

		unset($_FILES[$this->strName]);
    }


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$blnLanguage = false;
		$this->import('Database');

		// Merge parent record data
		if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->currentRecord] != '')
		{
			$blnLanguage = true;
			$objParent = $this->Database->execute("SELECT * FROM {$this->strTable} WHERE id={$this->currentRecord}");
			$arrParent = deserialize($objParent->{$this->strField});

			$this->import('Isotope');
			$this->varValue = $this->Isotope->mergeMediaData($this->varValue, $arrParent);
		}

		$GLOBALS['TL_CSS'][] = TL_PLUGINS_URL . 'plugins/mediabox/'. MEDIABOX .'/css/mediaboxAdvBlack21.css|screen';
		$GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'plugins/mediabox/' . MEDIABOX . '/js/mediabox.js';
		$GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'system/modules/isotope/html/mediabox_init.js';

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

		$upload = sprintf('<h3><label for="ctrl_%s_upload">%s</label></h3><p><input type="file" name="%s[]" id="ctrl_%s_upload" class="upload%s" multiple></p>',
						$this->strId,
						$GLOBALS['TL_LANG']['MSC']['mmUpload'],
						$this->strName,
						$this->strId,
						(strlen($this->strClass) ? ' ' . $this->strClass : ''));

		$return = '<div id="ctrl_' . $this->strId . '">';

		if (!is_array($this->varValue) || empty($this->varValue))
		{
			return $return . $GLOBALS['TL_LANG']['MSC']['mmNoUploads'] . $upload . '</div>';
		}

		// Add label and return wizard
		$return .= '<table class="tl_mediamanager">
  <thead>
  <tr>
    <td class="col_0 col_first">'.$GLOBALS['TL_LANG'][$this->strTable]['mmSrc'].'</td>
    <td class="col_1">'.$GLOBALS['TL_LANG'][$this->strTable]['mmAlt'].' / '.$GLOBALS['TL_LANG'][$this->strTable]['mmLink'].'</td>
    <td class="col_2">'.$GLOBALS['TL_LANG'][$this->strTable]['mmDesc'].'</td>
    <td class="col_3">'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslate'].'</td>
    <td class="col_4 col_last">&nbsp;</td>
  </tr>
  </thead>
  <tbody>';

		// Add input fields
		for ($i=0, $count=count($this->varValue); $i<$count; $i++)
		{
			$strFile = 'isotope/' . strtolower(substr($this->varValue[$i]['src'], 0, 1)) . '/' . $this->varValue[$i]['src'];

			if (!is_file(TL_ROOT . '/' . $strFile))
			{
				continue;
			}

			$objFile = new File($strFile);

			if ($objFile->isGdImage)
			{
				$strPreview = $this->getImage($strFile, 50, 50, 'box');
			}
			else
			{
				$strPreview = 'system/themes/' . $this->getTheme() . '/images/' . $objFile->icon;
			}

			$strTranslateText = ($blnLanguage && $this->varValue[$i]['translate'] != 'all') ? ' disabled="disabled"' : '';
			$strTranslateNone = ($blnLanguage && !$this->varValue[$i]['translate']) ? ' disabled="disabled"' : '';

			$return .= '
  <tr>
    <td class="col_0 col_first"><input type="hidden" name="' . $this->strName . '['.$i.'][src]" value="' . specialchars($this->varValue[$i]['src']) . '"><a href="' . $strFile . '" rel="lightbox"><img src="' . $strPreview . '" alt="' . specialchars($this->varValue[$i]['src']) . '"></a></td>
    <td class="col_1"><input type="text" class="tl_text_2" name="' . $this->strName . '['.$i.'][alt]" value="' . specialchars($this->varValue[$i]['alt'], true) . '"'.$strTranslateNone.'><br><input type="text" class="tl_text_2" name="' . $this->strName . '['.$i.'][link]" value="' . specialchars($this->varValue[$i]['link'], true) . '"'.$strTranslateText.'></td>
    <td class="col_2"><textarea name="' . $this->strName . '['.$i.'][desc]" cols="40" rows="3" class="tl_textarea"'.$strTranslateNone.' >' . specialchars($this->varValue[$i]['desc']) . '</textarea></td>
    <td class="col_3">
    	'.($blnLanguage ? ('<input type="hidden" name="' . $this->strName . '['.$i.'][translate]" value="'.$this->varValue[$i]['translate'].'"') : '').'
    	<fieldset class="radio_container">
	    	<span>
	    		<input id="' . $this->strName . '_'.$i.'_translate_none" name="' . $this->strName . '['.$i.'][translate]" type="radio" class="tl_radio" value=""'.$this->optionChecked('', $this->varValue[$i]['translate']).($blnLanguage ? ' disabled="disabled"' : '').'>
	    		<label for="' . $this->strName . '_'.$i.'_translate_none" title="'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslateNone'][1].'">'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslateNone'][0].'</label></span>
	    	<span>
	    		<input id="' . $this->strName . '_'.$i.'_translate_text" name="' . $this->strName . '['.$i.'][translate]" type="radio" class="tl_radio" value="text"'.$this->optionChecked('text', $this->varValue[$i]['translate']).($blnLanguage ? ' disabled="disabled"' : '').'>
	    		<label for="' . $this->strName . '_'.$i.'_translate_text" title="'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslateText'][1].'">'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslateText'][0].'</label></span>
	    	<span>
	    		<input id="' . $this->strName . '_'.$i.'_translate_all" name="' . $this->strName . '['.$i.'][translate]" type="radio" class="tl_radio" value="all"'.$this->optionChecked('all', $this->varValue[$i]['translate']).($blnLanguage ? ' disabled="disabled"' : '').'>
	    		<label for="' . $this->strName . '_'.$i.'_translate_all" title="'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslateAll'][1].'">'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslateAll'][0].'</label></span>
    	</fieldset>
    </td>
    <td class="col_4 col_last">';

			foreach ($arrButtons as $button)
			{
				if ($button == 'delete' && $blnLanguage && $this->varValue[$i]['translate'] != 'all')
				{
					continue;
				}

				$return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button]).'" onclick="Isotope.mediaManager(this, \''.$button.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.$this->generateImage($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button], 'class="tl_listwizard_img"').'</a> ';
			}

			$return .= '</td>
  </tr>';
		}

		return $return.'
  </tbody>
  </table>' . $upload . '</div>';
	}
}