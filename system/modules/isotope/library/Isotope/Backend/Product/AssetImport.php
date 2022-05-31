<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\FileTree;
use Contao\Folder;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;

class AssetImport extends Backend
{

    /**
     * Import images and other media file for products
     *
     * @return string
     */
    public function generate($dc)
    {
        $objTree = new FileTree(FileTree::getAttributesFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields']['source'], 'source', null, 'source', 'tl_iso_product', $dc));

        // Import assets
        if (Input::post('FORM_SUBMIT') === 'tl_iso_product_import' && Input::post('source') != '') {

            $objFolder = FilesModel::findByUuid(StringUtil::uuidToBin(Input::post('source')));

            if (null !== $objFolder) {
                $this->importFromPath($objFolder->path);
            }
        }

        // Return form
        return '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=import', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['tl_iso_product']['import'][1] . '</h2>

<div class="tl_message"><div class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_product']['importAssetsDescr'] . '</div></div>
' . Message::generate() . '

<form id="tl_iso_product_import" class="tl_form" method="post">
<div class="tl_formbody_edit iso_importassets">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_product_import">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">

<div class="tl_tbox block">
  <div class="widget">
    <h3><label for="source">' . $GLOBALS['TL_LANG']['tl_iso_product']['source'][0] . '</label></h3>
    ' . $objTree->generate() . (\strlen($GLOBALS['TL_LANG']['tl_iso_product']['source'][1]) ? '
    <p class="tl_help">' . $GLOBALS['TL_LANG']['tl_iso_product']['source'][1] . '</p>' : '') . '
  </div>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import product assets" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['import'][0]) . '">
</div>

</div>
</form>';
    }

    /**
     * Import files from selected folder
     *
     * @param string $strPath
     */
    protected function importFromPath($strPath)
    {
        $arrFiles = scan(TL_ROOT . '/' . $strPath);

        if (empty($arrFiles)) {
            Message::addError($GLOBALS['TL_LANG']['MSC']['noFilesInFolder']);
            Controller::reload();
        }

        $blnEmpty    = true;
        $arrDelete   = array();
        $objProducts = Database::getInstance()->prepare('SELECT * FROM tl_iso_product WHERE pid=0')->execute();

        while ($objProducts->next()) {
            $arrImageNames = array();
            $arrImages = StringUtil::deserialize($objProducts->images);

            if (!\is_array($arrImages)) {
                $arrImages = array();
            } else {
                foreach ($arrImages as $row) {
                    if ($row['src']) {
                        $arrImageNames[] = $row['src'];
                    }
                }
            }

            $arrPattern   = array();
            $arrPattern[] = $objProducts->alias ? StringUtil::standardize($objProducts->alias) : null;
            $arrPattern[] = $objProducts->sku ?: null;
            $arrPattern[] = $objProducts->sku ? StringUtil::standardize($objProducts->sku) : null;
            $arrPattern[] = !empty($arrImageNames) ? implode('|', $arrImageNames) : null;

            // !HOOK: add custom import regex patterns
            if (isset($GLOBALS['ISO_HOOKS']['addAssetImportRegexp']) && \is_array($GLOBALS['ISO_HOOKS']['addAssetImportRegexp'])) {
                foreach ($GLOBALS['ISO_HOOKS']['addAssetImportRegexp'] as $callback) {
                    $arrPattern = System::importStatic($callback[0])->{$callback[1]}($arrPattern, $objProducts);
                }
            }

            $strPattern = '@^(' . implode('|', array_filter($arrPattern)) . ')@i';

            $arrMatches = preg_grep($strPattern, $arrFiles);

            if (!empty($arrMatches)) {
                $arrNewImages = array();

                foreach ($arrMatches as $file) {
                    if (is_dir(TL_ROOT . '/' . $strPath . '/' . $file)) {
                        $arrSubfiles = scan(TL_ROOT . '/' . $strPath . '/' . $file);

                        if (!empty($arrSubfiles)) {
                            foreach ($arrSubfiles as $subfile) {
                                if (is_file(TL_ROOT . '/' . $strPath . '/' . $file . '/' . $subfile)) {
                                    $objFile = new File($strPath . '/' . $file . '/' . $subfile);

                                    if ($objFile->isGdImage) {
                                        $arrNewImages[] = $strPath . '/' . $file . '/' . $subfile;
                                    }
                                }
                            }
                        }
                    } elseif (is_file(TL_ROOT . '/' . $strPath . '/' . $file)) {
                        $objFile = new File($strPath . '/' . $file);

                        if ($objFile->isGdImage) {
                            $arrNewImages[] = $strPath . '/' . $file;
                        }
                    }
                }

                if (!empty($arrNewImages)) {
                    foreach ($arrNewImages as $strFile) {
                        $pathinfo = pathinfo(TL_ROOT . '/' . strtolower($strFile));

                        // Will recursively create the folder
                        $objFolder = new Folder('isotope/' . substr($pathinfo['filename'], 0, 1));

                        $strCacheName = $pathinfo['filename'] . '-' . substr(md5_file(TL_ROOT . '/' . $strFile), 0, 8) . '.' . $pathinfo['extension'];

                        Files::getInstance()->copy($strFile, $objFolder->path . '/' . $strCacheName);
                        $arrImages[] = array('src' => $strCacheName);
                        $arrDelete[] = $strFile;

                        Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['MSC']['assetImportConfirmation'], $pathinfo['filename'] . '.' . $pathinfo['extension'], $objProducts->name));
                        $blnEmpty = false;
                    }

                    Database::getInstance()->prepare('UPDATE tl_iso_product SET images=? WHERE id=?')->execute(serialize($arrImages), $objProducts->id);
                }
            }
        }

        if (!empty($arrDelete)) {
            $arrDelete = array_unique($arrDelete);

            foreach ($arrDelete as $file) {
                Files::getInstance()->delete($file);
            }
        }

        if ($blnEmpty) {
            Message::addInfo($GLOBALS['TL_LANG']['MSC']['assetImportNoFilesFound']);
        }

        Controller::reload();
    }
}
