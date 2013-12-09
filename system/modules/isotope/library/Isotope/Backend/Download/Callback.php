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

namespace Isotope\Backend\Download;

use Isotope\Model\Download;
use Isotope\Model\ProductCollectionDownload;


class Callback extends \Backend
{

    /**
     * List download files
     * @param   array
     * @return  string
     * @see     https://contao.org/de/manual/3.1/data-container-arrays.html#label_callback
     */
    public function listRows($row)
    {
        $objDownload = Download::findByPk($row['id']);
        $icon        = '';

        if (null === $objDownload || null === $objDownload->getRelated('singleSRC')) {
            return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['invalidName'] . '</p>';
        }

        $path = $objDownload->getRelated('singleSRC')->path;

        if ($objDownload->getRelated('singleSRC')->type == 'folder') {
            $arrDownloads = array();

            foreach (scan(TL_ROOT . '/' . $path) as $file) {
                if (is_file(TL_ROOT . '/' . $path . '/' . $file)) {
                    $objFile        = new \File($path . '/' . $file);
                    $icon           = 'background:url(' . TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $path . '/' . $file);
                }
            }

            if (empty($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">' . $path . '</div>' . implode("\n", $arrDownloads);
        }

        if (is_file(TL_ROOT . '/' . $path)) {
            $objFile = new \File($path);
            $icon    = 'background: url(' . TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
        }

        return sprintf('<div style="height: 16px;%s">%s</div>', $icon, $path);
    }

    /**
     * Prevent delete on a download which has been sold
     * @param   array
     * @param   string
     * @param   string
     * @param   string
     * @param   string
     * @param   array
     * @return  string
     * @see     https://contao.org/de/manual/3.1/data-container-arrays.html#button_callback
     */
    public function deleteButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (ProductCollectionDownload::countBy('download_id', $row['id']) > 0) {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }


    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            \Controller::redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_download::published', 'alexf')) {
            return '';
        }

        if ($row['published'] != '1') {
            $icon = 'invisible.gif';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }


    /**
     * Publish/unpublish a product
     * @param integer
     * @param boolean
     * @return void
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        // Check permissions to publish
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_download::published', 'alexf')) {
            \System::log('Not enough permissions to publish/unpublish download ID "' . $intId . '"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_iso_download', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_download']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_download']['fields']['published']['save_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $blnVisible  = $objCallback->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_iso_download SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $this->createNewVersion('tl_iso_download', $intId);
    }
}
