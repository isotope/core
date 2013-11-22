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

namespace Isotope\Backend;

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
     * @param DataContainer
     * @param array
     * @param string
     * @param bool
     * @param array
     * @return string
     * @link http://www.contao.org/callbacks.html#paste_button_callback
     */
    public function generate(\DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
    {
        // Disable all buttons if there is a circular reference
        if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id']))))
        {
            return '';
        }

        $objProduct = \Database::getInstance()->prepare("SELECT p.*, t.variants FROM " . \Isotope\Model\Product::getTable() . " p LEFT JOIN " . ProductType::getTable() . " t ON p.type=t.id WHERE p.id=?")->execute($arrClipboard['id']);

        // Copy or cut a single product or variant
        if ($arrClipboard['mode'] == 'cut' || $arrClipboard['mode'] == 'copy')
        {
            return $this->pasteVariant($objProduct, $table, $row, $arrClipboard);
        }

        // Cut or copy multiple variants
        elseif ($arrClipboard['mode'] == 'cutAll' || $arrClipboard['mode'] == 'copyAll')
        {
            return $this->pasteAll($objProduct, $table, $row, $arrClipboard);
        }

        $this->Session->set('CLIPBOARD', null);
        throw new \InvalidArgumentException('Unhandled paste_button_callback mode "' . $arrClipboard['mode'] . '"');
    }


    /**
     * Copy or paste a single variant
     * @return string
     */
    protected function pasteVariant($objProduct, $table, $row, $arrClipboard)
    {
        // Can't copy variant into it's current product
        if ($table == 'tl_iso_product' && $objProduct->pid == $row['id'] && $arrClipboard['mode'] == 'copy')
        {
            return $this->getPasteButton(false);
        }

        // Disable paste button for products without variant data
        elseif ($table == 'tl_iso_product' && $row['id'] > 0)
        {
            $objType = ProductType::findByPk($row['type']);

            if (null === $objType || !$objType->hasVariants())
            {
                return $this->getPasteButton(false);
            }
        }

        return $this->getPasteButton(true, $this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id']), $table, $row['id']);
    }


    /**
     * Copy or paste multiple products
     * @return string
     */
    protected function pasteAll($objProduct, $table, $row, $arrClipboard)
    {
        // Can't paste products in product or variant
        if ($table == 'tl_iso_product' && $row['id'] > 0)
        {
            return '';
        }

        return $this->getPasteButton(true, $this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;childs=1&amp;gid='.$row['id']), $table, $row['id']);
    }


    /**
     * Return the paste button image
     * @param bool
     * @param string
     * @param string
     * @return string
     */
    protected function getPasteButton($blnActive, $url='#', $table='', $id='')
    {
        if (!$blnActive)
        {
            return \Image::getHtml('pasteinto_.gif', '', 'class="blink"');
        }

        return '<a href="'.$url.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset();">'.\Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $id), 'class="blink"').'</a> ';
    }
}
