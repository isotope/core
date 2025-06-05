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
use Contao\File;
use Contao\Folder;
use Contao\System;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\Haste\Util\Format;
use Isotope\Model\Download;
use Isotope\Model\Product;


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
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        if ('folder' === $objDownload->getRelated('singleSRC')->type) {
            $arrDownloads = array();

            foreach (Folder::scan($projectDir . '/' . $path) as $file) {
                if (is_file($projectDir . '/' . $path . '/' . $file)) {
                    $objFile        = new File($path . '/' . $file);
                    $icon           = 'background:url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
                    $arrDownloads[] = sprintf('<div style="margin-bottom:5px;height:16px;%s">%s</div>', $icon, $path . '/' . $file);
                }
            }

            if (0 === \count($arrDownloads)) {
                return $GLOBALS['TL_LANG']['ERR']['emptyDownloadsFolder'];
            }

            return '<div style="margin-bottom:5px;height:16px;font-weight:bold">' . $path . '</div>' . implode("\n", $arrDownloads);
        }

        if (is_file($projectDir . '/' . $path)) {
            $objFile = new File($path);
            $icon    = 'background: url(assets/contao/images/' . $objFile->icon . ') left center no-repeat; padding-left: 22px';
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
                    System::getContainer()->get(AttributeRegistry::class)->getVariantOptionFields()
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
}
