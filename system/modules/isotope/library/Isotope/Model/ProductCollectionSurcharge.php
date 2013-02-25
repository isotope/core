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

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Tax;

/**
 * Class Surcharge
 *
 * Provide methods to handle Isotope product collection surcharges.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class ProductCollectionSurcharge extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_collection_surcharge';

    /**
     * Tax amount for individual products
     * @var array
     */
    protected $arrProducts = array();

    /**
     * Return if the surcharge has tax
     * @return bool
     */
    public function hasTax()
    {
        return ($this->tax_class > 0 || !empty($this->arrProducts)) ? true : false;
    }

    /**
     * Get tax amount for an individual product
     * @param IsotopeProduct
     */
    public function getAmountForProduct(IsotopeProduct $objProduct)
    {
        if (isset($this->arrProducts[$objProduct->collection_id])) {

            return (float) $this->arrProducts[$objProduct->collection_id];
        }

        return 0;
    }

    /**
     * Set tax amount for a product
     * @param  float
     * @param  IsotopeProduct
     */
    public function setAmountForProduct($fltAmount, IsotopeProduct $objProduct)
    {
        if ($objProduct->collection_id == 0) {
            throw new \UnderflowException('Product must be in the cart (must have collection_id value)');
        }

        if ($fltAmount != 0) {
            $this->arrProducts[$objProduct->collection_id] = $fltAmount;
        } else {
            unset($this->arrProducts[$objProduct->collection_id]);
        }
    }

    /**
     * Set the current record from an array
     *
     * @param array $arrData The data record
     *
     * @return \Model The model object
     */
    public function setRow(array $arrData)
    {
        $this->arrProducts = deserialize($arrData['products']);
        unset($arrData['products']);

        if (!is_array($this->arrProducts)) {
            $this->arrProducts = array();
        }

        return parent::setRow($arrData);
    }

    /**
     * Modify the current row before it is stored in the database
     *
     * @param array $arrSet The data array
     *
     * @return array The modified data array
     */
    protected function preSave(array $arrSet)
    {
        $arrSet['products'] = serialize($this->arrProducts);

        return $arrSet;
    }
     * Return a model or collection based on the database result type
     */
    protected static function find(array $arrOptions)
    {
        if (static::$strTable == '')
        {
            return null;
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = \Model\QueryBuilder::find($arrOptions);

        $objStatement = \Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit']))
        {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset']))
        {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
        {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1)
        {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {
            $strClass = '\Isotope\Model\ProductCollectionSurcharge\\' . $objResult->type;

            return new $strClass($objResult);
        } else {

            return new \Isotope\Model\Collection\ProductCollectionSurcharge($objResult, static::$strTable);
        }
    }
}
