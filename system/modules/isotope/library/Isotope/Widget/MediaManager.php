<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Widget;

use Haste\Util\Debug;
use Isotope\Model\Gallery;


/**
 * Class MediaManager
 *
 * Provide methods to handle media files.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
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
     * Temporary folder for uploads
     * @var string
     */
    protected $strTempFolder = 'assets/images';

    /**
     * Instantiate widget and initialize uploader
     *
     * @param array $arrAttributes
     */
    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);
        $GLOBALS['TL_JAVASCRIPT']['fineuploader'] = Debug::uncompressedFile('system/modules/isotope/assets/plugins/fineuploader/fineuploader-4.0.3.min.js');
    }

    /**
     * Add specific attributes
     *
     * @param string $strKey
     * @param mixed  $varValue
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
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
     * Handle uploads from fineuploader.
     * Unfortunately, IE does use iframes and not ajax, so postAjax hook does not always work
     */
    public function ajaxUpload()
    {
        $strFile = $this->validateUpload();

        if ($this->hasErrors()) {
            $arrResponse = array('success' => false, 'error' => $this->getErrorAsString(), 'preventRetry' => true);
        } else {
            $arrResponse = array('success' => true, 'file' => $strFile);
        }

        // Can't use Haste\Response\JsonResponse, it triggers a json download in IE iframes
        while(ob_end_clean());
        echo json_encode($arrResponse);
        exit;
    }

    /**
     * Validate the upload
     *
     * @return string
     */
    public function validateUpload()
    {
        \Message::reset();

        $objUploader = new \FileUpload();
        $objUploader->setName($this->strName);
        $uploadFolder = $this->strTempFolder;

        // Convert the $_FILES array to Contao format
        if (!empty($_FILES[$this->strName])) {
            $pathinfo = pathinfo(strtolower($_FILES[$this->strName]['name']));
            $strCacheName = standardize($pathinfo['filename']) . '.' . $pathinfo['extension'];
            $uploadFolder = $this->strTempFolder . '/' . substr($strCacheName, 0, 1);

            if (is_file(TL_ROOT . '/' . $uploadFolder . '/' . $strCacheName)
                && md5_file(TL_ROOT . '/' .  $uploadFolder . '/' . $_FILES[$this->strName]['name']) != md5_file(TL_ROOT . '/' . $uploadFolder . '/' . $strCacheName)
            ) {
                $strCacheName = standardize($pathinfo['filename']) . '-' . substr(md5_file(TL_ROOT . '/' .  $uploadFolder . '/' . $_FILES[$this->strName]['name']), 0, 8) . '.' . $pathinfo['extension'];
                $uploadFolder = $this->strTempFolder . '/' . substr($strCacheName, 0, 1);
            }

            \Haste\Haste::mkdirr($uploadFolder);
            $arrFallback = $this->getFallbackData();

            // Check that image is not assigned in fallback language
            if (is_array($arrFallback) && in_array($strCacheName, $arrFallback)) {
                $this->addError($GLOBALS['TL_LANG']['ERR']['imageInFallback']);
            }

            $_FILES[$this->strName] = array(
                'name'     => array($strCacheName),
                'type'     => array($_FILES[$this->strName]['type']),
                'tmp_name' => array($_FILES[$this->strName]['tmp_name']),
                'error'    => array($_FILES[$this->strName]['error']),
                'size'     => array($_FILES[$this->strName]['size']),
            );
        }

        $varInput = '';

        try {
            $varInput = $objUploader->uploadTo($uploadFolder);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        if ($objUploader->hasError()) {
            foreach ($_SESSION['TL_ERROR'] as $strError) {
                $this->addError($strError);
            }
        }

		\Message::reset();

        if (!is_array($varInput) || empty($varInput)) {
            $this->addError($GLOBALS['TL_LANG']['MSC']['mmUnknownError']);
        }

        return $varInput[0];
    }

    /**
     * Validate input and set value
     */
    public function validate()
    {
        $this->varValue = $this->getPost($this->strName);

        if (!is_array($this->varValue)) {
            $this->varValue = array();
        }

        // Fetch fallback language record
        $arrFallback = $this->getFallbackData();

        if (is_array($arrFallback)) {
            foreach ($arrFallback as $k => $arrImage) {
                if ($arrImage['translate'] == 'all') {
                    unset($arrFallback[$k]);
                }
            }
        }

        // Check that image is not assigned in fallback language
        foreach ($this->varValue as $k => $v) {
            if (is_array($arrFallback) && in_array($v, $arrFallback)) {
                $this->addError($GLOBALS['TL_LANG']['ERR']['imageInFallback']);
            } elseif ($arrFallback !== false) {
                $this->varValue[$k]['translate'] = 'all';
            }
        }

        // Move all temporary files
        foreach ($this->varValue as $k => $v) {
            if (stripos($v['src'], $this->strTempFolder) !== false) {
                $strFile = $this->getFilePath(basename($v['src']));

                if (is_file(TL_ROOT . '/' . $strFile)
                    && md5_file(TL_ROOT . '/' .  $v['src']) != md5_file(TL_ROOT . '/' . $strFile)
                ) {
                    $pathinfo = pathinfo($v['src']);
                    $strFile = $this->getFilePath(
                        standardize($pathinfo['filename']) . '-' . substr(md5_file(TL_ROOT . '/' .  $strFile), 0, 8) . '.' . $pathinfo['extension']
                    );
                }

                \Haste\Haste::mkdirr(dirname($strFile));

                if (\Files::getInstance()->rename($v['src'], $strFile)) {
                    $this->varValue[$k]['src'] = basename($strFile);
                } else {
                    unset($this->varValue[$k]);
                }
            }
        }

        // Check if there are values
        if ($this->mandatory) {
            foreach ($this->varValue as $file) {
                if (is_file(TL_ROOT . '/' . $this->getFilePath($file['src']))) {
                    return;
                }
            }

            if (!is_array($arrFallback) || empty($arrFallback)) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }
        }

        if (empty($this->varValue)) {
            $this->varValue = null;
        }
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        if (\Input::post('action') == 'uploadMediaManager') {
            $this->ajaxUpload();
        }

        $blnLanguage = false;
        $arrFallback = $this->getFallbackData();

        // Adapt the temporary files
        if (is_array($this->varValue['files']) && !empty($this->varValue['files'])) {
            foreach ($this->varValue['files'] as $v) {
                if (!is_file(TL_ROOT . '/' . $this->getFilePath($v))) {
                    continue;
                }

                $this->varValue[] = array(
                    'src'       => $v,
                    'alt'       => '',
                    'desc'      => '',
                    'link'      => '',
                    'translate' => ''
                );
            }

            unset($this->varValue['files']);
        }

        // Merge parent record data
        if ($arrFallback !== false) {
            $blnLanguage    = true;
            $this->varValue = Gallery::mergeMediaData($this->varValue, $arrFallback);
        }

        $arrButtons = array('up', 'down', 'delete', 'drag');
        $strCommand = 'cmd_' . $this->strField;

        // Change the order
        if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord) {
            switch (\Input::get($strCommand)) {
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

            \Database::getInstance()->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
                                    ->execute(serialize($this->varValue), $this->currentRecord);

            \Controller::redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
        }

        $blnIsAjax = \Environment::get('isAjaxRequest');
        $return = '';
        $upload = '';

        if (!$blnIsAjax) {
            $return .= '<div id="ctrl_' . $this->strId . '" class="tl_mediamanager">';
            $extensions = trimsplit(',', $GLOBALS['TL_CONFIG']['validImageTypes']);

            $upload .= '<div id="fineuploader_'.$this->strId.'" class="upload_container"></div>
  <script>
    window.addEvent("domready", function() {
      Isotope.MediaManager.init($("fineuploader_'.$this->strId.'"), "'.$this->strId.'", '.json_encode($extensions).');
    });
  </script>
  <script type="text/template" id="qq-template">
    <div class="qq-uploader-selector qq-uploader">
        <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
            <span>'.$GLOBALS['TL_LANG']['MSC']['mmDrop'].'</span>
        </div>
        <div class="qq-upload-button-selector qq-upload-button">
            <div class="tl_submit">'.$GLOBALS['TL_LANG']['MSC']['mmUpload'].'</div>
        </div>
        <span class="qq-drop-processing-selector qq-drop-processing">
            <span>'.$GLOBALS['TL_LANG']['MSC']['mmProcessing'].'</span>
            <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
        </span>
        <ul class="qq-upload-list-selector qq-upload-list">
            <li>
                <div class="qq-progress-bar-container-selector">
                    <div class="qq-progress-bar-selector qq-progress-bar"></div>
                </div>
                <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
                <span class="qq-edit-filename-icon-selector qq-edit-filename-icon"></span>
                <span class="qq-upload-file-selector qq-upload-file"></span>
                <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
                <span class="qq-upload-size-selector qq-upload-size"></span>
                <span class="qq-upload-status-text-selector qq-upload-status-text"></span>
            </li>
        </ul>
    </div>
  </script>';
        }

        $return .= '<div>';

        if (!is_array($this->varValue) || empty($this->varValue)) {
            return $return . $GLOBALS['TL_LANG']['MSC']['mmNoUploads'] . '</div>' . $upload . (!$blnIsAjax ? '</div>' : '');
        }

        // Add label and return wizard
        $return .= '<table>
  <thead>
  <tr>
    <td class="col_0 col_first">'.$GLOBALS['TL_LANG'][$this->strTable]['mmSrc'].'</td>
    <td class="col_1">'.$GLOBALS['TL_LANG'][$this->strTable]['mmAlt'].' / '.$GLOBALS['TL_LANG'][$this->strTable]['mmLink'].'</td>
    <td class="col_2">'.$GLOBALS['TL_LANG'][$this->strTable]['mmDesc'].'</td>
    <td class="col_3">'.$GLOBALS['TL_LANG'][$this->strTable]['mmTranslate'].'</td>
    <td class="col_4 col_last">&nbsp;</td>
  </tr>
  </thead>
  <tbody class="sortable">';

        // Add input fields
        for ($i=0, $count=count($this->varValue); $i<$count; $i++) {
            $strFile = $this->getFilePath($this->varValue[$i]['src']);

            if (!is_file(TL_ROOT . '/' . $strFile)) {
                continue;
            }

            $objFile = new \File($strFile);

            if ($objFile->isGdImage) {
                $strPreview = \Image::get($strFile, 50, 50, 'box');
            } else {
                $strPreview = 'system/themes/' . $this->getTheme() . '/images/' . $objFile->icon;
            }

            $strTranslateText = ($blnLanguage && $this->varValue[$i]['translate'] != 'all') ? ' disabled="disabled"' : '';
            $strTranslateNone = ($blnLanguage && $this->varValue[$i]['translate'] == 'none') ? ' disabled="disabled"' : '';

            $return .= '
  <tr>
    <td class="col_0 col_first"><input type="hidden" name="' . $this->strName . '['.$i.'][src]" value="' . specialchars($this->varValue[$i]['src']) . '"><a href="' . $strFile . '" onclick="Backend.openModalImage({\'width\':' . $objFile->width . ',\'title\':\'' . str_replace("'", "\\'", $GLOBALS['TL_LANG'][$this->strTable]['mmSrc']) . '\',\'url\':\'' . $strFile . '\'});return false"><img src="' . $strPreview . '" alt="' . specialchars($this->varValue[$i]['src']) . '"></a></td>
    <td class="col_1"><input type="text" class="tl_text_2" name="' . $this->strName . '['.$i.'][alt]" value="' . specialchars($this->varValue[$i]['alt'], true) . '"'.$strTranslateNone.'><br><input type="text" class="tl_text_2" name="' . $this->strName . '['.$i.'][link]" value="' . specialchars($this->varValue[$i]['link'], true) . '"'.$strTranslateText.'></td>
    <td class="col_2"><textarea name="' . $this->strName . '['.$i.'][desc]" cols="40" rows="3" class="tl_textarea"'.$strTranslateNone.' >' . specialchars($this->varValue[$i]['desc']) . '</textarea></td>
    <td class="col_3">
        '.($blnLanguage ? ('<input type="hidden" name="' . $this->strName . '['.$i.'][translate]" value="'.$this->varValue[$i]['translate'].'">') : '').'
        <fieldset class="radio_container">
            <span>
                <input id="' . $this->strName . '_'.$i.'_translate_none" name="' . $this->strName . '['.$i.'][translate]" type="radio" class="tl_radio" value="none"'.$this->optionChecked('none', $this->varValue[$i]['translate']).($blnLanguage ? ' disabled="disabled"' : '').'>
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

            // Add buttons
            foreach ($arrButtons as $button) {
                if ($button == 'delete' && $blnLanguage && $this->varValue[$i]['translate'] != 'all') {
                    continue;
                }

                $class = ($button == 'up' || $button == 'down') ? ' class="button-move"' : '';

                if ($button == 'drag') {
                    $return .= \Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');
                } else {
                    $return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button]).'" onclick="Isotope.MediaManager.act(this, \''.$button.'\',  \'ctrl_'.$this->strId.'\'); return false;">'.\Image::getHtml($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable]['wz_'.$button], 'class="tl_listwizard_img"').'</a> ';
                }
            }

            $return .= '</td>
  </tr>';
        }

        return $return.'
  </tbody>
  </table>
  </div>' . $upload . (!$blnIsAjax ? '</div>' : '');
    }

    /**
     * Get the file path or folder only
     *
     * @param string $strFile
     * @param bool   $blnFolder
     *
     * @return string
     */
    protected function getFilePath($strFile, $blnFolder = false)
    {
        if (stripos($strFile, $this->strTempFolder) !== false) {
            return $blnFolder ? dirname($blnFolder) : $strFile;
        }

        return 'isotope/' . substr($strFile, 0, 1) . (!$blnFolder ? ('/' . $strFile) : '');
    }

    /**
     * Retrieve image data from fallback language
     *
     * @return array|false
     */
    protected function getFallbackData()
    {
        // Fetch fallback language record
        if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->currentRecord] != '') {
            return deserialize(\Database::getInstance()->execute("SELECT {$this->strField} FROM {$this->strTable} WHERE id={$this->currentRecord}")->{$this->strField});
        }

        return false;
    }
}
