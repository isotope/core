<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Isotope\Interfaces\IsotopeProduct;


/**
 * RelatedProduct holds array of related products
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class RelatedProduct extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_related_product';


    /**
     * Find related products of a product
     * @param   IsotopeProduct
     * @param   array
     * @param   array
     * @return  \Model\Collection|null
     */
    public static function findByProductAndCategories(IsotopeProduct $objProduct, array $arrCategories, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrOptions = array_merge(
            array(
                'column'    => array("$t.pid=?", "$t.category IN (" . implode(',', $arrCategories) . ")"),
                'value'     => array($objProduct->getProductId()),
                'order'     => \Database::getInstance()->findInSet("$t.category", $arrCategories),
                'return'    => 'Collection'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }
}
