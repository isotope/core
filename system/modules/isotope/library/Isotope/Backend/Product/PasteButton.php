<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend;

use Database\Result;
use Isotope\Model\ProductType;


/**
 * Class PastProductButton
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */
class PasteButton extends \Backend
{

    /**
     * Handle the paste button callback for tl_iso_product
     *
     * @param \DataContainer $dc
     * @param array          $row
     * @param string         $table
     * @param bool           $cr
     * @param array|bool     $arrClipboard
     *
     * @return string
     *
     * @link http://www.contao.org/callbacks.html#paste_button_callback
     */
    public function generate(\DataContainer $dc, $row, $table, $cr, $arrClipboard = false)
    {
        // Disable all buttons if there is a circular reference
        if ($arrClipboard !== false && ('cut' === $arrClipboard['mode'] && ($cr == 1 || $arrClipboard['id'] == $row['id']) || 'cutAll' === $arrClipboard['mode'] && ($cr == 1 || \in_array($row['id'], $arrClipboard['id'])))) {
            return '';
        }

        $objProduct = \Database::getInstance()->prepare("SELECT p.*, t.variants FROM tl_iso_product p LEFT JOIN tl_iso_producttype t ON p.type=t.id WHERE p.id=?")->execute($arrClipboard['id']);

        // Copy or cut a single product or variant
        if ('cut' === $arrClipboard['mode'] || 'copy' === $arrClipboard['mode']) {
            return $this->pasteVariant($objProduct, $table, $row, $arrClipboard);
        } // Cut or copy multiple variants
        elseif ('cutAll' === $arrClipboard['mode'] || 'copyAll' === $arrClipboard['mode']) {
            return $this->pasteAll($objProduct, $table, $row, $arrClipboard);
        }

        $this->Session->set('CLIPBOARD', null);
        throw new \InvalidArgumentException('Unhandled paste_button_callback mode "' . $arrClipboard['mode'] . '"');
    }


    /**
     * Copy or paste a single variant
     *
     * @param Result $objProduct
     * @param string $table
     * @param array  $row
     * @param array  $arrClipboard
     *
     * @return string
     */
    protected function pasteVariant($objProduct, $table, $row, $arrClipboard)
    {
        // Can't copy variant into it's current product
        if ('tl_iso_product' === $table && $objProduct->pid == $row['id'] && 'copy' === $arrClipboard['mode']) {
            return $this->getPasteButton(false);
        } // Disable paste button for products without variant data
        elseif ('tl_iso_product' === $table && $row['id'] > 0) {
            $objType = ProductType::findByPk($row['type']);

            if (null === $objType || !$objType->hasVariants()) {
                return $this->getPasteButton(false);
            }
        }

        return $this->getPasteButton(true, \Backend::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $row['id']), $table, $row['id']);
    }


    /**
     * Copy or paste multiple products
     * @return string
     */
    protected function pasteAll($objProduct, $table, $row, $arrClipboard)
    {
        // Can't paste products in product or variant
        if ('tl_iso_product' === $table && $row['id'] > 0) {
            return '';
        }

        return $this->getPasteButton(true, \Backend::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;childs=1&amp;gid=' . $row['id']), $table, $row['id']);
    }


    /**
     * Return the paste button image
     * @param bool
     * @param string
     * @param string
     * @return string
     */
    protected function getPasteButton($blnActive, $url = '#', $table = '', $id = '')
    {
        if (!$blnActive) {
            return \Image::getHtml('pasteinto_.gif', '', 'class="blink"');
        }

        return '<a href="' . $url . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $id)) . '" onclick="Backend.getScrollOffset();">' . \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $id), 'class="blink"') . '</a> ';
    }
}
