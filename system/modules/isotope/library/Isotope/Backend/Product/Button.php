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

namespace Isotope\Backend\Product;

use Isotope\Model\Download;
use Isotope\Model\ProductType;
use Isotope\Model\ProductCollectionItem;


class Button extends \Backend
{

    /**
     * Hide "product groups" button for non-admins
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function forGroups($href, $label, $title, $class, $attributes, $table, $root)
    {
        if (!\BackendUser::getInstance()->isAdmin && (!is_array(\BackendUser::getInstance()->iso_groupp) || empty(\BackendUser::getInstance()->iso_groupp))) {
            return '';
        }

        return '<a href="' . \Backend::addToUrl('&amp;' . $href) . '" class="header_icon" title="' . specialchars($title) . '"' . $attributes . '>' . specialchars($label) . '</a>';
    }

    /**
     * Return the "copy" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forCopy($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0) {
            return '<a href="' . preg_replace('/&(amp;)?id=[^& ]*/i', '', ampersand(\Environment::get('request'))) . '&amp;act=paste&amp;mode=copy&amp;table=tl_iso_product&amp;id=' . $row['id'] . '&amp;pid=' . \Input::get('id') . '" title="' . specialchars($title) . '"' . $attributes . ' onclick="Backend.getScrollOffset();">' . \Image::getHtml($icon, $label) . '</a> ';
        }

        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Return the "cut" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forCut($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] == 0) {
            return '';
        }

        return '<a href="' . preg_replace('/&(amp;)?id=[^& ]*/i', '', ampersand(\Environment::get('request'))) . '&amp;act=paste&amp;mode=cut&amp;table=tl_iso_product&amp;id=' . $row['id'] . '&amp;pid=' . \Input::get('id') . '&rt=' . \Input::get('rt') . '" title="' . specialchars($title) . '"' . $attributes . ' onclick="Backend.getScrollOffset();">' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Disable "delete" button if product has been sold
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forDelete($row, $href, $label, $title, $icon, $attributes)
    {
        $t = ProductCollectionItem::getTable();

        if (ProductCollectionItem::countBy(array("$t.pid IN (SELECT id FROM tl_iso_product_collection WHERE type='order')", "$t.product_id IN (SELECT id FROM tl_iso_product WHERE id=? OR (pid=? AND language=''))"), array($row['id'], $row['id'])) > 0) {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
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
    public function forVisibilityToggle($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            \Controller::redirect(\System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_product::published', 'alexf')) {
            return '';
        }

        $time          = time();
        $arrAttributes = array();

        if (($objProductType = ProductType::findByPk($row['type'])) !== null) {
            $arrAttributes = $row['pid'] ? $objProductType->getVariantAttributes() : $objProductType->getAttributes();
        }

        if (($arrAttributes['start']['enabled'] && $row['start'] != '' && $row['start'] > $time) || ($arrAttributes['stop']['enabled'] && $row['stop'] != '' && $row['stop'] < $time)) {
            return \Image::getHtml('system/modules/isotope/assets/images/invisible-startstop.png', $label) . ' ';
        } elseif ($row['published'] != '1') {
            $icon = 'invisible.gif';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        return '<a href="' . \Backend::addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Hide variant buttons for product types without variant support
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forVariants($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0 || ($objProductType = ProductType::findByPk($row['type'])) === null || !$objProductType->hasVariants()) {
            return '';
        }

        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Hide "related" button for variants
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forRelated($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0) {
            return '';
        }

        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Show/hide the downloads button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forDownloads($row, $href, $label, $title, $icon, $attributes)
    {
        if (($objProductType = ProductType::findByPk($row['type'])) === null || !$objProductType->hasDownloads()) {
            return '';
        }

        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_DCA']['tl_iso_product']['list']['operations']['downloads']['label'][2], $this->getNumberOfDownloadsForProduct($row['id'])) . $title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Return the "cut" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function forGroup($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0) {
            return '';
        }

        // Check permission
        if (!\BackendUser::getInstance()->isAdmin) {
            $groups = deserialize(\BackendUser::getInstance()->iso_groups);

            if (!is_array($groups) || empty($groups)) {
                return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
            }
        }

        return '<a href="system/modules/isotope/group.php?do=' . \Input::get('do') . '&amp;table=' . \Isotope\Model\Group::getTable() . '&amp;field=gid&amp;value=' . $row['gid'] . '" title="' . specialchars($title) . '"' . $attributes . ' onclick="Backend.getScrollOffset();Isotope.openModalGroupSelector({\'width\':765,\'title\':\'' . specialchars($GLOBALS['TL_LANG']['tl_iso_product']['product_groups'][0]) . '\',\'url\':this.href,\'action\':\'moveProduct\',\'redirect\':\'' . \Backend::addToUrl($href . '&pid=' . intval(\Input::get('pid')) . '&id=' . $row['id']) . '\'});return false">' . \Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Publish/unpublish a product
     * @param integer
     * @param boolean
     * @return void
     */
    protected function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        Permission::check();

        // Check permissions to publish
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_product::published', 'alexf')) {
            \System::log('Not enough permissions to publish/unpublish product ID "' . $intId . '"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new \Versions('tl_iso_product', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_product']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields']['published']['save_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $blnVisible  = $objCallback->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_iso_product SET published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
    }


    /**
     * Get number of downloads for a product id
     * @param   int
     * @return  int
     */
    protected function getNumberOfDownloadsForProduct($intProduct)
    {
        // Cache number of downloads
        static $arrDownloads;

        if (null === $arrDownloads) {
            $objDownloads = \Database::getInstance()->query("
                SELECT pid, COUNT(id) AS total FROM " . Download::getTable() . " GROUP BY pid
            ");

            while ($objDownloads->next()) {
                $arrDownloads[$objDownloads->pid] = $objDownloads->total;
            }
        }

        return (int) $arrDownloads[$intProduct];
    }
}
