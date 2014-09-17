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

use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;


/**
 * Class AttributeOption
 *
 * @property int    id
 * @property int    pid
 * @property int    sorting
 * @property int    tstamp
 * @property string ptable
 * @property int    langPid
 * @property string language
 * @property string label
 * @property string type
 * @property bool   isDefault
 * @property bool   published
 */
class AttributeOption extends \MultilingualModel
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_attribute_option';

    /**
     * Get array representation of the attribute option
     *
     * @param IsotopeProduct $objProduct
     *
     * @return array
     */
    public function getAsArray(IsotopeProduct $objProduct = null)
    {
        return array(
            'value'     => $this->id,
            'label'     => $this->getLabel($objProduct),
            'group'     => ($this->type == 'group' ? '1' : ''),
            'default'   => ($this->isDefault ? '1' : ''),
            'model'     => $this
        );
    }

    /**
     * Return true if the option price is a percentage (not fixed) amount
     *
     * @return bool
     */
    public function isPercentage()
    {
        return substr($this->arrData['price'], -1) == '%' ? true : false;
    }

    /**
     * Return percentage amount (if applicable)
     *
     * @return float
     * @throws \UnexpectedValueException
     */
    public function getPercentage()
    {
        if (!$this->isPercentage()) {
            throw new \UnexpectedValueException('Attribute option does not have a percentage amount.');
        }

        return substr($this->arrData['price'], 0, -1);
    }

    /**
     * Return calculated price for this attribute option
     *
     * @param IsotopeProduct $objProduct
     *
     * @return float
     */
    public function getPrice(IsotopeProduct $objProduct = null)
    {
        if ($this->isPercentage() && null !== $objProduct) {
            $objPrice = $objProduct->getPrice();

            if (null !== $objPrice) {
                $fltAmount = $objPrice->getOriginalAmount();

                return $fltAmount / 100 * $this->getPercentage();
            }
        }

        return $this->price;
    }

    /**
     * Get formatted label for the attribute option
     *
     * @param IsotopeProduct $objProduct
     *
     * @return string
     */
    public function getLabel(IsotopeProduct $objProduct = null)
    {
        $strLabel = $this->label;

        /** @type Attribute $objAttribute */
        $objAttribute = null;

        switch ($this->ptable) {
            case 'tl_iso_product':
                $objAttribute = Attribute::findByFieldName($this->field_name);
                break;

            case 'tl_iso_attribute':
                $objAttribute = Attribute::findByPk($this->pid);
                break;
        }

        if (null !== $objAttribute && !$objAttribute->isVariantOption() && $this->price != '') {

            $strLabel .= ' (';

            if (!$this->isPercentage() || null !== $objProduct) {
                $strLabel .= Isotope::formatPriceWithCurrency($this->getPrice($objProduct), false);
            } else {
                $strLabel .= $this->price;
            }

            $strLabel .= ')';
        }

        return $strLabel;
    }

    /**
     * Find all options by attribute
     *
     * @param Attribute $objAttribute
     *
     * @return \Isotope\Collection\AttributeOption|null
     */
    public static function findByAttribute(IsotopeAttributeWithOptions $objAttribute)
    {
        if ($objAttribute->optionsSource != 'table') {
            throw new \LogicException('Options source for attribute "' . $objAttribute->field_name . '" is not the database table');
        }

        $t = static::getTable();

        return static::findBy(
            array(
                "$t.pid=?",
                "$t.ptable='tl_iso_attribute'",
                "$t.published='1'"
            ),
            array(
                $objAttribute->id
            ),
            array(
                'order' => "$t.sorting"
            )
        );
    }

    /**
     * Find all options by attribute
     *
     * @param IsotopeProduct              $objProduct
     * @param IsotopeAttributeWithOptions $objAttribute
     *
     * @return \Isotope\Collection\AttributeOption|null
     */
    public static function findByProductAndAttribute(IsotopeProduct $objProduct, IsotopeAttributeWithOptions $objAttribute)
    {
        if ($objAttribute->optionsSource != 'product') {
            throw new \LogicException('Options source for attribute "' . $objAttribute->field_name . '" is not the product');
        }

        $t = static::getTable();

        return static::findBy(
            array(
                "$t.pid=?",
                "$t.ptable='tl_iso_product'",
                "$t.field_name=?",
                "$t.published='1'"
            ),
            array(
                $objProduct->id,
                $objAttribute->field_name
            ),
            array(
                'order' => "$t.sorting"
            )
        );
    }

    /**
     * Create a Model\Collection object
     *
     * @param array  $arrModels An array of models
     * @param string $strTable  The table name
     *
     * @return \Isotope\Collection\AttributeOption The Model\Collection object
     */
    protected static function createCollection(array $arrModels, $strTable)
    {
        return new \Isotope\Collection\AttributeOption($arrModels, $strTable);
    }


    /**
     * Create a new collection from a database result
     *
     * @param \Database\Result $objResult The database result object
     * @param string           $strTable  The table name
     *
     * @return \Isotope\Collection\AttributeOption The model collection
     */
    protected static function createCollectionFromDbResult(\Database\Result $objResult, $strTable)
    {
        return \Isotope\Collection\AttributeOption::createFromDbResult($objResult, $strTable);
    }
}
