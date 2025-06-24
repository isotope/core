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
     * Return the "cut" button
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
    public function forCut($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] == 0) {
            return '';
        }

        return '<a href="' . preg_replace('/&(amp;)?id=[^& ]*/i', '', \Contao\StringUtil::ampersand(Environment::get('request'))) . '&amp;act=paste&amp;mode=cut&amp;table=tl_iso_product&amp;id=' . $row['id'] . '&amp;pid=' . Input::get('id') . '&rt=' . Input::get('rt') . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . ' onclick="Backend.getScrollOffset();">' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Return the "toggle visibility" button
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
    public function forVisibilityToggle($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), Input::get('state') == 1);
            Controller::redirect(System::getReferer());
        }

        $user = BackendUser::getInstance();

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->isAdmin && !$user->hasAccess('tl_iso_product::published', 'alexf')) {
            return '';
        }

        $time          = time();
        $arrAttributes = array();

        if (($objProductType = ProductType::findByProductData($row)) !== null) {
            $arrAttributes = $row['pid'] ? $objProductType->getVariantAttributes() : $objProductType->getAttributes();
        }

        if ((($arrAttributes['start']['enabled'] ?? false) && $row['start'] != '' && $row['start'] > $time) || (($arrAttributes['stop']['enabled'] ?? false) && $row['stop'] != '' && $row['stop'] < $time)) {
            return Image::getHtml(CompatibilityHelper::asset('images/invisible-startstop.png'), $label) . ' ';
        }

        if ($row['published'] != '1') {
            $icon = 'invisible.svg';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        return '<a href="' . Backend::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Return the "toggle fallback" button
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
    public function forFallbackToggle($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] < 1) {
            return '';
        }

        $icon = $row['fallback'] ? 'featured.svg' : 'featured_.svg';

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
     * Manage act=select buttons
     *
     * @param array $arrButtons
     *
     * @return array
     */
    public function forSelect($arrButtons)
    {
        if ('select' === Input::get('act') && !Input::get('id')) {
            unset($arrButtons['copy']);
            unset($arrButtons['cut']);
        }

        return $arrButtons;
    }

    /**
     * Publish/unpublish a product
     *
     * @param int  $intId
     * @param bool $blnVisible
     */
    protected function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        Permission::check();

        $user = BackendUser::getInstance();

        // Check permissions to publish
        if (!$user->isAdmin && !$user->hasAccess('tl_iso_product::published', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish product ID "' . $intId . '"');
        }

        $objVersions = new Versions('tl_iso_product', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_iso_product']['fields']['published']['save_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields']['published']['save_callback'] as $callback) {
                $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $this);
            }
        }

        // Update the database
        Database::getInstance()->prepare("UPDATE tl_iso_product SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        System::getContainer()->get('monolog.logger.contao')->info('A new version of record "tl_iso_product.id='.$intId.'" has been created'.$this->getParentEntries('tl_iso_product', $intId), ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]);
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
