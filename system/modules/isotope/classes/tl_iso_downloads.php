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

        $objDownload = Download::findByPk($row['id']);

        if (null === $objDownload) {
            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['invalidName'].'</p>';
        }

        $path = $objDownload->getRelated('singleSRC')->path;

        if ($objDownload->getRelated('singleSRC')->type == 'folder') {
            $arrDownloads = array();

            foreach (scan(TL_ROOT . '/' . $path) as $file) {
                if (is_file(TL_ROOT . '/' . $path . '/' . $file)) {
                    $objFile = new \File($path . '/' . $file);
                    $icon = 'background:url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $path . '/' . $file);
                }
            }

            if (empty($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">' . $path . '</div>' . implode("\n", $arrDownloads);
        }

        if (is_file(TL_ROOT . '/' . $path))
        {
            $objFile = new \File($path);
            $icon = 'background: url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
        }

        return sprintf('<div style="height: 16px;%s">%s</div>', $icon, $path);
    }
}
