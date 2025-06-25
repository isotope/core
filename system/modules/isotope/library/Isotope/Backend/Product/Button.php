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
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\CompatibilityHelper;
use Isotope\Model\ProductType;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


class Button extends Backend
{
    /**
     * Return the "copy" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function forCopy($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0) {
            return '<a href="' . preg_replace('/&(amp;)?id=[^& ]*/i', '', \Contao\StringUtil::ampersand(Environment::get('request'))) . '&amp;act=paste&amp;mode=copy&amp;table=tl_iso_product&amp;id=' . $row['id'] . '&amp;pid=' . Input::get('id') . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . ' onclick="Backend.getScrollOffset();">' . Image::getHtml($icon, $label) . '</a> ';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Hide variant buttons for product types without variant support
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function forVariants($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0 || ($objProductType = ProductType::findByProductData($row)) === null || !$objProductType->hasVariants()) {
            return '';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Hide "related" button for variants
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function forRelated($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0) {
            return '';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Show/hide the downloads button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function forDownloads($row, $href, $label, $title, $icon, $attributes)
    {
        if (($objProductType = ProductType::findByProductData($row)) === null || !$objProductType->hasDownloads()) {
            return '';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title . '<br>'.sprintf($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['downloads']['label'][2], $this->getNumberOfDownloadsForProduct($row['id']))) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Get number of downloads for a product id
     *
     * @param int $intProduct
     *
     * @return int
     */
    protected function getNumberOfDownloadsForProduct($intProduct)
    {
        // Cache number of downloads
        static $arrDownloads;

        if (null === $arrDownloads) {
            $objDownloads = Database::getInstance()->query(
                'SELECT pid, COUNT(id) AS total FROM tl_iso_download GROUP BY pid'
            );

            while ($objDownloads->next()) {
                $arrDownloads[$objDownloads->pid] = $objDownloads->total;
            }
        }

        return (int) ($arrDownloads[$intProduct] ?? 0);
    }
}
