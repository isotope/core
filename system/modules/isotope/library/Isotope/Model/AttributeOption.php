<?php

namespace Isotope\Model;

use Contao\Database\Result;
use Isotope\Collection\ProductPrice as ProductPriceCollection;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

/**
 * Class AttributeOption
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $sorting
 * @property int    $tstamp
 * @property string $ptable
 * @property int    $langPid
 * @property string $language
 * @property string $field_name
 * @property string $type
 * @property bool   $isDefault
 * @property string $label
 * @property string $price
 * @property bool   $published
 */
class AttributeOption extends Multilingual
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
     * @param bool           $blnPriceInLabel
     *
     * @return array
     */
    public function getAsArray(IsotopeProduct $objProduct = null, $blnPriceInLabel = true)
    {
        return array(
            'value'     => $this->getLanguageId(),
            'label'     => $blnPriceInLabel ? $this->getLabel($objProduct) : $this->label,
            'group'     => 'group' === $this->type ? '1' : '',
            'default'   => $this->isDefault ? '1' : '',
            'cssClass'  => $this->cssClass,
            'model'     => $this
        );
    }

    /**
     * Get attribute of option
     *
     * @return Attribute|null
     */
    public function getAttribute()
    {
        if ('tl_iso_attribute' === $this->ptable) {
            return Attribute::findByPk($this->pid);
        }

        if ('tl_iso_product' === $this->ptable) {
            return Attribute::findByFieldName($this->field_name);
        }

        return null;
    }

    /**
     * Return true if the option price is a percentage (not fixed) amount
     *
     * @return bool
     */
    public function isPercentage()
    {
        return '%' === substr($this->arrData['price'], -1);
    }

    /**
     * Check if we show from price for option
     *
     * @param IsotopeProduct $objProduct
     *
     * @return bool
     */
    public function isFromPrice(IsotopeProduct $objProduct = null)
    {
        if ($this->isPercentage() && null !== $objProduct) {

            /** @var ProductPrice[] $objPrice */
            $objPrice = $objProduct->getPrice();

            if (null !== $objPrice && $objPrice instanceof ProductPriceCollection) {
                $arrPrices = array();

                foreach ($objPrice as $objPriceModel) {
                    $arrPrices[] = $objPriceModel->getValueForTier($objPriceModel->getLowestTier());
                }

                return \count(array_unique($arrPrices)) > 1;
            }
        }

        return false;
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
     *
     * @deprecated Deprecated since Isotope 2.2.6, to be removed in 3.0.
     *             This method can result in an endless loop, use getAmount() instead.
     */
    public function getPrice(IsotopeProduct $objProduct = null)
    {
        if ($this->isPercentage() && null !== $objProduct) {

            /** @var ProductPrice|ProductPrice[] $objPrice */
            $objPrice = $objProduct->getPrice();

            if (null !== $objPrice) {
                if ($objPrice instanceof ProductPriceCollection) {
                    $fltPrice = null;

                    foreach ($objPrice as $objPriceModel) {
                        $fltAmount = $objPriceModel->getAmount();

                        if (null === $fltPrice || $fltAmount < $fltPrice) {
                            $fltPrice = $fltAmount;
                        }
                    }
                } else {
                    $fltPrice = $objPrice->getAmount();
                }

                return $fltPrice / 100 * $this->getPercentage();
            }
        } else {

            /** @var ProductPrice|ProductPrice[] $objPrice */
            if (null !== $objProduct && ($objPrice = $objProduct->getPrice()) !== null) {
                return Isotope::calculatePrice($this->price, $this, 'price', $objPrice->tax_class);
            }

            return Isotope::calculatePrice($this->price, $this, 'price');
        }

        return $this->price;
    }

    /**
     * Return calculated price for this attribute option
     *
     * @param float $fltPrice    The product base price
     * @param int   $intTaxClass Tax ID of the product
     *
     * @return float
     */
    public function getAmount($fltPrice, $intTaxClass)
    {
        if ($this->isPercentage()) {
            return $fltPrice / 100 * $this->getPercentage();
        }

        return Isotope::calculatePrice($this->price, $this, 'price', $intTaxClass);
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
        $strLabel    = $this->label;
        $priceFormat = $GLOBALS['TL_LANG']['MSC']['attributePriceLabel'];

        /** @var Attribute $objAttribute */
        $objAttribute = null;

        switch ($this->ptable) {
            case 'tl_iso_product':
                $objAttribute = Attribute::findByFieldName($this->field_name);
                break;

            case 'tl_iso_attribute':
                $objAttribute = Attribute::findByPk($this->pid);
                break;
        }

        if (null === $objAttribute || $this->price == '' || $objAttribute->isVariantOption()) {
            return $strLabel;
        }

        if (null === $objProduct && $this->isPercentage()) {
            return sprintf($priceFormat, $strLabel, $this->price);
        }

        $strPrice = Isotope::formatPriceWithCurrency($this->getPrice($objProduct), false);

        if ($this->isFromPrice($objProduct)) {
            $strPrice = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $strPrice);
        }

        return sprintf($priceFormat, $strLabel, $strPrice);
    }

    /**
     * Find all options by attribute
     *
     * @param IsotopeAttributeWithOptions|Attribute $objAttribute
     *
     * @return \Isotope\Collection\AttributeOption|null
     *
     * @throws \LogicException if attribute option source is not the database table
     */
    public static function findByAttribute(IsotopeAttributeWithOptions $objAttribute, array $arrOptions = [])
    {
        if (IsotopeAttributeWithOptions::SOURCE_TABLE !== $objAttribute->getOptionsSource()) {
            throw new \LogicException('Options source for attribute "' . $objAttribute->field_name . '" is not the database table');
        }

        $t = static::getTable();

        return static::findBy(
            [
                "$t.pid=?",
                "$t.ptable='tl_iso_attribute'",
                "$t.published='1'"
            ],
            [$objAttribute->id],
            array_merge(['order' => "$t.sorting"], $arrOptions)
        );
    }

    /**
     * Find all options by field name
     *
     * @param IsotopeAttributeWithOptions|Attribute $objAttribute
     *
     * @return \Isotope\Collection\AttributeOption|null
     *
     * @throws \LogicException if attribute option source is not the database table
     */
    public static function findByProducts(IsotopeAttributeWithOptions $objAttribute, array $arrOptions = [])
    {
        if (IsotopeAttributeWithOptions::SOURCE_PRODUCT !== $objAttribute->getOptionsSource()) {
            throw new \LogicException('Options source for attribute "' . $objAttribute->field_name . '" is not products');
        }

        $t = static::getTable();

        return static::findBy(
            [
                "$t.ptable='tl_iso_product'",
                "$t.field_name=?",
                "$t.published='1'"
            ],
            [$objAttribute->field_name],
            array_merge(['order' => "$t.sorting"], $arrOptions)
        );
    }

    /**
     * Find all options by attribute
     *
     * @param IsotopeProduct              $objProduct
     * @param IsotopeAttributeWithOptions $objAttribute
     *
     * @return \Isotope\Collection\AttributeOption|null
     *
     * @throws \LogicException if attribute options source is not the product
     */
    public static function findByProductAndAttribute(IsotopeProduct $objProduct, IsotopeAttributeWithOptions $objAttribute, array $arrOptions = [])
    {
        if (IsotopeAttributeWithOptions::SOURCE_PRODUCT !== $objAttribute->getOptionsSource()) {
            throw new \LogicException('Options source for attribute "' . $objAttribute->getFieldName() . '" is not the product');
        }

        $t = static::getTable();
        $productId = $objProduct->id;

        if ($objProduct->isVariant() && !\in_array($objAttribute->field_name, $objProduct->getVariantAttributes())) {
            $productId = $objProduct->getProductId();
        }

        return static::findBy(
            array(
                "$t.pid=?",
                "$t.ptable='tl_iso_product'",
                "$t.field_name=?",
                "$t.published='1'"
            ),
            array(
                $productId,
                $objAttribute->getFieldName()
            ),
            array_merge(['order' => "$t.sorting"], $arrOptions)
        );
    }

    /**
     * Find published attribute options by IDs
     *
     * @param array $arrIds
     * @param array $arrOptions
     *
     * @return \Isotope\Collection\AttributeOption|null
     */
    public static function findPublishedByIds(array $arrIds, array $arrOptions = array())
    {
        $t = static::getTable();

        $arrOptions = array_merge(
            $arrOptions,
            array(
                'column' => array(
                    "$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")",
                    "$t.published='1'"
                ),
                'order' => "$t.sorting"
            )
        );

        return static::find($arrOptions);
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
     * @param Result $objResult The database result object
     * @param string $strTable  The table name
     *
     * @return \Isotope\Collection\AttributeOption The model collection
     */
    protected static function createCollectionFromDbResult(Result $objResult, $strTable)
    {
        return \Isotope\Collection\AttributeOption::createFromDbResult($objResult, $strTable);
    }
}
