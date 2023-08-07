<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Isotope\Interfaces\IsotopeProduct;


/**
 * RelatedProduct holds array of related products
 *
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $sorting
 * @property int    $category
 * @property string $products
 */
class RelatedProduct extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_related_product';

    /**
     * Find related products of a product
     *
     * @param IsotopeProduct $objProduct
     * @param array          $arrCategories
     * @param array          $arrOptions
     *
     * @return Collection|null
     */
    public static function findByProductAndCategories(IsotopeProduct $objProduct, array $arrCategories, array $arrOptions = array())
    {
        $t = static::$strTable;

        if ($objProduct->isVariant()) {
            $pid = "($t.pid=? OR $t.pid=?)";
            $value = [$objProduct->getProductId(), $objProduct->getId()];
        } else {
            $pid = "$t.pid=?";
            $value = [$objProduct->getProductId()];
        }

        $arrOptions = array_merge(
            array(
                'column'    => [$pid, "$t.category IN (" . implode(',', $arrCategories) . ')'],
                'value'     => $value,
                'order'     => Database::getInstance()->findInSet("$t.category", $arrCategories),
                'return'    => 'Collection'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }
}
