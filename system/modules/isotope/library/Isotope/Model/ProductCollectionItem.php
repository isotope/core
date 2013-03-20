<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Product\Standard as StandardProduct;


/**
 * ProductCollectionItem represents an item in a product collection.
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductCollectionItem extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_item';


    /**
     * Get the product related to this item
     * @return IsotopeProduct|null
     */
    public function getProduct()
    {
        $strClass = $GLOBALS['ISO_PRODUCT'][$this->type]['class'];

        if ($strClass == '' || class_exists($strClass)) {
            $strClass = 'Isotope\Product\Standard';
        }

        $arrData = array('sku'=>$this->sku, 'name'=>$this->name, 'price'=>$this->price, 'tax_free_price'=>$this->tax_free_price);

        $objProductData = \Database::getInstance()->prepare($strClass::getSelectStatement() . " WHERE p1.language='' AND p1.id=?")
                                                  ->execute($this->product_id);

        if ($objProductData->numRows) {
            $arrData = $this->blnLocked ? array_merge($objProductData->row(), $arrData) : $objProductData->row();
        }

        $objProduct = new $strClass($arrData, deserialize($this->options), $this->blnLocked, $this->quantity);
        $objProduct->collection_id = $this->id;
        $objProduct->tax_id = $this->tax_id;
        $objProduct->reader_jumpTo_Override = $this->href_reader;

        return $objProduct;
    }
}
