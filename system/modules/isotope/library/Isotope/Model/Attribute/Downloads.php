<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;


/**
 * Attribute to provide downloads in the product details
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Downloads extends Attribute implements IsotopeAttribute
{
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "blob NULL";

        if ($this->fieldType == 'checkbox') {
            $arrData['fields'][$this->field_name]['sql']              = "blob NULL";
            $arrData['fields'][$this->field_name]['eval']['multiple'] = true;

            // Custom sorting
            if ($this->sortBy == 'custom') {
                $strOrderField                                              = $this->field_name . '_order';
                $arrData['fields'][$this->field_name]['eval']['orderField'] = $strOrderField;
                $arrData['fields'][$strOrderField]['sql']                   = "text NULL";
            }
        } else {
            $arrData['fields'][$this->field_name]['sql']              = "int(10) unsigned NOT NULL default '0'";
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
        }
    }

    /**
     * Return class name for the backend widget or false if none should be available
     * @return    string
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['fileTree'];
    }


    /**
     * Generate download attributes
     * @param IsotopeProduct
     * @return string
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        global $objPage;
        $arrFiles = $objProduct->{$this->field_name};

        // Return if there are no files
        if (empty($arrFiles) || !is_array($arrFiles)) {
            return '';
        }

        // Check for version 3 format
        if (!is_numeric($arrFiles[0])) {
            return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
        }

        // Get the file entries from the database
        $objFiles = \FilesModel::findMultipleByIds($arrFiles);

        if (null === $objFiles) {
            return '';
        }

        $file = \Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file != '' && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file))) {
            while ($objFiles->next()) {
                if ($file == $objFiles->path || dirname($file) == $objFiles->path) {
                    \Controller::sendFileToBrowser($file);
                }
            }

            $objFiles->reset();
        }

        $files   = array();
        $auxDate = array();

        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        // Get all files
        while ($objFiles->next()) {

            // Continue if the files has been processed or does not exist
            if (isset($files[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path)) {
                continue;
            }

            // Single files
            if ($objFiles->type == 'file') {
                $objFile = new \File($objFiles->path, true);

                if (!in_array($objFile->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename)) {
                    continue;
                }

                $arrMeta = \Frontend::getMetaData($objFiles->meta, $objPage->language);

                // Use the file name as title if none is given
                if ($arrMeta['title'] == '') {
                    $arrMeta['title'] = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)));
                }

                $strHref = \Environment::get('request');

                // Remove an existing file parameter (see #5683)
                if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
                    $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
                }

                $strHref .= (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objFiles->path);

                // Add the image
                $files[$objFiles->path] = array(
                    'id'        => $objFiles->id,
                    'name'      => $objFile->basename,
                    'title'     => $arrMeta['title'],
                    'link'      => $arrMeta['title'],
                    'caption'   => $arrMeta['caption'],
                    'href'      => $strHref,
                    'filesize'  => \System::getReadableSize($objFile->filesize, 1),
                    'icon'      => TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon,
                    'mime'      => $objFile->mime,
                    'meta'      => $arrMeta,
                    'extension' => $objFile->extension,
                    'path'      => $objFile->dirname
                );

                $auxDate[] = $objFile->mtime;
            } // Folders
            else {
                $objSubfiles = \FilesModel::findByPid($objFiles->id);

                if ($objSubfiles === null) {
                    continue;
                }

                while ($objSubfiles->next()) {

                    // Skip subfolders
                    if ($objSubfiles->type == 'folder') {
                        continue;
                    }

                    $objFile = new \File($objSubfiles->path, true);

                    if (!in_array($objFile->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename)) {
                        continue;
                    }

                    $arrMeta = \Frontend::getMetaData($objSubfiles->meta, $objPage->language);

                    // Use the file name as title if none is given
                    if ($arrMeta['title'] == '') {
                        $arrMeta['title'] = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)));
                    }

                    $strHref = \Environment::get('request');

                    // Remove an existing file parameter (see #5683)
                    if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
                        $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
                    }

                    $strHref .= (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objSubfiles->path);

                    // Add the image
                    $files[$objSubfiles->path] = array(
                        'id'        => $objSubfiles->id,
                        'name'      => $objFile->basename,
                        'title'     => $arrMeta['title'],
                        'link'      => $arrMeta['title'],
                        'caption'   => $arrMeta['caption'],
                        'href'      => $strHref,
                        'filesize'  => $this->getReadableSize($objFile->filesize, 1),
                        'icon'      => TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon,
                        'mime'      => $objFile->mime,
                        'meta'      => $arrMeta,
                        'extension' => $objFile->extension,
                        'path'      => $objFile->dirname
                    );

                    $auxDate[] = $objFile->mtime;
                }
            }
        }

        // Sort array
        $sortBy = $arrOptions['sortBy'] ? : $this->sortBy;
        switch ($sortBy) {
            default:
            case 'name_asc':
                uksort($files, 'basename_natcasecmp');
                break;

            case 'name_desc':
                uksort($files, 'basename_natcasercmp');
                break;

            case 'date_asc':
                array_multisort($files, SORT_NUMERIC, $auxDate, SORT_ASC);
                break;

            case 'date_desc':
                array_multisort($files, SORT_NUMERIC, $auxDate, SORT_DESC);
                break;

            case 'custom':
                if ($this->{$this->field_name . '_order'} != '') {
                    // Turn the order string into an array and remove all values
                    $arrOrder = explode(',', $this->{$this->field_name . '_order'});
                    $arrOrder = array_flip(array_map('intval', $arrOrder));
                    $arrOrder = array_map(function () {
                    }, $arrOrder);

                    // Move the matching elements to their position in $arrOrder
                    foreach ($files as $k => $v) {
                        if (array_key_exists($v['id'], $arrOrder)) {
                            $arrOrder[$v['id']] = $v;
                            unset($files[$k]);
                        }
                    }

                    // Append the left-over files at the end
                    if (!empty($files)) {
                        $arrOrder = array_merge($arrOrder, array_values($files));
                    }

                    // Remove empty (unreplaced) entries
                    $files = array_filter($arrOrder);
                    unset($arrOrder);
                }
                break;

            case 'random':
                shuffle($files);
                break;
        }

        $objTemplate        = new \Isotope\Template('ce_downloads');
        $objTemplate->class = $this->field_name;
        $objTemplate->files = array_values($files);

        return $objTemplate->parse();
    }
}
