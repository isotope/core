<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;

use Isotope\Model\Download;


/**
 * Class tl_iso_downloads
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_downloads extends \Backend
{

    /**
     * List download files
     * @param   array
     * @return  string
     */
    public function listRows($row)
    {
        // Check for version 3 format
        if (!is_numeric($row['singleSRC'])) {
            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
        }

        $objFiles = \FilesModel::findByPk($row['singleSRC']);

        if (null === $objFiles) {
            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['invalidName'].'</p>';
        }

        if ($objFiles->type == 'folder') {
            $arrDownloads = array();

            foreach (scan(TL_ROOT . '/' . $objFiles->path) as $file) {
                if (is_file(TL_ROOT . '/' . $objFiles->path . '/' . $file)) {
                    $objFile = new \File($objFiles->path . '/' . $file);
                    $icon = 'background:url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $objFiles->path . '/' . $file);
                }
            }

            if (empty($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">' . $objFiles->path . '</div>' . implode("\n", $arrDownloads);
        }

        if (is_file(TL_ROOT . '/' . $objFiles->path))
        {
            $objFile = new \File($objFiles->path);
            $icon = 'background: url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
        }

        return sprintf('<div style="height: 16px;%s">%s</div>', $icon, $objFiles->path);
    }
}
