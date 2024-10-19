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

use Contao\PageModel;
use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Gallery\Standard as StandardGallery;

/**
 * Gallery is the parent class for all gallery types
 *
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $type
 * @property string $anchor
 * @property string $placeholder
 * @property string $main_size
 * @property string $main_watermark_image
 * @property string $main_watermark_position
 * @property string $gallery_size
 * @property string $gallery_watermark_image
 * @property string $gallery_watermark_position
 * @property string $customTpl
 */
abstract class Gallery extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_gallery';

    /**
     * Interface to validate shipping method
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeGallery';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Create a gallery for product, falls back to standard gallery if none is defined
     *
     * @param string         $strAttribute
     * @param array          $arrConfig
     * @return static
     */
    public static function createForProductAttribute(IsotopeProduct $objProduct, $strAttribute, $arrConfig)
    {
        $objGallery = static::findByPk((int) $arrConfig['gallery']);

        if (null === $objGallery) {
            $objGallery = new StandardGallery();
        } else {
            $objGallery = clone $objGallery;
            $objGallery->preventSaving();
            $objGallery->id = (int) $arrConfig['gallery'];
        }

        $objGallery->setName($objProduct->getFormId() . '_' . $strAttribute);
        $objGallery->setFiles(static::mergeMediaData(
            StringUtil::deserialize($objProduct->$strAttribute, true),
            StringUtil::deserialize($objProduct->{$strAttribute . '_fallback'}, true)
        ));
        $objGallery->product_id = $objProduct->getProductId();

        if (!$arrConfig['jumpTo'] instanceof PageModel || $arrConfig['jumpTo']->iso_readerMode !== 'none') {
            $objGallery->href = $objProduct->generateUrl($arrConfig['jumpTo']);
        }

        return $objGallery;
    }

    /**
     * Merge media manager data from fallback and translated product data
     *
     * @param array $arrCurrent
     * @param array $arrParent
     *
     * @return array
     */
    public static function mergeMediaData($arrCurrent, $arrParent)
    {
        $arrTranslate = array();

        if (\is_array($arrParent) && 0 !== \count($arrParent)) {

            // Create an array of images where key = image name
            foreach ($arrParent as $image) {
                if ('all' !== ($image['translate'] ?? null)) {
                    $arrTranslate[$image['src']] = $image;
                }
            }
        }

        if (\is_array($arrCurrent) && 0 !== \count($arrCurrent)) {
            foreach ($arrCurrent as $i => $image) {

                if (isset($arrTranslate[$image['src']])) {
                    if ('none' === ($arrTranslate[$image['src']]['translate'] ?? null)) {
                        $arrCurrent[$i] = $arrTranslate[$image['src']];
                    } else {
                        $arrCurrent[$i]['link']      = $arrTranslate[$image['src']]['link'] ?? '';
                        $arrCurrent[$i]['translate'] = $arrTranslate[$image['src']]['translate'] ?? '';
                    }

                    unset($arrTranslate[$image['src']]);

                } elseif ('all' !== $arrCurrent[$i]['translate']) {
                    unset($arrCurrent[$i]);
                }
            }

            // Add remaining parent image to the list
            if (!empty($arrTranslate)) {
                $arrCurrent = array_merge($arrCurrent, array_values($arrTranslate));
            }

            $arrCurrent = array_values($arrCurrent);

        } else {
            $arrCurrent = array_values($arrTranslate);
        }

        return $arrCurrent;
    }
}
