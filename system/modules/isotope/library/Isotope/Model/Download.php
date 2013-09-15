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

namespace Isotope\Model;


/**
 * Download model represents a file or folder download for a product
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Download extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_downloads';


    /**
     * Get array of files for this download (could be multiple for folder selection)
     * @return  array
     */
    public function getFiles()
    {
        // Check for version 3 format
        if (!is_numeric($this->singleSRC)) {
            throw new \UnexpectedValueException($GLOBALS['TL_LANG']['ERR']['version2format']);
        }

        $objFile = $this->getRelated('singleSRC');

        if (null !== $objFile && $objFile->type == 'folder') {
            $arrFiles = array();
            $objFiles = \FilesModel::findBy(array("pid=?", "type='file'"), array($objFile->id));

            if (null !== $objFiles) {
                while ($objFiles->next()) {
                    $arrFiles[] = $objFiles->current();
                }
            }

            return $arrFiles;

        } elseif (null !== $objFile && is_file(TL_ROOT . '/' . $objFile->path)) {

            return array($objFile);
        }

        return array();
    }

    /**
     * Calculate the expiration time of a download
     * @param   int|null
     * @return  int|null
     */
    public function getExpirationTimestamp($intFrom=null)
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
