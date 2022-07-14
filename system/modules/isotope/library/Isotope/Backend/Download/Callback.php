<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Download;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\File;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\Model\Attribute;
use Isotope\Model\Download;
use Isotope\Model\Product;
use Isotope\Model\ProductCollectionDownload;
use Haste\Util\Format;


class Callback extends Backend
{

    /**
     * List download files
     *
     * @param array $row
     *
     * @return  string
     *
     * @see https://contao.org/de/manual/3.1/data-container-arrays.html#label_callback
     */
    public function listRows($row)
    {
        $objDownload = Download::findByPk($row['id']);
        $icon        = '';

        if (null === $objDownload || null === $objDownload->getRelated('singleSRC')) {
            return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['invalidName'] . '</p>';
        }

        $path = $objDownload->getRelated('singleSRC')->path;

        if ('folder' === $objDownload->getRelated('singleSRC')->type) {
            $arrDownloads = array();

            foreach (scan(TL_ROOT . '/' . $path) as $file) {
                if (is_file(TL_ROOT . '/' . $path . '/' . $file)) {
                    $objFile        = new File($path . '/' . $file);
                    $icon           = 'background:url(' . TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $path . '/' . $file);
                }
            }

            if (0 === \count($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">' . $path . '</div>' . implode("\n", $arrDownloads);
        }

        if (is_file(TL_ROOT . '/' . $path)) {
            $objFile = new File($path);
            $icon    = 'background: url(' . TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
        }

        return sprintf('<div style="height: 16px;%s">%s</div>', $icon, $path);
    }

    /**
     * Generate header fields for product or variant
     *
     * @param array                 $arrFields
     * @param \Contao\DataContainer $dc
     *
     * @return array
     */
    public function headerFields($arrFields, $dc)
    {
        $t          = Product::getTable();
        $arrNew     = array();
        $objProduct = Product::findByPk($dc->id);

        if (null === $objProduct) {
            return $arrFields;
        }

        $arrAttributes = array('name', 'alias', 'sku');

        if ($objProduct->isVariant()) {
            $arrAttributes = array_merge(
                $arrAttributes,
                array_intersect(
                    array_merge($objProduct->getType()->getAttributes(), $objProduct->getType()->getVariantAttributes()),
                    Attribute::getVariantOptionFields()
                )
            );
        }

        foreach ($arrAttributes as $field) {
            $v = $objProduct->$field;

            if ($v != '') {
                $arrNew[Format::dcaLabel($t, $field)] = Format::dcaValue($t, $field, $v);
            }
        }

        // Add fields that have potentially been added through the DCA, but do not allow to override the core fields
        return array_merge($arrNew, array_diff_key($arrFields, $arrNew));
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
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
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
        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), Input::get('state') == 1);
            Controller::redirect(System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!BackendUser::getInstance()->isAdmin && !BackendUser::getInstance()->hasAccess('tl_iso_download::published', 'alexf')) {
            return '';
        }

        if ($row['published'] != '1') {
            $icon = 'invisible.svg';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        return '<a href="' . Backend::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
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
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        // Check permissions to publish
        if (!BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_download::published', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish download ID "' . $intId . '"');
        }

        $objVersions = new Versions('tl_iso_download', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_iso_download']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_download']['fields']['published']['save_callback'] as $callback) {
                $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $this);
            }
        }

        // Update the database
        Database::getInstance()->prepare("UPDATE tl_iso_download SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        System::log('A new version of record "tl_iso_download.id='.$intId.'" has been created'.$this->getParentEntries('tl_iso_download', $intId), __METHOD__, TL_GENERAL);
    }
}
