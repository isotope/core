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

namespace Isotope\Model;


/**
 * Download model represents a file or folder download for a product
 */
class Download extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_download';


    /**
     * Get array of files for this download (could be multiple for folder selection)
     * @return  array
     */
    public function getFiles()
    {
        $objFile = $this->getRelated('singleSRC');

        if (null === $objFile) {
            return array();
        }

        if ($objFile->type == 'folder') {
            $arrFiles = array();
            $objFiles = \FilesModel::findBy(array("pid=?", "type='file'"), array($objFile->id));

            if (null !== $objFiles) {
                while ($objFiles->next()) {
                    $arrFiles[] = $objFiles->current();
                }
            }

            return $arrFiles;

        } elseif (is_file(TL_ROOT . '/' . $objFile->path)) {

            return array($objFile);
        }

        return array();
    }

    /**
     * Calculate the expiration time of a download
     * @param   int|null
     * @return  int|null
     */
    public function getExpirationTimestamp($intFrom = null)
    {
        if ($this->expires == '') {
            return null;
        }

        $arrExpires = deserialize($this->expires, true);

        if ($arrExpires['value'] == 0 || $arrExpires['unit'] == '') {
            return null;
        }

        return strtotime('+' . $arrExpires['value'] . ' ' . $arrExpires['unit'], $intFrom);
    }
}
