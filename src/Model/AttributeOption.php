<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model\Collection;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Product\CoreProduct;
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
 * @property string $cssClass
 * @property bool   $published
 */
class AttributeOption extends Multilingual
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_attribute_option';

    /**
     * Find published options by attribute.
     *
     * @return Collection<AttributeOption>|null
     *
     * @throws \LogicException if attribute option source is not the database table
     */
    public static function findByAttribute(OptionsAttributeInterface $attribute, array $options = []): Collection|null
    {
        if (OptionsSource::Table !== $attribute->getOptionsSource()) {
            throw new \LogicException('Options source for attribute "' . $attribute->getFieldName() . '" is not the database table');
        }

        $t = static::getTable();

        return static::findBy(
            [
                "$t.pid=(SELECT id FROM tl_iso_attribute WHERE field_name=?)",
                "$t.ptable='tl_iso_attribute'",
                "$t.published='1'"
            ],
            [$attribute->getFieldName()],
            array_merge(['order' => "$t.sorting"], $options)
        );
    }

    /**
     * Find published options by product attribute.
     *
     * @return Collection<AttributeOption>|null
     *
     * @throws \LogicException If attribute options source is not the product.
     */
    public static function findByProductAndAttribute(CoreProduct $product, OptionsAttributeInterface $attribute, array $options = []): Collection|null
    {
        if (OptionsSource::Product !== $attribute->getOptionsSource()) {
            throw new \LogicException('Options source for attribute "' . $attribute->getFieldName() . '" is not the product');
        }

        $t = static::getTable();
        $productId = $product->id;

        if ($product->isVariant() && !\in_array($attribute->getFieldName(), $product->getProductType()->getVariantAttributes())) {
            $productId = $product->getProductId();
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
                $attribute->getFieldName()
            ),
            array_merge(['order' => "$t.sorting"], $options)
        );
    }

    /**
     * Find published attribute options by IDs.
     *
     * @return Collection<AttributeOption>|null
     */
    public static function findPublishedByIds(array $arrIds, array $arrOptions = array()): Collection|null
    {
        $t = static::getTable();

        $arrOptions = array_merge(
            $arrOptions,
            array(
                'column' => array(
                    "$t.id IN (" . implode(',', array_map(intval(...), $arrIds)) . ")",
                    "$t.published='1'"
                ),
                'order' => "$t.sorting"
            )
        );

        return static::find($arrOptions);
    }
}
