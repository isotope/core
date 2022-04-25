<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Image;
use Contao\StringUtil;
use Haste\Util\Format;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;

class Label
{

    /**
     * Generate a product label and return it as HTML string
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param array          $args
     *
     * @return array
     */
    public function generate($row, $label, $dc, $args)
    {
        $objProduct = Product::findByPk($row['id']);

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
            switch ($field) {
                case 'images':
                    $args[$i] = static::generateImage($objProduct);
                    break;

                case 'name':
                    $args[$i] = $this->generateName($row, $objProduct, $dc);
                    break;

                case 'price':
                    $args[$i] = $this->generatePrice($row);
                    break;

                case 'variantFields':
                    $args[$i] = $this->generateVariantFields($args[$i], $objProduct, $dc);
                    break;

                default:
                    $objProductType = ProductType::findByPk($row['type']);
                    if ($objProductType
                        && $objProductType->hasVariants()
                        && !\in_array($field, $objProductType->getAttributes(), true)
                        && \in_array($field, $objProductType->getVariantAttributes(), true)
                    ) {
                        $values = Database::getInstance()->prepare("SELECT $field FROM tl_iso_product WHERE pid=?")->execute($row['id'])->fetchEach($field);
                        $values = array_unique(array_filter($values));
                        $removed = array_splice($values, 3);

                        $args[$i] = implode('<br>', $values);

                        if ($removed) {
                            $args[$i] .= '<br><span title="'.StringUtil::specialchars(implode(', ', $removed)).'">…</span><span class="invisible">'.StringUtil::specialchars(implode(', ', $removed)).'</span>';
                        }
                    }
            }
        }

        return $args;
    }

    /**
     * Generate image label for product.
     *
     * @param Product $objProduct
     *
     * @return string
     */
    public static function generateImage($objProduct)
    {
        $arrImages = StringUtil::deserialize($objProduct->images);

        if (!empty($arrImages) && \is_array($arrImages)) {
            foreach ($arrImages as $image) {
                $strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];

                if (is_file(TL_ROOT . '/' . $strImage)) {
                    $size = @getimagesize(TL_ROOT . '/' . $strImage);

                    $script = sprintf(
                        "Backend.openModalImage({'width':%s,'title':'%s','url':'%s'});return false",
                        $size[0],
                        str_replace("'", "\\'", $objProduct->name),
                        TL_FILES_URL . $strImage
                    );

                    /** @noinspection BadExpressionStatementJS */
                    /** @noinspection HtmlUnknownTarget */
                    return sprintf(
                        '<a href="%s" onclick="%s"><img src="%s" alt="%s"></a>',
                        TL_FILES_URL . $strImage,
                        $script,
                        TL_ASSETS_URL . Image::get($strImage, 50, 50, 'proportional'),
                        $image['alt']
                    );
                }
            }
        }

        return '&nbsp;';
    }

    /**
     * Generate name label for product with link to variants if enabled.
     *
     * @param array $row
     * @param Product $objProduct
     * @param DataContainer $dc
     *
     * @return string
     */
    private function generateName($row, $objProduct, $dc)
    {
        // Add a variants link
        if ($row['pid'] == 0
            && ($objProductType = ProductType::findByPk($row['type'])) !== null
            && $objProductType->hasVariants()
        ) {
            /** @noinspection HtmlUnknownTarget */
            return sprintf(
                '<a href="%s" title="%s">%s</a>',
                StringUtil::ampersand(Environment::get('request')) . '&amp;id=' . $row['id'],
                StringUtil::specialchars($GLOBALS['TL_LANG'][$dc->table]['showVariants']),
                $objProduct->name
            );
        }

        return $objProduct->name;
    }

    /**
     * Generate price label for product.
     *
     * @param array $row
     *
     * @return string
     */
    private function generatePrice($row)
    {
        $objPrice = null;
        $fromPrice = false;

        if ($row['pid'] == 0
            && null !== ($objProductType = ProductType::findByPk($row['type']))
            && $objProductType->hasVariants()
            && \in_array('price', $objProductType->getVariantAttributes())
        ) {
            $variantIds = Database::getInstance()->prepare("SELECT id FROM tl_iso_product WHERE pid=? AND language=''")->execute($row['id'])->fetchEach('id');

            if (empty($variantIds)) {
                return '';
            }

            $objPrices = ProductPrice::findPrimaryByProductIds($variantIds);

            if (null !== $objPrices) {
                $fltPrice = null;
                $arrPrices = [];

                /** @var ProductPrice $price */
                foreach ($objPrices as $price) {
                    $fltNew = $price->getValueForTier($price->getLowestTier());
                    $arrPrices[] = $fltNew;

                    if (null === $fltPrice || $fltNew < $fltPrice) {
                        $fltPrice = $fltNew;
                        $objPrice = $price;
                    }
                }

                if (\count(array_unique($arrPrices)) > 1) {
                    $fromPrice = true;
                }
            }
        }

        if (null === $objPrice) {
            $objPrice = ProductPrice::findPrimaryByProductId($row['id']);
        }

        if (null !== $objPrice) {
            try {
                /** @var \Isotope\Model\TaxClass $objTax */
                $objTax = $objPrice->getRelated('tax_class');
                $strTax = (null === $objTax ? '' : ' (' . $objTax->getName() . ')');

                if ($fromPrice) {
                    return sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $objPrice->getValueForTier(1)) . $strTax;
                }

                return $objPrice->getValueForTier(1) . $strTax;
            } catch (\Exception $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * Generate variant fields for product.
     *
     * @param string $label
     * @param Product $objProduct
     * @param DataContainer $dc
     *
     * @return string
     */
    private function generateVariantFields($label, $objProduct, $dc)
    {
        $attributes = [];

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
            $attributes[] = sprintf(
                '<strong>%s:</strong>&nbsp;%s',
                Format::dcaLabel($dc->table, $variantField),
                Format::dcaValue($dc->table, $variantField, $objProduct->$variantField)
            );
        }

        return ($label ? $label . '<br>' : '') . implode(', ', $attributes);
    }
}
