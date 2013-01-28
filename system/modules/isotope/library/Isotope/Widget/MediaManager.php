<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Widget;


/**
 * Class MediaManager
 *
 * Provide methods to handle media files.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class MediaManager extends \Widget implements \uploadable
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
     * Instance of the file uploader
     * @var object
     */
    protected $objUploader;


    /**
     * Instantiate widget and initialize uploader
     */
    public function __construct($arrAttributes=false)
    {
        parent::__construct($arrAttributes);

        $this->import('Database');

        $this->objUploader = \Isotope\Backend::getUploader();
    }


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

        if (!is_array($this->varValue))
        {
            $this->varValue = array();
        }

        // Prepare system for the upload
        $arrAllowedTypes = $GLOBALS['TL_CONFIG']['uploadTypes'];

        if ($this->extensions != '')
        {
            $GLOBALS['TL_CONFIG']['uploadTypes'] = $this->extensions;
        }

        // Process the uploaded files
        $arrUploaded = $this->objUploader->uploadTo('isotope', 'files');

        // Reset system configuration
        $GLOBALS['TL_CONFIG']['uploadTypes'] = $arrAllowedTypes;

        // Fetch fallback language record
		$arrFallback = $this->getFallbackData();

		if (is_array($arrFallback))
		{
    		foreach ($arrFallback as $k => $arrImage)
    		{
    		    if ($arrImage['translate'] == 'all')
    		    {
        			unset($arrFallback[$k]);
                }
    		}
        }

        // Save file in the isotope folder
        if (!empty($arrUploaded))
        {
            $this->import('Files');

            foreach ($arrUploaded as $strFile)
            {
                $pathinfo = pathinfo(strtolower($strFile));
                $strCacheName = standardize($pathinfo['filename']) . '.' . $pathinfo['extension'];
                $uploadFolder = 'isotope/' . substr($strCacheName, 0, 1);

                if (is_file(TL_ROOT . '/' . $uploadFolder . '/' . $strCacheName) && md5_file(TL_ROOT . '/' . $strFile) != md5_file(TL_ROOT . '/' . $uploadFolder . '/' . $strCacheName))
                {
                    $strCacheName = standardize($pathinfo['filename']) . '-' . substr(md5_file(TL_ROOT . '/' . $strFile), 0, 8) . '.' . $pathinfo['extension'];
                    $uploadFolder = 'isotope/' . substr($strCacheName, 0, 1);
                }

                // Check that image is not assigned in fallback language
				if (is_array($arrFallback) && in_array($strCacheName, $arrFallback))
				{
    				$this->addError($GLOBALS['ISO_LANG']['ERR']['imageInFallback']);
				}
				else
				{
                    // Make sure directory exists
                    $this->Files->mkdir($uploadFolder);
                    $this->Files->rename($strFile, $uploadFolder . '/' . $strCacheName);

                    $this->varValue[] = array('src'=>$strCacheName, 'translate'=>($arrFallback === false ? '' : 'all'));
                }
            }
        }

        if ($this->mandatory)
        {
            foreach ($this->varValue as $file)
            {
                if (is_file(TL_ROOT . '/isotope/' . substr($file['src'], 0, 1) . '/' . $file['src']))
                {
                    return;
                }
            }

            if (!is_array($arrFallback) || empty($arrFallback))
			{
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }
        }
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $arrFallback = $this->getFallbackData();

        // Merge parent record data
        if ($arrFallback !== false)
        {
            $blnLanguage = true;

            $this->import('Isotope\Isotope', 'Isotope');
            $this->varValue = $this->Isotope->mergeMediaData($this->varValue, $arrFallback);
        }

        $GLOBALS['TL_CSS'][] = TL_PLUGINS_URL . 'plugins/mediabox/'. MEDIABOX .'/css/mediaboxAdvBlack21.css|screen';
        $GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'plugins/mediabox/' . MEDIABOX . '/js/mediabox.js';
        $GLOBALS['TL_JAVASCRIPT'][] = TL_PLUGINS_URL . 'system/modules/isotope/assets/mediabox_init.js';

        $arrButtons = array('up', 'down', 'delete');
        $strCommand = 'cmd_' . $this->strField;

        // Change the order
        if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord)
        {
            switch (\Input::get($strCommand))
            {
                case 'up':
                    $this->varValue = array_move_up($this->varValue, \Input::get('cid'));
                    break;

                case 'down':
                    $this->varValue = array_move_down($this->varValue, \Input::get('cid'));
                    break;

                case 'delete':
                    $this->varValue = array_delete($this->varValue, \Input::get('cid'));
                    break;
            }

            $this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
                           ->execute(serialize($this->varValue), $this->currentRecord);

            $this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
        }

        $upload = sprintf('<h3><label for="ctrl_%s_upload">%s</label></h3>' . $this->objUploader->generateMarkup(),
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

            $objFile = new \File($strFile);

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
        '.($blnLanguage ? ('<input type="hidden" name="' . $this->strName . '['.$i.'][translate]" value="'.$this->varValue[$i]['translate'].'">') : '').'
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


	/**
	 * Retrieve image data from fallback language
	 * @return array|false
	 */
	protected function getFallbackData()
	{
		// Fetch fallback language record
		if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->currentRecord] != '')
		{
			return deserialize($this->Database->execute("SELECT * FROM {$this->strTable} WHERE id={$this->currentRecord}")->{$this->strField});
		}

		return false;
	}
}
